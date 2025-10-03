<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Portfolio;
use Illuminate\Console\Command;

class DeleteInactivePortfoliosCommand extends Command
{
    protected $signature = 'app:delete-inactive-portfolios-command';

    protected $description = 'Command description';

    public function handle()
    {
        $this->info('DeleteInactivePortfoliosCommand started');
        while (true) {
            $count = Portfolio::where('is_active', false)->count();
            if ($count === 0) {
                break;
            }

            Portfolio::where('is_active', false)
                ->orderBy('created_at')
                ->limit(50)
                ->delete();

            $this->info(
                printf("Deleted 50 inactive portfolios, remaining: %d", $count - 50)
            );
            sleep(1);
        }

        $this->info('DeleteInactivePortfoliosCommand finished');
        return Command::SUCCESS;
    }
}
