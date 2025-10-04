<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\RebalanceAssetDto;
use App\DTOs\PortfolioAnalysisDto;
use App\Models\Portfolio;
use App\Repositories\TokenPriceRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PortfolioAnalyzer
{
    private array $prices = [];

    public function __construct(private readonly TokenPriceRepository $tokenPriceRepository) {}

    public function withPrice(string $symbol, float $priceUsd): self
    {
        $this->prices[$symbol] = $priceUsd;
        return $this;
    }

    public function resetPrices(): self
    {
        $this->prices = [];
        return $this;
    }

    public function for(Portfolio $portfolio): PortfolioAnalysisDto
    {
        try {
            $totalValueUsd = $portfolio->assets->sum(function ($asset) {
                return $asset->quantity * $this->getPriceForSymbol($asset->token_symbol);
            });
        } catch (ModelNotFoundException $e) {
            $portfolio->is_active = false;
            $portfolio->save();

            return new PortfolioAnalysisDto(
                portfolioId: $portfolio->id,
                totalValueUsd: 0.0,
                threshold: (float) $portfolio->rebalance_threshold_percent,
                assets: collect([new RebalanceAssetDto(
                    tokenSymbol: '',
                    currentUsdValue: 0.0,
                    targetUsdValue: 0.0,
                    currentPercent: 0.0,
                    targetPercent: 0.0,
                    differencePercent: 0.0,
                    priceUsd: 0.0,
                    quantityDelta: 0.0,
                    quantityBefore: 0.0,
                    quantityAfter: 0.0,
                )])
            );
        }

        $assetCollection = $portfolio->assets->map(function ($asset) use ($totalValueUsd) {
            $tokenPrice = $this->getPriceForSymbol($asset->token_symbol);
            $currentValueUsd = $asset->quantity * $tokenPrice;
            $currentAllocationPercent = ($currentValueUsd / $totalValueUsd) * 100;
            $targetUsdValue = $asset->target_allocation_percent * $totalValueUsd / 100;

            return new RebalanceAssetDto(
                tokenSymbol: $asset->token_symbol,
                currentUsdValue: (float) $currentValueUsd,
                targetUsdValue: (float) $targetUsdValue,
                currentPercent: (float) $currentAllocationPercent,
                targetPercent: (float) $asset->target_allocation_percent,
                differencePercent: abs($currentAllocationPercent - $asset->target_allocation_percent),
                priceUsd: (float) $tokenPrice,
                quantityDelta: (float) $targetUsdValue / $tokenPrice - $asset->quantity,
                quantityBefore: (float) $asset->quantity,
                quantityAfter: (float) $targetUsdValue / $tokenPrice,
            );
        });

        return new PortfolioAnalysisDto(
            portfolioId: $portfolio->id,
            totalValueUsd: $totalValueUsd,
            threshold: (float) $portfolio->rebalance_threshold_percent,
            assets: $assetCollection
        );
    }

    private function getPriceForSymbol(string $symbol): float
    {
        if (isset($this->prices[$symbol])) {
            return $this->prices[$symbol];
        }

        return $this->tokenPriceRepository->getLatestPriceBySymbol($symbol);
    }
}
