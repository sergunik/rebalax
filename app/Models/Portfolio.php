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

    public const STATUS_OK = 1;
    public const STATUS_INACTIVE_ASSETS = 2;
    public const STATUS_WAITING_FOR_RERUN = 3;

    protected $fillable = [
        'user_id',
        'is_active',
        'status',
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

    public function assets(): HasMany
    {
        return $this->hasMany(PortfolioAsset::class);
    }
}
