<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteInactivePortfoliosCommandTest extends TestCase
{
    use RefreshDatabase;
    public function test_it_runs_the_command(): void
    {
        $this->artisan('app:delete-inactive-portfolios-command')
            ->assertExitCode(0);

        $this->assertTrue(true);
    }

    public function test_it_deletes_inactive_portfolios_and_leaves_active_ones()
    {
        // Arrange
        Portfolio::factory()->count(3)->create([
            'is_active' => false,
            'status' => Portfolio::STATUS_INACTIVE_ASSETS,
        ]);
        Portfolio::factory()->count(2)->create(['is_active' => true]);

        $this->assertDatabaseCount('portfolios', 5);
        $this->assertDatabaseHas('portfolios', ['is_active' => true]);

        // Act
        $this->artisan('app:delete-inactive-portfolios-command')
            ->expectsOutput('DeleteInactivePortfoliosCommand started')
            ->expectsOutput('DeleteInactivePortfoliosCommand finished')
            ->assertExitCode(0);

        // Assert
        $this->assertDatabaseCount('portfolios', 2);
        $this->assertDatabaseMissing('portfolios', ['is_active' => false]);
        $this->assertDatabaseHas('portfolios', ['is_active' => true]);
    }
}
