<?php

declare(strict_types=1);

namespace App\Jobs\Rebalance;

use App\DTOs\PortfolioAnalysisDto;
use App\DTOs\RebalanceAssetDto;
use App\Models\RebalanceLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class PortfolioRebalancedJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly PortfolioAnalysisDto $dto
    ) {
    }

    public function handle(): void
    {
        Log::info('Portfolio has been rebalanced', [
            'portfolio_id' => $this->dto->portfolioId,
            'timestamp' => now(),
        ]);

        $insertData = [];
        foreach ($this->dto->assets as $asset) {
            $insertData[] = $this->prepareLogData($asset);
        }
        if (!empty($insertData)) {
            RebalanceLog::insert($insertData);
        }
    }


    private function prepareLogData(RebalanceAssetDto $asset): array
    {
        return [
            'portfolio_id' => $this->dto->portfolioId,
            'token_symbol' => $asset->tokenSymbol,
            'quantity_before' => (float)$asset->quantityBefore,
            'quantity_after' => (float)$asset->quantityAfter,
            'quantity_delta' => (float)$asset->quantityDelta,
            'target_allocation_percent' => (float)$asset->targetPercent,
            'current_allocation_percent' => (float)$asset->currentPercent,
            'price_usd' => (float)$asset->priceUsd,
            'value_before_usd' => (float)$asset->currentUsdValue,
            'value_after_usd' => (float)$asset->targetUsdValue,
            'executed_at' => now(),
        ];
    }
}
