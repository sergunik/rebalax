<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\User;
use App\Repositories\TokenPriceRepository;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\DB;

class CreateBotWithPortfolioCommand extends Command
{
    protected $signature = 'app:farm:create-bot-with-portfolio';
    protected $description = 'Create a bot with a portfolio of cryptocurrencies';

    private User $user;
    private array $tokens;
    private array $allTokens;
    private float $initialAmount = 1000.0;

    public function __construct(
        private readonly TokenPriceRepository $tokenPriceRepository,
        private readonly Repository $cacheRepository,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->user = User::factory()->create();

        $this->createPortfolioForPair();
        for( $i = 3; $i <= 5; $i++) {
            $this->createPortfolioForSize($i);
        }

        return self::SUCCESS;
    }

    private function createPortfolioForPair(): void
    {
        $size = 2;

        // Create a portfolio for the user
        $portfolio = Portfolio::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'asset_count' => $size,
            'rebalance_threshold_percent' => config('rebalax.rebalance.simple.threshold_percent'),
            'last_rebalanced_at' => null,
        ]);

        // Find unique pairs of assets
        $existingPairs = $this->getCachedExistingPairs();
        $tokenA = $this->getRandomToken();
        $iterations = count($this->tokens) - 1;
        for ($i = 0; $i < $iterations; $i++) {
            $tokenB = $this->getRandomToken();
            $pairKey = sprintf('%s_%s', $tokenA['symbol'], $tokenB['symbol']);
            if (!in_array($pairKey, $existingPairs)) {
                break;
            }
        }
        $this->refreshAllTokens();

        // Create an assets collection for the portfolio
        $assets = [];
        foreach ([$tokenA, $tokenB] as $token) {
            $assets[] = [
                'token_symbol' => $token['symbol'],
                'target_allocation_percent' => 50.0,
                'quantity' => round(
                    $this->initialAmount / $token['price'],
                    18
                ),
            ];
        }
        $portfolio->assets()->createMany($assets);
    }

    private function createPortfolioForSize(int $size): void
    {
        // Create a portfolio for the user
        $portfolio = Portfolio::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
            'asset_count' => $size,
            'rebalance_threshold_percent' => config('rebalax.rebalance.simple.threshold_percent'),
            'last_rebalanced_at' => null,
        ]);

        // Create an assets collection for the portfolio
        $assets = [];
        for ($i = 0; $i < $size; $i++) {
            $token = $this->getRandomToken();
            $assets[] = [
                'token_symbol' => $token['symbol'],
                'target_allocation_percent' => 100.0 / $size,
                'quantity' => round(
                    $this->initialAmount / $token['price'],
                    18
                ),
            ];
        }
        $portfolio->assets()->createMany($assets);
    }

    /**
     * @return array<string, mixed>
     */
    private function getRandomToken(): array
    {
        if (empty($this->tokens)) {
            $this->tokens = $this->allTokens = $this->tokenPriceRepository->getLatestPrices();
        }

        $randomToken = array_rand($this->tokens);
        $randomValue = $this->tokens[$randomToken];
        unset($this->tokens[$randomToken]);

        return [
            'symbol' => $randomToken,
            'price' => $randomValue,
        ];
    }

    private function refreshAllTokens(): void
    {
        if (!empty($this->allTokens)) {
            $this->tokens = $this->allTokens;
        } else {
            $this->tokens = $this->allTokens = $this->tokenPriceRepository->getLatestPrices();
        }
    }

    private function getCachedExistingPairs(): array
    {
        return $this->cacheRepository->remember(
            'create_bot_with_portfolio_existing_pairs',
            60 * 10, // Cache for 10 minutes
            function () {
                return DB::select("
                    SELECT DISTINCT
                        LEAST(a1.token_symbol, a2.token_symbol) || '_' || GREATEST(a1.token_symbol, a2.token_symbol) AS asset_pair
                    FROM portfolios p
                    JOIN portfolio_assets a1 ON a1.portfolio_id = p.id
                    JOIN portfolio_assets a2 ON a2.portfolio_id = p.id AND a1.id < a2.id
                    WHERE p.asset_count = 2
                ");
            }
        );
    }
}
