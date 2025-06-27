<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_active',
        'rebalance_threshold_percent',
        'last_rebalanced_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rebalance_threshold_percent' => 'decimal:2',
        'last_rebalanced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

//    public function allocations(): HasMany
//    {
//        return $this->hasMany(PortfolioAllocation::class);
//    }
//
//    public function holdings(): HasMany
//    {
//        return $this->hasMany(PortfolioHolding::class);
//    }
}
