<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $symbol The token symbol (e.g., BTC, ETH)
 * @property string $pair The trading pair (e.g., BTC_USD)
 * @property float $price_usd The price of the token in USD
 * @property \Illuminate\Support\Carbon $fetched_at The timestamp when the price was fetched
 * @property string $fetch_hash A hash to identify the fetch operation
 */
class TokenPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'pair',
        'price_usd',
        'fetched_at',
        'fetch_hash',
    ];

    protected $casts = [
        'price_usd' => 'decimal:8',
        'fetched_at' => 'datetime',
    ];

    public $timestamps = false;
}
