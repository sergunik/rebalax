<?php

declare(strict_types=1);

namespace App\Contracts;

interface PriceCollector
{
    public function collectPrices(): void;
}
