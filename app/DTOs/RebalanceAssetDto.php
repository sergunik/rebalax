<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class RebalanceAssetDto
{
    public function __construct(
        public string $tokenSymbol,
        public float $currentUsdValue,
        public float $targetUsdValue,
        public float $currentPercent,
        public float $targetPercent,
        public float $differencePercent,
        public float $priceUsd,
        public float $quantityDelta, // positive = need to buy, negative = sell
    ) {}
}
