<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenPrice extends Model
{
    protected $fillable = [
        'symbol',
        'pair',
        'price_usd',
        'fetched_at',
    ];

    protected $casts = [
        'price_usd' => 'decimal:8',
        'fetched_at' => 'datetime',
    ];
}
