<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\RebalanceAssetDto;
use App\DTOs\PortfolioAnalysisDto;
use App\Models\Portfolio;
use App\Repositories\TokenPriceRepository;

readonly class PortfolioAnalyzer
{
    public function __construct(private TokenPriceRepository $tokenPriceRepository) {}

    public function for(Portfolio $portfolio): PortfolioAnalysisDto
    {
        try {
            $totalValueUsd = $portfolio->assets->reduce(function ($carry, $asset) {
                $tokenPrice = $this->tokenPriceRepository->getLatestPriceBySymbol($asset->token_symbol);
                return $carry + ($asset->quantity * $tokenPrice);
            }, 0.0);
        } catch (\Throwable $exception) {
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
            $tokenPrice = $this->tokenPriceRepository->getLatestPriceBySymbol($asset->token_symbol);
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
}
