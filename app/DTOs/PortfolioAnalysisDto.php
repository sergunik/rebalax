<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Support\Collection;

readonly class PortfolioAnalysisDto
{
    /**
     * @param \Illuminate\Support\Collection<\App\DTOs\RebalanceAssetDto> $assets
     */
    public function __construct(
        public int $portfolioId,
        public float $totalValueUsd,
        public float $threshold,
        public Collection $assets
    ) {}

    public function isRebalanceNeeded(): bool
    {
        return $this->assets->contains(function (RebalanceAssetDto $asset) {
            return $asset->differencePercent >= $this->threshold;
        });
    }
}
