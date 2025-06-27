<?php

namespace Tests\Unit\Jobs\Portfolio;

use App\Jobs\Portfolio\CreatePortfolioAllocationJob;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreatePortfolioAllocationJobTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('holdingDataProvider')]
    public function test_job_is_dispatched($holdingData)
    {
        // Arrange
        Bus::fake();

        $job = new CreatePortfolioAllocationJob(
            portfolioId: $holdingData['portfolioId'],
            tokenSymbol: $holdingData['tokenSymbol'],
            targetAllocationPercent: $holdingData['targetAllocationPercent'],
        );

        // Act
        dispatch($job);

        // Assert
        Bus::assertDispatched(CreatePortfolioAllocationJob::class);
    }

    public static function holdingDataProvider(): Generator
    {
        yield 'basic holding' => [[
            'portfolioId' => 13,
            'tokenSymbol' => 'BTC',
            'targetAllocationPercent' => 50.0,
        ]];

        yield 'another holding' => [[
            'portfolioId' => 14,
            'tokenSymbol' => 'ETH',
            'targetAllocationPercent' => 25.0,
        ]];
    }
}
