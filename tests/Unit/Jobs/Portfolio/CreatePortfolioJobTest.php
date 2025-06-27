<?php

namespace Tests\Unit\Jobs\Portfolio;

use App\Jobs\Portfolio\CreatePortfolioJob;
use App\Models\User;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreatePortfolioJobTest  extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    #[DataProvider('portfolioDataProvider')]
    public function test_job_is_dispatched($portfolioData)
    {
        // Arrange
        Bus::fake();

        $job = new CreatePortfolioJob(
            $this->user->id,
            $portfolioData['name'],
            $portfolioData['description'],
            $portfolioData['is_active'],
            $portfolioData['rebalance_threshold_percent'],
            $portfolioData['last_rebalanced_at']
        );

        // Act
        dispatch($job);

        // Assert
        Bus::assertDispatched(CreatePortfolioJob::class);
    }

    #[DataProvider('portfolioDataProvider')]
    public function test_handle_creates_portfolio($portfolioData)
    {
        // Arrange
        $job = new CreatePortfolioJob(
            $this->user->id,
            $portfolioData['name'],
            $portfolioData['description'],
            $portfolioData['is_active'],
            $portfolioData['rebalance_threshold_percent'],
            $portfolioData['last_rebalanced_at']
        );

        // Act
        $job->handle();

        // Assert
        $this->assertDatabaseHas('portfolios', [
            'user_id' => $this->user->id,
            'name' => $portfolioData['name'],
            'description' => $portfolioData['description'],
            'is_active' => $portfolioData['is_active'],
            'rebalance_threshold_percent' => $portfolioData['rebalance_threshold_percent'],
        ]);
    }

    public static function portfolioDataProvider(): Generator
    {
        yield 'basic portfolio' => [[
            'user_id' => null,
            'name' => 'Test Portfolio',
            'description' => 'A test portfolio',
            'is_active' => true,
            'rebalance_threshold_percent' => 5.0,
            'last_rebalanced_at' => null,
        ]];

        yield 'inactive portfolio' => [[
            'user_id' => null,
            'name' => 'Inactive Portfolio',
            'description' => 'Should be inactive',
            'is_active' => false,
            'rebalance_threshold_percent' => 10.0,
            'last_rebalanced_at' => now(),
        ]];
    }
}
