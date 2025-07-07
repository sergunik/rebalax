<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\Portfolio\CreatePortfolioAssetJob;
use App\Jobs\Portfolio\CreatePortfolioJob;
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

    public function assignAsset(int $portfolioId, string $tokenSymbol, float $targetAllocationPercent, float $quantity = 0.0): void
    {
        $job = new CreatePortfolioAssetJob(
            $portfolioId,
            $tokenSymbol,
            $targetAllocationPercent,
            $quantity
        );
        $this->dispatcher->dispatchSync($job);
    }
}
