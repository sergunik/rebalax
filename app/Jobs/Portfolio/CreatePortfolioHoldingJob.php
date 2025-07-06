<?php

declare(strict_types=1);

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
        public readonly string $tokenSymbol,
        public readonly float $quantity,
        public readonly ?DateTimeInterface $lastUpdatedAt = null,
    ) {
    }

    public function handle(): void
    {
        PortfolioHolding::updateOrCreate(
            [
                'portfolio_id' => $this->portfolioId,
                'token_symbol' => $this->tokenSymbol,
            ],
            [
                'quantity' => $this->quantity,
                'last_updated_at' => $this->lastUpdatedAt,
            ]
        );
    }
}
