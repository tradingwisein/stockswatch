<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Upstox\Client\Configuration;
use Upstox\Client\Api\HistoryApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use DB;

class UpstoxController extends Controller
{

    public function login()
    {
        $clientId = config('services.upstox.client_id');
        $redirectUri = config('services.upstox.redirect_uri');

        $state = bin2hex(random_bytes(16));

        session(['upstox_state' => $state]);

        $url = 'https://api.upstox.com/v2/login/authorization/dialog?'.http_build_query([
            'response_type' => 'code',
            'redirect_uri'  => $redirectUri,
            'client_id'     => $clientId,
            'state'         => $state,
        ]);

        return redirect()->away($url);
    }

    public function callback(Request $request)
    {

        $code = $request->query('code');
    
        if (!$code) {
            return response()->json([
                'success' => false,
                'message' => 'Authorization code not received.'
            ], 400);
        }  

        $response = Http::asForm()
            ->acceptJson()
            ->post('https://api.upstox.com/v2/login/authorization/token', [
                'code' => $code,
                'client_id' => config('services.upstox.client_id'),
                'client_secret' => config('services.upstox.client_secret'),
                'redirect_uri' => config('services.upstox.redirect_uri'),
                'grant_type' => 'authorization_code',
            ]);

        return response()->json([
            'status' => $response->status(),
            'data' => $response->json(),
        ]);
        
            
        
    
        if (!$response->successful()) {
            return response()->json([
                'status' => $response->status(),
                'data' => $response->json(),
            ], 400);
        }
        else {
            $data = $response->json();
    
            if (!isset($data['access_token']) || $data['access_token']=='') {
                return response()->json([
                    'error' => 'Upstox returned an error',
                    'response' => $data,
                ], 400);
            }
            else {
                session([
                    'upstox_token' => $data['access_token'],
                    'extended_token' => $data['extended_token'],
                ]);
            }
        }       
    
        return response()->json([
            'success' => true,
            'message' => 'Upstocks connected successfully',
            'data' => $data,
        ]);

    }


    private function config(): Configuration
    {
        $sandbox = config('services.upstox.environment') === 'sandbox';

        $token = $sandbox
            ? config('services.upstox.sandbox_access_token')
            : config('services.upstox.access_token');

        if (!$token) {
            abort(500, 'Upstox access token is not configured.');
        }

        return Configuration::getDefaultConfiguration(
            sandbox: $sandbox
        )->setAccessToken($token);
    }

    private function client(): Client
    {
        return new Client();
    }

    public function history(Request $symbol)
    {
        $accessToken = session('upstox_token');

        $today = '2026-08-17';

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get(
                'https://api.upstox.com/v3/historical-candle/' .
                'NSE_EQ|INE139A01034/minutes/1/'.$today.'/'.$today
            );

        $stock = DB::table('stocks')->where('symbol','MOTHERSON')->first() or abort(404);

        if($stock) {
            echo 'Stock Name: '.$stock->symbol.' ('.$stock->name.')'."<br/>";
        }


        $candles = $response->json('data.candles');

        if(count($candles)) {
            echo '<table border="1" cellpadding="5"><thead><tr>
                    <th>Date</th><th>O</th><th>H</th><th>L</th><th>C</th><th>Volume</th><th>OI</th>
            </tr></thead><tbody>';
        }

        foreach($candles as $candle) {
            echo '<tr><td>'.$candle[0].'</td>';
            echo '<td>'.$candle[1].'</td>';
            echo '<td>'.$candle[2].'</td>';
            echo '<td>'.$candle[3].'</td>';
            echo '<td>'.$candle[4].'</td>';
            echo '<td>'.$candle[5].'</td>';
            echo '<td>'.$candle[6].'</td></tr>';
        }

        echo '</tbody></table>';

       /*  return response()->json([
            'status' => $response->status(),
            'data' => $response->json(),
        ]); */
    }

    public function intraday(Request $symbol)
    {
        $accessToken = session('upstox_token');

        $stock = DB::table('stocks')->where('symbol','MOTHERSON')->first() or abort(404);

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://api.upstox.com/v3/historical-candle/intraday/'.$stock->instrument_key.'/minutes/15');

        if($stock) {
            echo 'Stock Name: '.$stock->symbol.' ('.$stock->name.')'."<br/>";
        }


        $candles = $response->json('data.candles');

        if(count($candles)) {
            echo '<table border="1" cellpadding="5"><thead><tr>
                    <th>Date</th><th>O</th><th>H</th><th>L</th><th>C</th><th>Volume</th><th>OI</th>
            </tr></thead><tbody>';
        }

        foreach($candles as $candle) {
            echo '<tr><td>'.$candle[0].'</td>';
            echo '<td>'.$candle[1].'</td>';
            echo '<td>'.$candle[2].'</td>';
            echo '<td>'.$candle[3].'</td>';
            echo '<td>'.$candle[4].'</td>';
            echo '<td>'.$candle[5].'</td>';
            echo '<td>'.$candle[6].'</td></tr>';
        }

        echo '</tbody></table>';

       /*  return response()->json([
            'status' => $response->status(),
            'data' => $response->json(),
        ]); */
    }
}