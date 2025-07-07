<?php

declare(strict_types=1);

namespace App\Jobs\Portfolio;

use App\Models\PortfolioAsset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreatePortfolioAssetJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $portfolioId,
        public readonly string $tokenSymbol,
        public readonly float $targetAllocationPercent,
        public readonly float $quantity = 0.0,
    ) {
    }

    public function handle(): void
    {
        PortfolioAsset::create([
            'portfolio_id' => $this->portfolioId,
            'token_symbol' => $this->tokenSymbol,
            'target_allocation_percent' => $this->targetAllocationPercent,
            'quantity' => $this->quantity,
        ]);
    }
}
