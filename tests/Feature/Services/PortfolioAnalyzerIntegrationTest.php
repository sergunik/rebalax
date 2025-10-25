<?php

namespace Tests\Feature\Services;

use App\DTOs\RebalanceAssetDto;
use App\Models\Portfolio;
use App\Models\PortfolioAsset;
use App\Models\TokenPrice;
use App\Repositories\TokenPriceRepository;
use App\Services\PortfolioAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioAnalyzerIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_happy_path()
    {
        // Arrange
        $portfolio = Portfolio::factory()->create(['rebalance_threshold_percent' => 5.0]);
        PortfolioAsset::factory()->create([
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'initial_quantity' => 2.0,
            'quantity' => 2.0,
            'target_allocation_percent' => 60.0,
        ]);
        PortfolioAsset::factory()->create([
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'ETH',
            'initial_quantity' => 10.0,
            'quantity' => 10.0,
            'target_allocation_percent' => 40.0,
        ]);
        TokenPrice::factory()->create([
            'symbol' => 'BTC',
            'price_usd' => 107000.0,
            'fetched_at' => now(),
            'fetch_hash' => 'testhash',
        ]);
        TokenPrice::factory()->create([
            'symbol' => 'ETH',
            'price_usd' => 2500.0,
            'fetched_at' => now(),
            'fetch_hash' => 'testhash',
        ]);

        $analyzer = new PortfolioAnalyzer(app(TokenPriceRepository::class));

        // Act
        $result = $analyzer->for($portfolio);

        // Assert
        $this->assertEquals($portfolio->id, $result->portfolioId);
        $this->assertEquals(239000.0, $result->totalValueUsd);
        $this->assertEquals(5.0, $result->threshold);
        $this->assertCount(2, $result->assets);
        $this->assertInstanceOf(RebalanceAssetDto::class, $result->assets[0]);
        $this->assertEquals('BTC', $result->assets[0]->tokenSymbol);
        $this->assertEquals(214000.0, $result->assets[0]->currentUsdValue);
        $this->assertEquals(143400.0, $result->assets[0]->targetUsdValue);
        $this->assertEquals(89.5397489539749, $result->assets[0]->currentPercent);
        $this->assertEquals(60.0, $result->assets[0]->targetPercent);
        $this->assertEquals(29.539748953974893, $result->assets[0]->differencePercent);
        $this->assertEquals(107000.0, $result->assets[0]->priceUsd);
        $this->assertEquals(-0.6598130841121495, $result->assets[0]->quantityDelta);
        $this->assertInstanceOf(RebalanceAssetDto::class, $result->assets[1]);
        $this->assertEquals('ETH', $result->assets[1]->tokenSymbol);
        $this->assertEquals(25000.0, $result->assets[1]->currentUsdValue);
        $this->assertEquals(95600.0, $result->assets[1]->targetUsdValue);
        $this->assertEquals(10.460251046025103, $result->assets[1]->currentPercent);
        $this->assertEquals(40.0, $result->assets[1]->targetPercent);
        $this->assertEquals(29.539748953974897, $result->assets[1]->differencePercent);
        $this->assertEquals(2500.0, $result->assets[1]->priceUsd);
        $this->assertEquals(28.240000000000002, $result->assets[1]->quantityDelta);
        $this->assertTrue($result->isRebalanceNeeded());
    }

    public function test_dont_need_rebalance()
    {
        // Arrange
        $portfolio = Portfolio::factory()->create(['rebalance_threshold_percent' => 5.0]);
        PortfolioAsset::factory()->create([
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'initial_quantity' => 2.0,
            'quantity' => 2.0,
            'target_allocation_percent' => 50.0,
        ]);
        PortfolioAsset::factory()->create([
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'ETH',
            'initial_quantity' => 100.0,
            'quantity' => 100.0,
            'target_allocation_percent' => 50.0,
        ]);
        TokenPrice::factory()->create([
            'symbol' => 'BTC',
            'price_usd' => 100000.0,
            'fetched_at' => now(),
        ]);
        TokenPrice::factory()->create([
            'symbol' => 'ETH',
            'price_usd' => 2000.0,
            'fetched_at' => now(),
        ]);

        $analyzer = new PortfolioAnalyzer(app(TokenPriceRepository::class));

        // Act
        $result = $analyzer->for($portfolio);

        // Assert
        $this->assertFalse($result->isRebalanceNeeded());
    }
}
