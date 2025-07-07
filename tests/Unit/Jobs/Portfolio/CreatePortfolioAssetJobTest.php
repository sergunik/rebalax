<?php

namespace Tests\Unit\Jobs\Portfolio;

use App\Jobs\Portfolio\CreatePortfolioAssetJob;
use Generator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreatePortfolioAssetJobTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('assetDataProvider')]
    public function test_job_is_dispatched($assetData)
    {
        // Arrange
        Bus::fake();

        $job = new CreatePortfolioAssetJob(
            $assetData['portfolio_id'],
            $assetData['token_symbol'],
            $assetData['target_allocation_percent'],
            $assetData['quantity'] ?? 0.0
        );

        // Act
        dispatch($job);

        // Assert
        Bus::assertDispatched(CreatePortfolioAssetJob::class);
    }

    public static function assetDataProvider(): Generator
    {
        yield 'basic asset' => [
            [
                'portfolio_id' => 1,
                'token_symbol' => 'BTC',
                'target_allocation_percent' => 50.0,
                'quantity' => 0.5,
            ]
        ];

        yield 'another asset' => [
            [
                'portfolio_id' => 2,
                'token_symbol' => 'ETH',
                'target_allocation_percent' => 50.0,
                'quantity' => 10.0,
            ]
        ];
    }
}

