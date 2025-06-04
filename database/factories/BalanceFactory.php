<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Balance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Balance>
 */
class BalanceFactory extends Factory
{
    protected $model = Balance::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'token_name' => $this->faker->randomElement(['BTC', 'ETH', 'XRP', 'SOL', 'XAUT']),
            'amount' => $this->faker->randomFloat(8, 0.001, 10), // до 8 знаків після коми
        ];
    }
}
