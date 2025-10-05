<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Services\MetricsService;
use App\Services\SimpleRebalanceService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Throwable;

class RunSimpleRebalanceCommand extends Command
{
    private const COMMAND_NAME = 'rebalance_simple';
    protected $signature = 'app:rebalance:simple';

    protected $description = 'Run a simple rebalance of the portfolio based on predefined rules';

    public function __construct(
        private readonly SimpleRebalanceService $rebalanceService,
        private readonly Repository $cacheRepository,
        private readonly MetricsService $metricsService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $timeStart = microtime(true);
        $status = 'success';

        $totalPortfolios = $this->getCachedTotalPortfoliosCount();

        if ($totalPortfolios === 0) {
            $this->info('No portfolios to process.');
            return 0;
        }

        $currentHour = (int) date('G') + 1; // +1 to avoid division by zero

        $globalBatchSize = (int) ceil($totalPortfolios / $currentHour);
        $currentBatchIndex = $currentHour - 1;
        $globalBatchOffset = $globalBatchSize * $currentBatchIndex;
        $countOfIterations = (int) ceil($globalBatchSize / config('rebalax.rebalance.simple.batch_size'));
        $localBatchSize = min($globalBatchSize, config('rebalax.rebalance.simple.batch_size'));

        try {
            $totalCount = 0;
            for ($i = 0; $i < $countOfIterations; $i++) {
                $batchOffset = $globalBatchOffset + ($i * $localBatchSize);
                $batchSize = min($localBatchSize, $globalBatchSize * ($currentBatchIndex+1) - $batchOffset);
                $this->rebalanceService->do($batchOffset, $batchSize);

                // Record batch metrics
                $this->metricsService->recordBatchProcessed(self::COMMAND_NAME, $batchOffset, $batchSize);

                $totalCount += $batchSize;

                if (microtime(true) - $timeStart > config('rebalax.rebalance.simple.timeout')) {
                    $this->metricsService->recordCommandTimeout(self::COMMAND_NAME);
                    break;
                }
            }

            // Record total portfolios processed
            $this->metricsService->recordPortfoliosProcessed(self::COMMAND_NAME, $totalCount);

        } catch (Throwable $e) {
            $status = 'error';
            $this->error("Command failed: " . $e->getMessage());
        } finally {
            $executionTime = microtime(true) - $timeStart;

            $this->metricsService->recordCommandExecutionTime(self::COMMAND_NAME, $executionTime);
            $this->metricsService->incrementCommandExecutions(self::COMMAND_NAME, $status);
        }

        return $status === 'success' ? 0 : 1;
    }

    private function getCachedTotalPortfoliosCount(): int
    {
        return (int) $this->cacheRepository->remember(
            'rebalance_total_portfolios_count',
            60 * 60 * 2, // Cache for 2 hours
            fn() => Portfolio::where('is_active', true)->count()
        );
    }
}
