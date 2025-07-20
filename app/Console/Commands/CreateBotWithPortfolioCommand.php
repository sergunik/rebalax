<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\User;
use App\Repositories\TokenPriceRepository;
use Illuminate\Console\Command;

class CreateBotWithPortfolioCommand extends Command
{
    protected $signature = 'app:farm:create-bot-with-portfolio';
    protected $description = 'Create a bot with a portfolio of cryptocurrencies';

    private User $user;
    private array $tokens;
    private float $initialAmount = 1000.0;

    public function __construct(
        private readonly TokenPriceRepository $tokenPriceRepository,
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->user = User::factory()->create();

        for( $i = 2; $i <= 10; $i++) {
            $this->createPortfolioForSize($i);
        }

        return self::SUCCESS;
    }

    private function createPortfolioForSize(int $size): void
    {
        // Create a portfolio for the user
        $portfolio = Portfolio::factory()->create([
            'user_id' => $this->user->id,
            'is_active' => true,
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
            $this->tokens = $this->tokenPriceRepository->getLatestPrices();
        }

        $randomToken = array_rand($this->tokens);
        $randomValue = $this->tokens[$randomToken];
        unset($this->tokens[$randomToken]);

        return [
            'symbol' => $randomToken,
            'price' => $randomValue,
        ];
    }
}
