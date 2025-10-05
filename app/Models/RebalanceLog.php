<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $rebalance_uuid The UUID of the rebalance operation
 * @property int $portfolio_id The ID of the portfolio
 * @property string $token_symbol The symbol of the token (e.g., BTC, ETH)
 * @property float $quantity_before The quantity of the token before rebalancing
 * @property float $quantity_after The quantity of the token after rebalancing
 * @property float $quantity_delta The change in quantity of the token
 * @property float $target_allocation_percent The target allocation percentage for the token in the portfolio
 * @property float $current_allocation_percent The current allocation percentage for the token in the portfolio
 * @property float $price_usd The price of the token in USD at the time of rebalancing
 * @property float $value_before_usd The total value of the token before rebalancing in USD
 * @property float $value_after_usd The total value of the token after rebalancing in USD
 * @property \Illuminate\Support\Carbon $executed_at The timestamp when the rebalancing was executed
 */
class RebalanceLog extends Model
{
    protected $fillable = [
        'rebalance_uuid',
        'portfolio_id',
        'token_symbol',
        'quantity_before',
        'quantity_after',
        'quantity_delta',
        'target_allocation_percent',
        'current_allocation_percent',
        'price_usd',
        'value_before_usd',
        'value_after_usd',
        'executed_at',
    ];

    protected $casts = [
        'quantity_before' => 'decimal:18',
        'quantity_after' => 'decimal:18',
        'quantity_delta' => 'decimal:18',
        'target_allocation_percent' => 'decimal:4',
        'current_allocation_percent' => 'decimal:4',
        'price_usd' => 'decimal:8',
        'value_before_usd' => 'decimal:8',
        'value_after_usd' => 'decimal:8',
        'executed_at' => 'datetime',
    ];

    public $timestamps = false;

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
