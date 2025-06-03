<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\SimpleRebalanceService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SimpleRebalanceServiceTest extends TestCase
{
    private SimpleRebalanceService $service;

    protected function setUp(): void
    {
        $this->service = new SimpleRebalanceService();
    }

    public function test_it_detects_when_rebalance_is_needed(): void
    {
        $balances = [
            'BTC' => 1.0,    // $50,000
            'ETH' => 10.0    // $20,000
        ];

        $prices = [
            'BTC' => 50000,
            'ETH' => 2000
        ];

        // With 70k total value, deviation is ~21% from 50/50
        $this->assertTrue($this->service->isNeedRebalance($balances, $prices, 0.07));
        $this->assertFalse($this->service->isNeedRebalance($balances, $prices, 0.25));
    }

    public function test_it_calculates_correct_rebalance_amounts(): void
    {
        $balances = [
            'BTC' => 1.0,    // $50,000
            'ETH' => 10.0    // $20,000
        ];

        $prices = [
            'BTC' => 50000,
            'ETH' => 2000
        ];

        $result = $this->service->doRebalance($balances, $prices);

        // Total portfolio value: $70,000
        // Target per coin: $35,000
        // Need to sell BTC worth $15,000 and buy ETH
        $expectedSellBtc = 15000 / 50000; // 0.3 BTC
        $expectedBuyEth = 15000 / 2000;   // 7.5 ETH

        $this->assertEqualsWithDelta($expectedSellBtc, $result['sell']['BTC'], 0.0001);
        $this->assertEqualsWithDelta($expectedBuyEth, $result['buy']['ETH'], 0.0001);
    }

    public function test_it_handles_balanced_portfolio(): void
    {
        $balances = [
            'BTC' => 1.0,    // $50,000
            'ETH' => 25.0    // $50,000
        ];

        $prices = [
            'BTC' => 50000,
            'ETH' => 2000
        ];

        $this->assertFalse($this->service->isNeedRebalance($balances, $prices, 0.05));
    }

    public function test_it_validates_input_data(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $balances = [
            'BTC' => 1.0,
            'ETH' => 10.0,
            'XRP' => 1000.0  // Third coin should trigger exception
        ];

        $prices = [
            'BTC' => 50000,
            'ETH' => 2000
        ];

        $this->service->isNeedRebalance($balances, $prices, 0.07);
    }

    public function test_it_validates_negative_values(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $balances = [
            'BTC' => -1.0,   // Negative balance should trigger exception
            'ETH' => 10.0
        ];

        $prices = [
            'BTC' => 50000,
            'ETH' => 2000
        ];

        $this->service->isNeedRebalance($balances, $prices, 0.07);
    }

    public function test_it_validates_matching_coin_names(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $balances = [
            'BTC' => 1.0,
            'ETH' => 10.0
        ];

        $prices = [
            'BTC' => 50000,
            'XRP' => 2000    // Mismatched coin name should trigger exception
        ];

        $this->service->isNeedRebalance($balances, $prices, 0.07);
    }

    public function test_it_validates_zero_prices(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $balances = [
            'BTC' => 1.0,
            'ETH' => 10.0
        ];

        $prices = [
            'BTC' => 0,      // Zero price should trigger exception
            'ETH' => 2000
        ];

        $this->service->isNeedRebalance($balances, $prices, 0.07);
    }
}
