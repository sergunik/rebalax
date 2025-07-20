<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\RebalanceChecker;
use App\Jobs\Rebalance\DoRebalanceJob;
use App\Models\Portfolio;
use Illuminate\Contracts\Bus\Dispatcher;

final readonly class SimpleRebalanceService implements RebalanceChecker
{
    public function __construct(
        private PortfolioAnalyzer $analyzer,
        private Dispatcher $dispatcher
    ) {}

    public function do(int $batchOffset, int $batchSize): int
    {
        $portfolios = Portfolio::query()
            ->with('assets')
            ->where('is_active', true)
            ->orderBy('id')
            ->offset($batchOffset)
            ->limit($batchSize)
            ->get();
        if ($portfolios->isEmpty()) {
            return 0;
        }

        foreach ($portfolios as $portfolio) {
            $analysisDto = $this->analyzer->for($portfolio);

            if ($analysisDto->isRebalanceNeeded()) {
                $this->dispatcher->dispatch(new DoRebalanceJob($analysisDto));
            }
        }

        return $portfolios->count();
    }
}

