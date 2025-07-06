<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Portfolio;
use App\Models\User;
use App\Services\PortfolioCommandService;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class PortfolioCommandServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PortfolioCommandService $service;
    protected Dispatcher $dispatcher;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = App::make(Dispatcher::class);
        $this->service = new PortfolioCommandService($this->dispatcher);
        $this->user = User::factory()->create();
    }

    public function test_create_portfolio()
    {
        $portfolioData = [
            'user_id' => $this->user->id,
            'name' => 'Integration Portfolio',
            'description' => 'Integration test',
            'is_active' => true,
            'rebalance_threshold_percent' => 5.0,
            'last_rebalanced_at' => now(),
        ];
        $portfolio = $this->service->createPortfolio($portfolioData);
        $this->assertEquals('Integration Portfolio', $portfolio->name);
        $this->assertEquals($this->user->id, $portfolio->user_id);
        $this->assertDatabaseHas('portfolios', [
            'user_id' => $this->user->id,
            'name' => 'Integration Portfolio',
            'description' => 'Integration test',
            'is_active' => true,
            'rebalance_threshold_percent' => 5.0,
        ]);
    }

    public function test_assign_allocations()
    {
        $portfolio = Portfolio::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Integration Portfolio'
        ]);
        $allocations = [
            ['token_symbol' => 'BTC', 'target_allocation_percent' => 60],
            ['token_symbol' => 'ETH', 'target_allocation_percent' => 40],
        ];
        $this->service->assignAllocations($portfolio->id, $allocations);
        $this->assertDatabaseHas('portfolio_allocations', [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'target_allocation_percent' => 60,
        ]);
        $this->assertDatabaseHas('portfolio_allocations', [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'ETH',
            'target_allocation_percent' => 40,
        ]);
    }

    public function test_update_holding()
    {
        $portfolio = Portfolio::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Integration Portfolio 2'
        ]);
        $holdingData = [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity' => 0.5,
            'last_updated_at' => now(),
        ];
        $this->service->updateHolding($holdingData);
        $this->assertDatabaseHas('portfolio_holdings', [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity' => 0.5,
        ]);
    }

    public function test_update_holding_updates_existing()
    {
        $portfolio = Portfolio::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Integration Portfolio 3'
        ]);
        $initialData = [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity' => 0.5,
            'last_updated_at' => now()->subDay(),
        ];
        $this->service->updateHolding($initialData);
        $this->assertDatabaseHas('portfolio_holdings', [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity' => 0.5,
        ]);

        $updatedData = [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity' => 1.25,
            'last_updated_at' => now(),
        ];
        $this->service->updateHolding($updatedData);
        $this->assertDatabaseHas('portfolio_holdings', [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'quantity' => 1.25,
        ]);
    }
}
