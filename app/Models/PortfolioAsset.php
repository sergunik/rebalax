<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'token_symbol',
        'target_allocation_percent',
        'initial_quantity',
        'quantity',
    ];

    protected $casts = [
        'target_allocation_percent' => 'decimal:4',
        'initial_quantity' => 'decimal:18',
        'quantity' => 'decimal:18',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
