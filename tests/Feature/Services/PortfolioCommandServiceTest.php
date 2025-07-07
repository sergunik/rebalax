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

    public function test_assign_assets()
    {
        $portfolio = Portfolio::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Integration Portfolio'
        ]);
        $this->service->assignAsset($portfolio->id, 'BTC', 100.0, 1.5);
        $this->assertDatabaseHas('portfolio_assets', [
            'portfolio_id' => $portfolio->id,
            'token_symbol' => 'BTC',
            'target_allocation_percent' => 100.0,
            'quantity' => 1.5,
        ]);
    }
}
