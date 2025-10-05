<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Portfolio;
use App\Models\PortfolioAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioAsset>
 */
class PortfolioAssetFactory extends Factory
{
    protected $model = PortfolioAsset::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'portfolio_id' => Portfolio::factory()->create()->id,
            'token_symbol' => $this->faker->randomElement(['BTC', 'ETH', 'SOL', 'BNB', 'XRP', 'ADA']),
            'target_allocation_percent' => $this->faker->randomFloat(1, 0, 100),
            'initial_quantity' => $quantity = $this->faker->randomFloat(18, 0.1, 1000),
            'quantity' => $quantity,
            'created_at' => $this->faker->dateTimeThisYear(),
            'updated_at' => $this->faker->dateTimeThisYear(),
        ];
    }
}
