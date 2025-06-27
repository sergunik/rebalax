<?php

namespace App\Jobs\Portfolio;

use App\Models\PortfolioHolding;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CreatePortfolioHoldingJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $portfolioId,
        public readonly int $userId,
        public readonly string $tokenSymbol,
        public readonly float $quantity,
        public readonly ?DateTimeInterface $lastUpdatedAt = null,
    ) {
    }

    public function handle(): void
    {
        $portfolioHolding = new PortfolioHolding();
        $portfolioHolding->portfolio_id = $this->portfolioId;
        $portfolioHolding->user_id = $this->userId;
        $portfolioHolding->token_symbol = $this->tokenSymbol;
        $portfolioHolding->quantity = $this->quantity;
        $portfolioHolding->last_updated_at = $this->lastUpdatedAt;
        $portfolioHolding->save();
    }
}
