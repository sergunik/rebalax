<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioHolding extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'user_id',
        'token_symbol',
        'quantity',
        'last_updated_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:18',
        'last_updated_at' => 'datetime',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

//    // Helper method to calculate current value
//    public function getCurrentValue(): float
//    {
//        return (float) ($this->quantity * $this->last_price);
//    }
}
