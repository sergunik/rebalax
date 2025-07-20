<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\TokenPrice;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final readonly class CachedTokenPriceRepository implements TokenPriceRepository
{
    private array $cache;
    public function __construct(
        private TokenPrice $tokenPrice
    ) {
        $this->cache = $this->setupCache();
    }

    public function getLatestPriceBySymbol(string $symbol): float
    {
        if (isset($this->cache[$symbol])) {
            return (float) $this->cache[$symbol];
        }

        throw new ModelNotFoundException("No price found for symbol: {$symbol}");
    }

    public function getLatestPrices(): array
    {
        return $this->cache;
    }

    /**
     * @return array<string, float>
     */
    private function setupCache(): array
    {
        return $this->tokenPrice
            ->select('symbol', 'price_usd')
            ->whereIn('fetched_at', function ($query) {
                $query->select('fetched_at')
                    ->from('token_prices as tp2')
                    ->orderBy('fetched_at', 'desc')
                    ->limit(1);
            })
            ->get()
            ->pluck('price_usd', 'symbol')
            ->map(fn($price) => (float) $price)
            ->toArray();
    }
}
