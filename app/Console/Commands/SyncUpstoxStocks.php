<?php

namespace App\Console\Commands;

use App\Models\Stock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SyncUpstoxStocks extends Command
{
    protected $signature = 'upstox:sync-stocks';

    protected $description = 'Sync NSE equity instruments from Upstox';

    public function handle()
    {
        $this->info('Downloading Upstox NSE instruments...');

        /*
         * Upstox provides a dedicated NSE JSON instrument file.
         */
        $url = 'https://assets.upstox.com/market-quote/instruments/exchange/NSE.json.gz';

        $response = Http::timeout(120)
            ->get($url);

        if ($response->failed()) {
            $this->error(
                'Failed to download instruments. HTTP ' .
                $response->status()
            );

            return self::FAILURE;
        }

        $this->info('Downloaded successfully.');

        /*
         * The file is gzip compressed JSON.
         */
        $json = gzdecode($response->body());

        if ($json === false) {
            $this->error('Unable to decompress Upstox instrument file.');

            return self::FAILURE;
        }

        $instruments = json_decode($json, true);

        if (!is_array($instruments)) {
            $this->error('Invalid JSON received from Upstox.');

            return self::FAILURE;
        }

        $this->info(
            'Total instruments received: ' . count($instruments)
        );

        $count = 0;

        foreach ($instruments as $instrument) {

            /*
             * We only want NSE equity instruments.
             */
            if (
                ($instrument['segment'] ?? null) !== 'NSE_EQ' ||
                ($instrument['instrument_type'] ?? null) !== 'EQ'
            ) {
                continue;
            }

            if (empty($instrument['instrument_key'])) {
                continue;
            }

            Stock::updateOrCreate(
                [
                    'instrument_key' => $instrument['instrument_key'],
                ],
                [
                    'symbol' => $instrument['trading_symbol'] ?? null,

                    'name' => $instrument['name'] ?? null,

                    'isin' => $instrument['isin'] ?? null,

                    'exchange_token' =>
                        $instrument['exchange_token'] ?? null,

                    'exchange' =>
                        $instrument['exchange'] ?? 'NSE',

                    'segment' =>
                        $instrument['segment'] ?? 'NSE_EQ',

                    'instrument_type' =>
                        $instrument['instrument_type'] ?? null,

                    'lot_size' =>
                        isset($instrument['lot_size'])
                            ? (int) $instrument['lot_size']
                            : null,

                    'tick_size' =>
                        $instrument['tick_size'] ?? null,

                    'short_name' =>
                        $instrument['short_name'] ?? null,

                    'security_type' =>
                        $instrument['security_type'] ?? null,

                    'is_active' => true,
                ]
            );

            $count++;
        }

        $this->info(
            "NSE equity stocks synchronized: {$count}"
        );

        return self::SUCCESS;
    }
}