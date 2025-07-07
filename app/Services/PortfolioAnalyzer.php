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
        $totalValueUsd = $portfolio->assets->reduce(function ($carry, $asset) {
            $tokenPrice = $this->tokenPriceRepository->getLatestPriceBySymbol($asset->token_symbol);
            return $carry + ($asset->quantity * $tokenPrice);
        }, 0.0);

        $assetCollection = $portfolio->assets->map(function ($asset) use ($totalValueUsd) {
            $tokenPrice = $this->tokenPriceRepository->getLatestPriceBySymbol($asset->token_symbol);
            $currentValueUsd = $asset->quantity * $tokenPrice;
            $currentAllocationPercent = ($currentValueUsd / $totalValueUsd) * 100;
            $targetUsdValue = $asset->target_allocation_percent * $totalValueUsd / 100;

            return new RebalanceAssetDto(
                tokenSymbol: $asset->token_symbol,
                currentUsdValue: $currentValueUsd,
                targetUsdValue: $targetUsdValue,
                currentPercent: $currentAllocationPercent,
                targetPercent: (float) $asset->target_allocation_percent,
                differencePercent: abs($currentAllocationPercent - $asset->target_allocation_percent),
                priceUsd: $tokenPrice,
                quantityDelta: $targetUsdValue / $tokenPrice - $asset->quantity,
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
