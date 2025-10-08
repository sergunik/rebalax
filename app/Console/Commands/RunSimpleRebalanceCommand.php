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
        $timeOut = config('rebalax.rebalance.simple.timeout');
        $delay = config('rebalax.rebalance.simple.delay_between_rebalances_in_days');
        $status = 'success';

        $batchOffset = $this->getLastProcessedPortfolioId();
        $batchLimit = config('rebalax.rebalance.simple.batch_size');

        if(0 === $batchOffset) {
            $this->info("Starting simple rebalance from the beginning.");
            $lastRebalancedDate = $this->getLastRebalancedDate();
            if ($lastRebalancedDate) {
                $this->info("Last rebalanced at: " . $lastRebalancedDate);
                if (strtotime($lastRebalancedDate) > strtotime(sprintf('-%d days', $delay))) {
                    $this->info("Last rebalance was less than a week ago. Exiting.");
                    return Command::SUCCESS;
                }
            } else {
                $this->info("No previous rebalances found.");
            }
        } else {
            $this->info("Resuming simple rebalance from portfolio ID: {$batchOffset}");
        }

        while (true) {
            if (microtime(true) - $timeStart > ($timeOut*2/3)) {
                $executionTime = microtime(true) - $timeStart;

                $this->metricsService->recordCommandExecutionTime(self::COMMAND_NAME, $executionTime);
                $this->metricsService->incrementCommandExecutions(self::COMMAND_NAME, $status);
                break;
            }

            try {
                $processedCount = $this->rebalanceService->do($batchOffset, $batchLimit);
                $this->metricsService->recordBatchProcessed(self::COMMAND_NAME, $batchOffset, $batchLimit);
                $this->metricsService->recordPortfoliosProcessed(self::COMMAND_NAME, $processedCount);

                $batchOffset += $processedCount;
                $this->updateLastProcessedPortfolioId($batchOffset);

                if ($processedCount < $batchLimit) {
                    // No more portfolios to process
                    $this->updateLastProcessedPortfolioId(0);

                    $executionTime = microtime(true) - $timeStart;

                    $this->metricsService->recordCommandExecutionTime(self::COMMAND_NAME, $executionTime);
                    $this->metricsService->incrementCommandExecutions(self::COMMAND_NAME, $status);
                    break;
                }
            } catch (Throwable $e) {
                $status = 'error';
                $this->error("Command failed: " . $e->getMessage());
                break;
            }

            if (microtime(true) - $timeStart > $timeOut) {
                $this->metricsService->recordCommandTimeout(self::COMMAND_NAME);
                break;
            }

        }

        return $status === 'success' ? 0 : 1;
    }

    private function getLastProcessedPortfolioId(): int
    {
        return (int) $this->cacheRepository->get('rebalance_simple_last_id', 0);
    }

    private function updateLastProcessedPortfolioId(int $lastId): void
    {
        $this->cacheRepository->put('rebalance_simple_last_id', $lastId, 3600*24); // 1 day
    }

    private function getLastRebalancedDate(): ?string
    {
        $lastPortfolio = Portfolio::query()
            ->whereNotNull('last_rebalanced_at')
            ->orderByDesc('last_rebalanced_at')
            ->first();

        return $lastPortfolio?->last_rebalanced_at?->toDateTimeString();
    }
}
