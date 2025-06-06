<?php

declare(strict_types=1);

namespace Tests\Playground;

use App\Models\Balance;
use App\Repositories\BalanceRepository;
use App\Services\SimpleRebalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DryRunSimpleRebalanceTest extends TestCase
{
    use RefreshDatabase;

    private const COIN_A_FIXTURE = 'tests/Fixtures/year/btc.csv';
    private const COIN_B_FIXTURE = 'tests/Fixtures/year/xaut.csv';
    private readonly BalanceRepository $balanceRepository;
    private readonly SimpleRebalanceService $rebalanceService;
    private array $coinAPrices = [];
    private array $coinBPrices = [];
    private Balance $coinABalance;
    private Balance $coinBBalance;
    private array $rebalanceLog = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->balanceRepository = app()->make(BalanceRepository::class);
        $this->rebalanceService = app()->make(SimpleRebalanceService::class);

        $this->loadPrices();
    }

    private function loadPrices():void
    {
        $this->coinAPrices = file(
            app()->basePath(self::COIN_A_FIXTURE), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );
        $this->coinBPrices = file(
            app()->basePath(self::COIN_B_FIXTURE), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES
        );

        self::assertSameSize($this->coinAPrices, $this->coinBPrices);
    }

    private function create_balances(): void
    {
        $initialBalance = 1000; //1k usdt

        $targetAllocation = SimpleRebalanceService::TARGET_ALLOCATION; // 50% allocation for each coin

        $coinAAmount = $initialBalance * $targetAllocation / $this->coinAPrices[0];
        $this->coinABalance = $this->balanceRepository->create('CoinA', $coinAAmount);
        $this->rebalanceLog['coinAInitBalance'] = $coinAAmount;

        $coinBAmount = $initialBalance * $targetAllocation / $this->coinBPrices[0];
        $this->coinBBalance = $this->balanceRepository->create('CoinB', $coinBAmount);
        $this->rebalanceLog['coinBInitBalance'] = $coinBAmount;
    }

    private function doRebalance(float $threshold): void
    {
        $this->rebalanceLog['rebalanceIterator'] = 0;

        // run loop for each price point
        for ($priceIndex = 0; $priceIndex < count($this->coinAPrices); $priceIndex++) {
            $coinAPrice = (float)$this->coinAPrices[$priceIndex];
            $coinBPrice = (float)$this->coinBPrices[$priceIndex];

            // check if rebalance is needed
            $isNeedRebalance = $this->rebalanceService->isNeedRebalance(
                [
                    'CoinA' => $this->coinABalance->amount,
                    'CoinB' => $this->coinBBalance->amount
                ],
                [
                    'CoinA' => $coinAPrice,
                    'CoinB' => $coinBPrice
                ],
                $threshold
            );

            // if needed, call rebalance service
            if ($isNeedRebalance) {
                $rebalanceResult = $this->rebalanceService->doRebalance(
                    [
                        'CoinA' => $this->coinABalance->amount,
                        'CoinB' => $this->coinBBalance->amount
                    ],
                    [
                        'CoinA' => $coinAPrice,
                        'CoinB' => $coinBPrice
                    ]
                );

                // Update balances based on rebalance result
                if (isset($rebalanceResult['CoinA']['buy'])) {
                    $this->coinABalance->amount += (float)$rebalanceResult['CoinA']['buy'];
                } elseif (isset($rebalanceResult['CoinA']['sell'])) {
                    $this->coinABalance->amount -= (float)$rebalanceResult['CoinA']['sell'];
                } else {
                    Log::warning("No action for CoinA with result: " . json_encode($rebalanceResult['CoinA']));
                }

                if (isset($rebalanceResult['CoinB']['buy'])) {
                    $this->coinBBalance->amount += (float)$rebalanceResult['CoinB']['buy'];
                } elseif (isset($rebalanceResult['CoinB']['sell'])) {
                    $this->coinBBalance->amount -= (float)$rebalanceResult['CoinB']['sell'];
                } else {
                    Log::warning("No action for CoinB with result: " . json_encode($rebalanceResult['CoinB']));
                }

                // and update balances accordingly
                $this->coinABalance->save();
                $this->coinBBalance->save();

                // log the results
                $this->rebalanceLog['rebalanceIterator']++;
//                Log::info("Rebalanced: CoinA - {$this->coinABalance->amount}, CoinB - {$this->coinBBalance->amount}");
            }
        }

        // return the final balances with last prices and total value
        $this->rebalanceLog['finalCoinABalance'] = $this->coinABalance->amount;
        $this->rebalanceLog['finalCoinBBalance'] = $this->coinBBalance->amount;
        $this->rebalanceLog['diffCoinA'] = $this->rebalanceLog['finalCoinABalance'] - $this->rebalanceLog['coinAInitBalance'];
        $this->rebalanceLog['diffCoinB'] = $this->rebalanceLog['finalCoinBBalance'] - $this->rebalanceLog['coinBInitBalance'];
        Log::info("Threshold: $threshold;
            Iterations: {$this->rebalanceLog['rebalanceIterator']}
            Difference CoinA: " . number_format($this->rebalanceLog['diffCoinA'], 8) . "
            Difference CoinB: " . number_format($this->rebalanceLog['diffCoinB'], 8) . "
            Total diff value: " . number_format(
                $this->rebalanceLog['diffCoinA'] * $coinAPrice +
                $this->rebalanceLog['diffCoinB'] * $coinBPrice, 2) . ";");
    }

    public function testRebalance(): void
    {
        $thresholds = range(0.03, 0.085, 0.005);
        foreach ($thresholds as $threshold) {
            $this->rebalanceLog = [];
            $this->create_balances();
            $this->doRebalance($threshold);
        }
    }
}
