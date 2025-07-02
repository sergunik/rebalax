<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\PriceCollector;
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
        try {
            $this->priceCollector->collectPrices();
        } catch (Exception $e) {
            $this->error('An error occurred while collecting prices: ' . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
