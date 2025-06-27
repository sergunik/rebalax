<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Portfolio>
 */
class PortfolioFactory extends Factory
{
    protected $model = Portfolio::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->words(2, true),
            'description' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(),
            'rebalance_threshold_percent' => $this->faker->randomFloat(2, 1, 20),
            'last_rebalanced_at' => $this->faker->optional()->dateTime(),
        ];
    }
}
