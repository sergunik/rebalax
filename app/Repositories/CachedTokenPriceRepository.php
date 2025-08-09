<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\TokenPrice;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class CachedTokenPriceRepository extends SQLTokenPriceRepository
{
    private $localCache;

    public function __construct(
        private readonly TokenPrice $tokenPrice,
        private readonly Repository $cacheRepository,
        private readonly int $ttl,
    ) {
        parent::__construct($tokenPrice);
        $this->localCache = [];
    }

    public function getLatestPriceBySymbol(string $symbol): float
    {
        if (isset($this->localCache[$symbol])) {
            return $this->localCache[$symbol];
        }
        if (isset($this->getLatestPrices()[$symbol])) {
            $this->localCache[$symbol] = $this->getLatestPrices()[$symbol];
            return $this->localCache[$symbol];
        }

        throw new ModelNotFoundException("No price found for symbol: {$symbol}");
    }

    public function getLatestPrices(): array
    {
        if(!empty($this->localCache)) {
            return $this->localCache;
        }
        $this->localCache = $this->cacheRepository->remember(
            'token_prices',
            $this->ttl,
            fn() => parent::getLatestPrices()
        );

        return $this->localCache;
    }
}
