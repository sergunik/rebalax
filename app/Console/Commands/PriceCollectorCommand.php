<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\PriceCollector;
use App\Models\TokenPrice;
use Exception;
use Illuminate\Console\Command;

class PriceCollectorCommand extends Command
{
    protected $signature = 'app:price:collect';
    protected $description = 'Collect cryptocurrency prices from exchange APIs and store them in the database';

    public function __construct(private PriceCollector $priceCollector)
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Initiating price collection');
        $startTime = hrtime(true);
        $initialCount = TokenPrice::count();

        try {
            $this->priceCollector->collectPrices();
        } catch (Exception $e) {
            $this->error('An error occurred while collecting prices: ' . $e->getMessage());
            return self::FAILURE;
        }

        $endTime = hrtime(true);
        $finalCount = TokenPrice::count();

        $this->info('Finished collecting prices');
        $this->info(sprintf(
            'Collected %d prices in %.2f seconds',
            $finalCount - $initialCount,
            ($endTime - $startTime) / 1e9
        ));

        return self::SUCCESS;
    }
}
