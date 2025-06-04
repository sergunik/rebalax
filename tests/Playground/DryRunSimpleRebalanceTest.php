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

    protected function setUp(): void
    {
        parent::setUp();

        $this->balanceRepository = app()->make(BalanceRepository::class);
        $this->rebalanceService = app()->make(SimpleRebalanceService::class);

        $this->loadPrices();
        $this->create_balances();
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
        $coinUSDTBalance = $this->balanceRepository->create('USDT', 1000);

        $targetAllocation = SimpleRebalanceService::TARGET_ALLOCATION; // 50% allocation for each coin

        $coinAAmount = $initialBalance * $targetAllocation / $this->coinAPrices[0];
        $this->coinABalance = $this->balanceRepository->create('CoinA', $coinAAmount);
        Log::info("CoinA initial balance: {$this->coinABalance->amount}");

        $coinBAmount = $initialBalance * $targetAllocation / $this->coinBPrices[0];
        $this->coinBBalance = $this->balanceRepository->create('CoinB', $coinBAmount);
        Log::info("CoinB initial balance: {$this->coinBBalance->amount}");

        $coinUSDTBalance->amount = 0;
        $coinUSDTBalance->save();

        self::assertSame(0, $this->balanceRepository->findByToken('USDT')->amount);
    }

    public function testRebalance(): void
    {
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
                0.07 // 7% deviation threshold
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
                Log::info("Rebalance needed at index $priceIndex: " . json_encode($rebalanceResult));

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
                Log::info("Rebalanced: CoinA - {$this->coinABalance->amount}, CoinB - {$this->coinBBalance->amount}");
            }
        }

        // return the final balances with last prices and total value
        Log::info("Final Balances:");
        Log::info("CoinA: {$this->coinABalance->amount}, CoinB: {$this->coinBBalance->amount}");
        Log::info("Total Value: " . ($this->coinABalance->amount * $coinAPrice) + ($this->coinBBalance->amount * $coinBPrice));
    }
}
