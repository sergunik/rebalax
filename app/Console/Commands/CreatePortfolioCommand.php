<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\User;
use App\Repositories\TokenPriceRepository;
use Illuminate\Console\Command;

class CreatePortfolioCommand extends Command
{
    protected $signature = 'app:farm:create-portfolio
        {--threshold=7.0 : Rebalance threshold percent}
        {--initial_amount=1000.0 : Initial amount to invest in the portfolio}
        {assets* : Asset allocations in the format SYMBOL:PERCENT, for example BTC:50 ETH:30 ADA:20}';

    protected $description = 'Create a portfolio with specified assets and target allocations';

    public function __construct(
        private readonly TokenPriceRepository $tokenPriceRepository,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $prices = $this->tokenPriceRepository->getLatestPrices();
        $initialAmount = (float) $this->option('initial_amount');

        $assetInput = $this->argument('assets');

        $portfolioAssets = [];
        $totalPercent = 0;
        foreach ($assetInput as $asset) {
            [$symbol, $percent] = explode(':', $asset);

            $symbol = strtoupper(trim($symbol));
            $percent = (int) trim($percent);

            $this->info("Asset: $symbol, Target Allocation Percent: $percent");

            if (!is_numeric($percent) || $percent <= 0 || $percent > 100) {
                $this->error("Invalid percent value for asset $symbol: $percent. Must be a number between 0 and 100.");
                return Command::FAILURE;
            }
            $totalPercent += $percent;

            if (!isset($prices[$symbol])) {
                $this->error("Price for asset $symbol not found.");
                return Command::FAILURE;
            }

            $quantity = ($initialAmount * $percent / 100) / $prices[$symbol];

            $portfolioAssets[] = [
                'token_symbol' => $symbol,
                'target_allocation_percent' => $percent,
                'initial_quantity' => $quantity,
                'quantity' => $quantity,
            ];
        }

        if ($totalPercent !== 100) {
            $this->error("Total target allocation percent must be 100. Current total: $totalPercent");
            return Command::FAILURE;
        }

        $user = $this->getUser();

        $portfolio = Portfolio::create([
            'user_id' => $user->id,
            'is_active' => true,
            'status' => Portfolio::STATUS_OK,
            'rebalance_threshold_percent' => (float) $this->option('threshold'),
            'last_rebalanced_at' => null,
            'asset_count' => count($portfolioAssets),
        ]);
        $portfolio->assets()->createMany($portfolioAssets);


        return Command::SUCCESS;
    }

    private function getUser(): User
    {
        $user = User::inRandomOrder()->first();
        if (!$user) {
            $user = User::factory()->create();
        }
        return $user;
    }
}
