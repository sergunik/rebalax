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

            $this->simulateRebalances($portfolio);

            $portfolio->update([
                'is_active' => true,
                'status' => Portfolio::STATUS_OK,
            ]);
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

    private function simulateRebalances(Portfolio $portfolio): void
    {
        $rebalanceTime = $portfolio->created_at->copy();

        while (true) {
            $rebalanceTime->addWeek();
            dump("Simulating rebalance at {$rebalanceTime->toDateTimeString()}");

            if ($rebalanceTime->greaterThan(now())) {
                break;
            }

            $this->analyzer->resetPrices();

            foreach ($portfolio->assets as $asset) {
                $price = $this->getSymbolPrice($asset->token_symbol, $rebalanceTime);
                if ($price === Command::FAILURE) {
                    continue;
                }
                $this->analyzer->withPrice($asset->token_symbol, $price);
            }

            $analysisDto = $this->analyzer->for($portfolio);

            if ($analysisDto->isRebalanceNeeded()) {
                $this->dispatchRebalance($portfolio->id, $analysisDto, $rebalanceTime);
                break;
            }
        }
    }

    private function dispatchRebalance(int $portfolioId, $analysisDto, $rebalanceTime): void
    {
        $this->dispatcher->dispatch(new DoRebalanceJob($analysisDto));
        dump("Dispatched DoRebalanceJob for portfolio ID {$portfolioId} at {$rebalanceTime->toDateTimeString()}");
    }

    private function getSymbolPrice(string $symbol, mixed $at): float|int
    {
        $priceRecord = TokenPrice::query()
            ->where('symbol', $symbol)
            ->where('fetched_at', '<=', $at)
            ->orderByDesc('fetched_at')
            ->first();

        if (! $priceRecord) {
            $this->error("No price record found for token {$symbol} before {$at}");
            return Command::FAILURE;
        }

        return (float) $priceRecord->price_usd;
    }
}
