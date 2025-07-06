<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\TokenPrice;
use Illuminate\Database\Eloquent\ModelNotFoundException;

readonly class TokenPriceRepository
{
    public function __construct(
        private TokenPrice $tokenPrice
    ) {}

    public function getLatestPriceBySymbol(string $symbol): float
    {
        $tokenPrice = $this->tokenPrice
            ->where('symbol', $symbol)
            ->latest('fetched_at')
            ->first();

        if (!$tokenPrice) {
            throw new ModelNotFoundException("No price found for symbol: {$symbol}");
        }

        return (float) $tokenPrice->price_usd;
    }

    public function getLatestPriceByPair(string $pair): float
    {
        $tokenPrice = $this->tokenPrice
            ->where('pair', $pair)
            ->latest('fetched_at')
            ->first();

        if (!$tokenPrice) {
            throw new ModelNotFoundException("No price found for pair: {$pair}");
        }

        return (float) $tokenPrice->price_usd;
    }

    /**
     * @todo refactor it
     * @return float[]
     */
    public function getLatestPrices(): array
    {
        return $this->tokenPrice
            ->select('symbol', 'price_usd')
            ->whereIn('id', function ($query) {
                $query->select('id')
                    ->from('token_prices as tp2')
                    ->whereColumn('tp2.symbol', 'token_prices.symbol')
                    ->orderBy('fetched_at', 'desc')
                    ->limit(1);
            })
            ->get()
            ->pluck('price_usd', 'symbol')
            ->map(fn($price) => (float) $price)
            ->toArray();
    }
}
