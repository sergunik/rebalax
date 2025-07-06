<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\Portfolio\CreatePortfolioJob;
use App\Jobs\Portfolio\CreatePortfolioAllocationJob;
use App\Jobs\Portfolio\CreatePortfolioHoldingJob;
use App\Models\Portfolio;
use Illuminate\Contracts\Bus\Dispatcher;

class PortfolioCommandService
{
    public function __construct(protected Dispatcher $dispatcher)
    {
    }

    public function createPortfolio(array $data): Portfolio
    {
        $job = new CreatePortfolioJob(
            $data['user_id'],
            $data['name'] ?? '',
            $data['description'] ?? '',
            $data['is_active'] ?? false,
            $data['rebalance_threshold_percent'] ?? 7.5,
            $data['last_rebalanced_at'] ?? null
        );
        $this->dispatcher->dispatchSync($job);
        return Portfolio::where('user_id', $data['user_id'])
            ->where('name', $data['name'] ?? '')
            ->latest('id')->firstOrFail();
    }

    public function assignAllocations(int $portfolioId, array $allocations): void
    {
        foreach ($allocations as $allocation) {
            $job = new CreatePortfolioAllocationJob(
                $portfolioId,
                $allocation['token_symbol'],
                $allocation['target_allocation_percent']
            );
            $this->dispatcher->dispatchSync($job);
        }
    }

    public function updateHolding(array $data): void
    {
        $job = new CreatePortfolioHoldingJob(
            $data['portfolio_id'],
            $data['token_symbol'],
            $data['quantity'],
            $data['last_updated_at'] ?? null
        );
        $this->dispatcher->dispatchSync($job);
    }
}
