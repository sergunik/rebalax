<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SimpleRebalanceService;
use Illuminate\Console\Command;

class RunSimpleRebalanceCommand extends Command
{
    protected $signature = 'app:rebalance:simple
        {--verbose|-v : Output detailed information during execution}
        {--batchOffset=0 : Offset for batch processing}
        {--batchSize=100 : Number of portfolios to process in a single batch}';

    protected $description = 'Run a simple rebalance of the portfolio based on predefined rules';

    public function __construct(private readonly SimpleRebalanceService $rebalanceService)
    {
        parent::__construct();
    }

    public function handle()
    {
        $timeStart = microtime(true);
        if ($this->option('verbose')) {
            $this->info("Starting simple rebalance at " . date('Y-m-d H:i:s'));
        }

        $batchOffset = (int) $this->option('batchOffset');
        $batchSize = $this->hasOption('batchSize')
            ? (int) $this->option('batchSize')
            : config('rebalax.rebalance.simple.batch_size');
        $totalCount = 0;

        while (true) {
            $totalCount += $count = $this->rebalanceService->do($batchOffset, $batchSize);
            if ($count) {
                if ($this->option('verbose')) {
                    $this->info("Processed batch with offset {$batchOffset} and size {$batchSize}");
                }
                $batchOffset += $batchSize;
            } else {
                if ($this->option('verbose')) {
                    $this->info("No more portfolios to process. Stopping.");
                }
                break;
            }

            if (microtime(true) - $timeStart > config('rebalax.rebalance.simple.timeout')) {
                if ($this->option('verbose')) {
                    $this->info("Max execution time reached. Stopping.");
                }
                break;
            }
        }

        $timeEnd = microtime(true);
        $executionTime = $timeEnd - $timeStart;
        if ($this->option('verbose')) {
            $this->info("Simple rebalance completed in " . round($executionTime, 2) . " seconds.");
            $this->info("Total portfolios processed: {$totalCount}");
        }
    }
}
