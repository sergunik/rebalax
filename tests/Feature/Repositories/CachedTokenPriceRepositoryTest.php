<?php

namespace Tests\Feature\Repositories;

use App\Models\TokenPrice;
use App\Repositories\CachedTokenPriceRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class CachedTokenPriceRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CacheRepository $cacheMock;
    private CachedTokenPriceRepository $tokenPriceRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->cacheMock = Mockery::mock(CacheRepository::class);

        $this->tokenPriceRepository = new CachedTokenPriceRepository(
            tokenPrice: new TokenPrice(),
            cacheRepository: $this->cacheMock,
            ttl: 300,
        );
    }

    public function test_get_latest_price_by_symbol_returns_price_from_cache()
    {
        $this->cacheMock->shouldReceive('remember')
            ->once()
            ->with('token_prices', 300, Mockery::any())
            ->andReturn(['BTC' => 123.45]);


        $price = $this->tokenPriceRepository->getLatestPriceBySymbol('BTC');
        $this->assertEquals(123.45, $price);

        TokenPrice::where('symbol', 'BTC')->delete();
        $priceCached = $this->tokenPriceRepository->getLatestPriceBySymbol('BTC');
        $this->assertEquals(123.45, $priceCached);
    }

    public function test_get_latest_price_by_symbol_throws_exception_when_symbol_not_found()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->cacheMock->shouldReceive('remember')
            ->once()
            ->with('token_prices', 300, Mockery::any())
            ->andReturn([]);

        $this->tokenPriceRepository->getLatestPriceBySymbol('FAKE_SYMBOL');
    }
}
