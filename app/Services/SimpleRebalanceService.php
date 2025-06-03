<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

class SimpleRebalanceService
{
    private const TARGET_ALLOCATION = 0.5; // 50%

    /**
     * Determines if rebalancing is needed based on current allocation deviation
     *
     * @param array<string,float> $walletBalances Current amounts of coins
     * @param array<string,float> $prices Current prices in USD
     * @param float $threshold Rebalance trigger percentage (0.07 = 7%)
     * @return bool True if rebalance is needed
     * @throws InvalidArgumentException If invalid input provided
     */
    public function isNeedRebalance(array $walletBalances, array $prices, float $threshold): bool
    {
        $this->validateInput($walletBalances, $prices);

        $deviation = $this->calculateDeviation($walletBalances, $prices);
        return abs($deviation) > $threshold;
    }

    /**
     * Calculates required trades to achieve 50/50 balance
     *
     * @param array<string,float> $walletBalances Current amounts of coins
     * @param array<string,float> $prices Current prices in USD
     * @return array{sell: array<string,float>, buy: array<string,float>}
     * @throws InvalidArgumentException If invalid input provided
     */
    public function doRebalance(array $walletBalances, array $prices): array
    {
        $this->validateInput($walletBalances, $prices);

        $totalValueUsd = $this->calculateTotalValueUsd($walletBalances, $prices);
        $targetValuePerCoin = $totalValueUsd * self::TARGET_ALLOCATION;

        $coins = array_keys($walletBalances);
        $currentValues = $this->calculateCurrentValues($walletBalances, $prices);

        if ($currentValues[$coins[0]] > $currentValues[$coins[1]]) {
            $sellCoin = $coins[0];
            $buyCoin = $coins[1];
        } else {
            $sellCoin = $coins[1];
            $buyCoin = $coins[0];
        }

        $valueToTrade = abs($currentValues[$sellCoin] - $targetValuePerCoin);
        $amountToSell = $valueToTrade / $prices[$sellCoin];
        $amountToBuy = $valueToTrade / $prices[$buyCoin];

        return [
            'sell' => [$sellCoin => $amountToSell],
            'buy' => [$buyCoin => $amountToBuy]
        ];
    }

    /**
     * Calculates current allocation percentages
     *
     * @param array<string,float> $walletBalances
     * @param array<string,float> $prices
     * @return array<string,float>
     */
    private function calculateAllocation(array $walletBalances, array $prices): array
    {
        $totalValue = $this->calculateTotalValueUsd($walletBalances, $prices);
        $allocation = [];

        foreach ($walletBalances as $coin => $amount) {
            $valueUsd = $amount * $prices[$coin];
            $allocation[$coin] = $valueUsd / $totalValue;
        }

        return $allocation;
    }

    /**
     * Calculates deviation from target 50/50 allocation
     *
     * @param array<string,float> $walletBalances
     * @param array<string,float> $prices
     * @return float Deviation from target (-1.0 to 1.0)
     */
    private function calculateDeviation(array $walletBalances, array $prices): float
    {
        $allocation = $this->calculateAllocation($walletBalances, $prices);
        $coins = array_keys($allocation);
        return $allocation[$coins[0]] - self::TARGET_ALLOCATION;
    }

    /**
     * Calculates current USD values for each coin
     *
     * @param array<string,float> $walletBalances
     * @param array<string,float> $prices
     * @return array<string,float>
     */
    private function calculateCurrentValues(array $walletBalances, array $prices): array
    {
        $values = [];
        foreach ($walletBalances as $coin => $amount) {
            $values[$coin] = $amount * $prices[$coin];
        }
        return $values;
    }

    /**
     * Calculates total portfolio value in USD
     *
     * @param array<string,float> $walletBalances
     * @param array<string,float> $prices
     * @return float
     */
    private function calculateTotalValueUsd(array $walletBalances, array $prices): float
    {
        return array_sum($this->calculateCurrentValues($walletBalances, $prices));
    }

    /**
     * Validates input arrays structure and values
     *
     * @param array<string,float> $walletBalances
     * @param array<string,float> $prices
     * @throws InvalidArgumentException
     */
    private function validateInput(array $walletBalances, array $prices): void
    {
        if (count($walletBalances) !== 2 || count($prices) !== 2) {
            throw new InvalidArgumentException('Exactly two coins required');
        }

        if (array_keys($walletBalances) !== array_keys($prices)) {
            throw new InvalidArgumentException('Coin names must match in balances and prices');
        }

        foreach ($walletBalances as $amount) {
            if ($amount < 0) {
                throw new InvalidArgumentException('Balance amounts must be positive');
            }
        }

        foreach ($prices as $price) {
            if ($price <= 0) {
                throw new InvalidArgumentException('Prices must be positive');
            }
        }
    }
}
