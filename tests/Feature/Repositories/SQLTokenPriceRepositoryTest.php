<?php

namespace Tests\Feature\Repositories;

use App\Models\TokenPrice;
use App\Repositories\SQLTokenPriceRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SQLTokenPriceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_latest_price_by_symbol_returns_price()
    {
        TokenPrice::create([
            'symbol' => 'BTC',
            'pair' => 'BTC_USDT',
            'price_usd' => 123.45,
            'fetched_at' => now(),
        ]);

        $repo = new SQLTokenPriceRepository(new TokenPrice());
        $price = $repo->getLatestPriceBySymbol('BTC');

        $this->assertEquals(123.45, $price);
    }

    public function test_get_latest_price_by_symbol_throws_if_not_found()
    {
        $this->expectException(ModelNotFoundException::class);

        $repo = new SQLTokenPriceRepository(new TokenPrice());
        $repo->getLatestPriceBySymbol('FAKE');
    }

    public function test_get_latest_prices_returns_array()
    {
        TokenPrice::create([
            'symbol' => 'BTC',
            'pair' => 'BTC_USDT',
            'price_usd' => 100.0,
            'fetched_at' => now(),
        ]);
        TokenPrice::create([
            'symbol' => 'ETH',
            'pair' => 'ETH_USDT',
            'price_usd' => 200.0,
            'fetched_at' => now(),
        ]);

        $repo = new SQLTokenPriceRepository(new TokenPrice());
        $prices = $repo->getLatestPrices();

        $this->assertEquals(['BTC' => 100.0, 'ETH' => 200.0], $prices);
    }
}
