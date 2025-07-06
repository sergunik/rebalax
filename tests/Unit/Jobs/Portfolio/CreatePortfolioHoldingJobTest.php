<?php

namespace Tests\Unit\Jobs\Portfolio;

use App\Jobs\Portfolio\CreatePortfolioHoldingJob;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreatePortfolioHoldingJobTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('holdingDataProvider')]
    public function test_job_is_dispatched($holdingData)
    {
        // Arrange
        Bus::fake();

        $job = new CreatePortfolioHoldingJob(
            $holdingData['portfolio_id'],
            $holdingData['token_symbol'],
            $holdingData['quantity'],
            $holdingData['last_updated_at'] ?? null
        );

        // Act
        dispatch($job);

        // Assert
        Bus::assertDispatched(CreatePortfolioHoldingJob::class);
    }

    public static function holdingDataProvider(): Generator
    {
        yield 'basic holding' => [[
            'portfolio_id' => 1,
            'token_symbol' => 'BTC',
            'quantity' => 0.5,
            'last_updated_at' => now(),
        ]];

        yield 'another holding' => [[
            'portfolio_id' => 2,
            'token_symbol' => 'ETH',
            'quantity' => 10.0,
            'last_updated_at' => now(),
        ]];
    }
}
