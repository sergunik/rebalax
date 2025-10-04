<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Portfolio;
use Illuminate\Console\Command;

class DeleteInactivePortfoliosCommand extends Command
{
    protected $signature = 'app:delete-inactive-portfolios-command
                            {--batch=50 : Number of portfolios to delete in each batch}';

    protected $description = 'Delete inactive portfolios in batches to avoid long-running processes';

    public function handle()
    {
        $batchSize = (int) $this->option('batch');
        if ($batchSize <= 0) {
            $this->error('Batch size must be a positive integer.');
            return Command::FAILURE;
        }

        $this->info('DeleteInactivePortfoliosCommand started');
        while (true) {
            $count = Portfolio::where('is_active', false)->count();
            if ($count <= 0) {
                break;
            }

            Portfolio::where('is_active', false)
                ->orderBy('created_at')
                ->limit($batchSize)
                ->delete();

            $count -= $batchSize;
            $this->info(
                printf("Deleted 50 inactive portfolios, remaining: %d", $count)
            );
            sleep(1);
        }

        $this->info('DeleteInactivePortfoliosCommand finished');
        return Command::SUCCESS;
    }
}
