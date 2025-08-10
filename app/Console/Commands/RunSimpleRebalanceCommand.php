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

        $this->metricsService->setActiveCommand(self::COMMAND_NAME);

        $totalPortfolios = $this->getCachedTotalPortfoliosCount();

        if ($totalPortfolios === 0) {
            $this->info('No portfolios to process.');
            return 0;
        }

        $runsPerHour = 12;

        $currentMinute = (int) date('i');
        $currentBatchIndex = intdiv($currentMinute, 5);

        $globalBatchSize = (int) ceil($totalPortfolios / $runsPerHour);
        $globalBatchOffset = $globalBatchSize * $currentBatchIndex;
        $countOfIterations = (int) ceil($globalBatchSize / config('rebalax.rebalance.simple.batch_size'));
        $localBatchSize = min($globalBatchSize, config('rebalax.rebalance.simple.batch_size'));

        try {
            $totalCount = 0;
            for ($i = 0; $i < $countOfIterations; $i++) {
                $batchOffset = $globalBatchOffset + ($i * $localBatchSize);
                $batchSize = min($localBatchSize, $globalBatchSize * ($currentBatchIndex+1) - $batchOffset);
                $count = $this->rebalanceService->do($batchOffset, $batchSize);

                // Record batch metrics
                $this->metricsService->recordBatchProcessed(self::COMMAND_NAME, $batchOffset, $batchSize, $count);

                $totalCount += $count;

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
            $this->metricsService->setActiveCommand(self::COMMAND_NAME, 0);
        }

        return $status === 'success' ? 0 : 1;
    }

    private function getCachedTotalPortfoliosCount(): int
    {
        return (int) $this->cacheRepository->remember(
            'rebalance_total_portfolios_count',
            60 * 60 * 6, // Cache for 6 hours
            fn() => Portfolio::count()
        );
    }
}
