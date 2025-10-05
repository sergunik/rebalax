<?php

namespace Feature\Console\Commands;

use App\DTOs\PortfolioAnalysisDto;
use App\Jobs\Rebalance\DoRebalanceJob;
use App\Models\Portfolio;
use App\Models\TokenPrice;
use App\Services\PortfolioAnalyzer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ReRunSimpleRebalanceCommandTest extends TestCase
{
    use RefreshDatabase;
    public function test_it_shows_message_if_no_portfolio_found()
    {
        $this->artisan('app:re-run-simple-rebalance-command')
            ->expectsOutput('No portfolio found with is_active = false and status = STATUS_WAITING_FOR_RERUN')
            ->assertExitCode(0);
    }

    public function test_it_re_runs_rebalance_for_waiting_portfolio_and_dispatches_job()
    {
        // Arrange
        Bus::fake();

        $portfolio = Portfolio::factory()->create([
            'is_active' => false,
            'status' => Portfolio::STATUS_WAITING_FOR_RERUN,
            'created_at' => now()->subWeeks(3),
        ]);

        $asset = $portfolio->assets()->create([
            'token_symbol' => 'BTC',
            'quantity' => 1.0,
            'target_allocation_percent' => 100.0,
        ]);

        TokenPrice::factory()->create([
            'symbol' => 'BTC',
            'price_usd' => 100000,
            'fetched_at' => now()->subWeeks(3),
        ]);
        TokenPrice::factory()->create([
            'symbol' => 'BTC',
            'price_usd' => 30000,
            'fetched_at' => now()->subWeeks(2),
        ]);

        $mockAnalyzer = $this->createMock(PortfolioAnalyzer::class);
        $mockAnalyzer->method('resetPrices')->willReturnSelf();
        $mockAnalyzer->method('withPrice')->willReturnSelf();

        $portfolioAnalysisDto = $this->createMock(PortfolioAnalysisDto::class);
        $portfolioAnalysisDto->method('isRebalanceNeeded')->willReturn(true);

        $mockAnalyzer->method('for')->willReturn($portfolioAnalysisDto);

        $this->app->instance(PortfolioAnalyzer::class, $mockAnalyzer);

        // Act
        Artisan::call('app:re-run-simple-rebalance-command', [
            '--batch' => 1,
        ]);

        // Assert
        Bus::assertDispatched(DoRebalanceJob::class);

        $portfolio->refresh();
        $this->assertTrue($portfolio->is_active);
        $this->assertEquals(Portfolio::STATUS_OK, $portfolio->status);
    }
}
