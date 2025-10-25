<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\TokenPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TokenPrice>
 */
class TokenPriceFactory extends Factory
{
    protected $model = TokenPrice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'symbol' => $this->faker->randomElement(['BTC', 'ETH', 'SOL', 'BNB', 'XRP', 'ADA']),
            'pair' => $this->faker->randomElement(['BTC_USDT', 'ETH_USDT', 'SOL_USDT', 'BNB_USDT', 'XRP_USDT', 'ADA_USDT']),
            'price_usd' => $this->faker->randomFloat(8, 0.01, 100000),
            'fetched_at' => $this->faker->dateTimeThisYear(),
            'fetch_hash' => uniqid('', true),
        ];
    }
}
