<?php

declare(strict_types=1);

namespace Feature\Services;

use App\Models\Portfolio;
use App\Models\PortfolioAsset;
use App\Models\TokenPrice;
use App\Services\SimpleRebalanceService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimpleRebalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_active_portfolio(): void
    {
        $service = app(SimpleRebalanceService::class);
        $service->do(0, 100);

        $this->assertDatabaseCount('portfolios', 0);
    }

    public function test_rebalance_without_prices(): void
    {
        $portfolio = Portfolio::factory()->create([
            'is_active' => true,
        ]);

        PortfolioAsset::factory()->create([
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'ETH',
        ]);

        $this->expectException(ModelNotFoundException::class);

        $service = app(SimpleRebalanceService::class);
        $service->do(0, 100);
    }

    public function test_rebalance_needed(): void
    {
        $portfolio = Portfolio::factory()->create([
            'is_active' => true,
        ]);

        PortfolioAsset::factory()->create([
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity' => 1.0,
            'target_allocation_percent' => 50.0,
        ]);
        PortfolioAsset::factory()->create([
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'ETH',
            'quantity' => 10.0,
            'target_allocation_percent' => 50.0,
        ]);

        TokenPrice::factory()->create([
            'symbol' => 'BTC',
            'pair' => 'BTC_USD',
            'price_usd' => 100000.0,
            'fetched_at' => now(),
        ]);
        TokenPrice::factory()->create([
            'symbol' => 'ETH',
            'pair' => 'ETH_USD',
            'price_usd' => 2000.0,
            'fetched_at' => now(),
        ]);

        $service = app(SimpleRebalanceService::class);
        $service->do(0, 100);

        $this->assertDatabaseHas('portfolios', [
            'id' => $portfolio->id,
            'last_rebalanced_at' => now(),
        ]);
        $this->assertDatabaseHas('portfolio_assets', [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity' => 0.6,
        ]);
    }

    public function test_rebalance_log_added(): void
    {
        $portfolio = Portfolio::factory()->create([
            'is_active' => true,
        ]);

        PortfolioAsset::factory()->create([
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity' => 1.0,
            'target_allocation_percent' => 50.0,
        ]);
        PortfolioAsset::factory()->create([
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'ETH',
            'quantity' => 10.0,
            'target_allocation_percent' => 50.0,
        ]);

        TokenPrice::factory()->create([
            'symbol' => 'BTC',
            'pair' => 'BTC_USD',
            'price_usd' => 100000.0,
            'fetched_at' => now(),
        ]);
        TokenPrice::factory()->create([
            'symbol' => 'ETH',
            'pair' => 'ETH_USD',
            'price_usd' => 2000.0,
            'fetched_at' => now(),
        ]);

        $service = app(SimpleRebalanceService::class);
        $service->do(0, 100);

        $this->assertDatabaseHas('rebalance_logs', [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity_before' => 1.0,
            'quantity_after' => 0.6,
        ]);
    }
}
