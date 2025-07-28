<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MetricsService;
use App\Services\SimpleRebalanceService;
use Illuminate\Console\Command;
use Throwable;

class RunSimpleRebalanceCommand extends Command
{
    private const COMMAND_NAME = 'rebalance_simple';

    protected $signature = 'app:rebalance:simple
        {--batchOffset=0 : Offset for batch processing}
        {--batchSize=100 : Number of portfolios to process in a single batch}';

    protected $description = 'Run a simple rebalance of the portfolio based on predefined rules';

    public function __construct(
        private readonly SimpleRebalanceService $rebalanceService,
        private readonly MetricsService $metricsService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $timeStart = microtime(true);
        $status = 'success';

        // Mark command as active
        $this->metricsService->setActiveCommand(self::COMMAND_NAME);

        try {
            $batchOffset = (int) $this->option('batchOffset');
            $batchSize = $this->hasOption('batchSize')
                ? (int) $this->option('batchSize')
                : config('rebalax.rebalance.simple.batch_size');
            $totalCount = 0;

            while (true) {
                $count = $this->rebalanceService->do($batchOffset, $batchSize);

                // Record batch metrics
                $this->metricsService->recordBatchProcessed(self::COMMAND_NAME, $batchSize, $count);

                $totalCount += $count;

                if ($count) {
                    $batchOffset += $batchSize;
                } else {
                    break;
                }

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

            // Record metrics
            $this->metricsService->recordCommandExecutionTime(self::COMMAND_NAME, $executionTime);
            $this->metricsService->incrementCommandExecutions(self::COMMAND_NAME, $status);

            // Mark command as inactive
            $this->metricsService->setActiveCommand(self::COMMAND_NAME, 0);
        }

        return $status === 'success' ? 0 : 1;
    }
}
