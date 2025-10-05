<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Rebalance\DoRebalanceJob;
use App\Models\Portfolio;
use App\Models\TokenPrice;
use App\Services\PortfolioAnalyzer;
use Illuminate\Console\Command;
use Illuminate\Contracts\Bus\Dispatcher;

class ReRunSimpleRebalanceCommand extends Command
{
    protected $signature = 'app:re-run-simple-rebalance-command
                            {--batch=10 : Number of portfolios to process in one batch}';

    protected $description = 'Re-run simple rebalance for inactive portfolios waiting for rerun, simulating weekly rebalances from creation date to now.';

    private float $initialAmount = 1000.0;
    public function __construct(
        private readonly PortfolioAnalyzer $analyzer,
        private readonly Dispatcher $dispatcher
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $batch = (int) $this->option('batch');

        for ($i = 0; $i < $batch; $i++) {
            $portfolio = $this->nextPortfolio();

            if (! $portfolio) {
                $this->info('No portfolio found with is_active = false and status = STATUS_WAITING_FOR_RERUN');
                return Command::SUCCESS;
            }

            if($this->simulateRebalances($portfolio)) {
                $portfolio->update([
                    'is_active' => true,
                    'status' => Portfolio::STATUS_OK,
                ]);
            }
        }

        return Command::SUCCESS;
    }

    private function nextPortfolio(): ?Portfolio
    {
        return Portfolio::query()
            ->where('is_active', false)
            ->where('status', Portfolio::STATUS_WAITING_FOR_RERUN)
            ->orderBy('id')
            ->first();
    }

    private function simulateRebalances(Portfolio $portfolio): bool
    {
        $rebalanceTime = $portfolio->created_at->copy();

        unset($asset);
        foreach ($portfolio->assets as &$asset) {
            $price = $this->getSymbolPrice($asset->token_symbol, $rebalanceTime);
            if ($price === null) {
                $portfolio->update([
                    'is_active' => false,
                    'status' => Portfolio::STATUS_INACTIVE_ASSETS,
                ]);
                return false;
            }

            $q = round(
                ($this->initialAmount * ($asset->target_allocation_percent / 100.0)) / $price,
                18
            );
            $asset->update([
                'initial_quantity' => $q,
                'quantity' => $q,
            ]);
        }

        while (true) {
            $rebalanceTime->addWeek();

            if ($rebalanceTime->greaterThan(now())) {
                break;
            }

            $this->analyzer->resetPrices();

            unset($asset);
            foreach ($portfolio->assets as $asset) {
                $price = $this->getSymbolPrice($asset->token_symbol, $rebalanceTime);
                if ($price === null) {
                    $portfolio->update([
                        'is_active' => false,
                        'status' => Portfolio::STATUS_INACTIVE_ASSETS,
                    ]);
                    return false;
                }
                $this->analyzer->withPrice($asset->token_symbol, $price);
            }

            $analysisDto = $this->analyzer->for($portfolio);

            if ($analysisDto->isRebalanceNeeded()) {
                $this->dispatcher->dispatch(new DoRebalanceJob($analysisDto));
                break;
            }
        }

        return true;
    }

    private function getSymbolPrice(string $symbol, mixed $at): ?float
    {
        $priceRecord = TokenPrice::query()
            ->where('symbol', $symbol)
            ->where('fetched_at', '<=', $at)
            ->orderByDesc('fetched_at')
            ->first();

        if (! $priceRecord) {
            $this->error("No price record found for token {$symbol} before {$at}");
            return null;
        }

        return (float) $priceRecord->price_usd;
    }
}
