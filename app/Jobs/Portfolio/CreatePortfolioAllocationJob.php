<?php

namespace App\Jobs\Portfolio;

use App\Models\PortfolioAllocation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreatePortfolioAllocationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $portfolioId,
        public readonly string $tokenSymbol,
        public readonly float $targetAllocationPercent,
    ) {
    }

    public function handle(): void
    {
        $portfolioAllocation = new PortfolioAllocation();
        $portfolioAllocation->portfolio_id = $this->portfolioId;
        $portfolioAllocation->token_symbol = $this->tokenSymbol;
        $portfolioAllocation->target_allocation_percent = $this->targetAllocationPercent;
        $portfolioAllocation->save();
    }
}
