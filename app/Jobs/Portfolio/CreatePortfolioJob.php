<?php

declare(strict_types=1);

namespace App\Jobs\Portfolio;

use App\Models\Portfolio;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreatePortfolioJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $userId,
        public readonly string $name = '',
        public readonly string $description = '',
        public readonly bool $isActive = false,
        public readonly float $rebalanceThresholdPercent = 7.5,
        public readonly ?DateTimeInterface $lastRebalancedAt = null,
    ) {
    }

    public function handle(): void
    {
        $portfolio = new Portfolio();
        $portfolio->user_id = $this->userId;
        $portfolio->name = $this->name;
        $portfolio->description = $this->description;
        $portfolio->is_active = $this->isActive;
        $portfolio->rebalance_threshold_percent = $this->rebalanceThresholdPercent;
        $portfolio->last_rebalanced_at = $this->lastRebalancedAt;
        $portfolio->save();
    }
}
