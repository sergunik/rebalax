<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RebalanceChecker;
use App\Jobs\Rebalance\DoRebalanceJob;
use App\Models\Portfolio;
use Illuminate\Contracts\Bus\Dispatcher;

final class SimpleRebalanceService implements RebalanceChecker
{
    public function __construct(
        private readonly PortfolioAnalyzer $analyzer,
        private readonly Dispatcher $dispatcher
    ) {}

    public function do(): void
    {
        $portfolios = Portfolio::query()
            ->with('assets')
            ->where('is_active', true)
            ->orderBy('last_rebalanced_at')
            ->limit(10)
            ->get();

        foreach ($portfolios as $portfolio) {
            $analysisDto = $this->analyzer->for($portfolio);

            if ($analysisDto->isRebalanceNeeded()) {
                $this->dispatcher->dispatch(new DoRebalanceJob($analysisDto));
            }
        }
    }
}

