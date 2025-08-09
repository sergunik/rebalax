<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\TokenPrice;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SQLTokenPriceRepository implements TokenPriceRepository
{
    public function __construct(
        private readonly TokenPrice $tokenPrice
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

    public function getLatestPrices(): array
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
