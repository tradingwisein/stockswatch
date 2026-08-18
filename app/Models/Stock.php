<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $fillable = [
        'symbol',
        'name',
        'isin',
        'instrument_key',
        'exchange_token',
        'exchange',
        'segment',
        'instrument_type',
        'lot_size',
        'tick_size',
        'short_name',
        'security_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lot_size' => 'integer',
        'tick_size' => 'decimal:4',
    ];
}
