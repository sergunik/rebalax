<?php

declare(strict_types=1);

namespace App\Jobs\Rebalance;

use App\DTOs\PortfolioAnalysisDto;
use App\Models\Portfolio;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DoRebalanceJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PortfolioAnalysisDto $dto
    ) {
    }

    public function handle(): void
    {
        $portfolio = Portfolio::findOrFail($this->dto->portfolioId);
        foreach ($this->dto->assets as $rebalanceAssetDto) {
            $asset = $portfolio->assets()->where('token_symbol', $rebalanceAssetDto->tokenSymbol)->first();
            if ($asset) {
                $asset->quantity = ((float)$asset->quantity) + $rebalanceAssetDto->quantityDelta;
                $asset->save();
            }
        }
        $portfolio->last_rebalanced_at = now();
        $portfolio->save();

        PortfolioRebalancedJob::dispatch($this->dto)
            ->delay(now()->addSeconds(5));
    }
}
