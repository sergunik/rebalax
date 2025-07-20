<?php

declare(strict_types=1);

namespace App\Repositories;

interface TokenPriceRepository
{
    public function getLatestPriceBySymbol(string $symbol): float;

    /**
     * @return array<string, float>
     */
    public function getLatestPrices(): array;
}
