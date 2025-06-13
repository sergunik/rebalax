<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'portfolio_id',
        'token_symbol',
        'target_allocation_percent',
    ];

    protected $casts = [
        'target_allocation_percent' => 'decimal:4',
    ];

    public function portfolio(): BelongsTo
    {
        return $this->belongsTo(Portfolio::class);
    }
}
