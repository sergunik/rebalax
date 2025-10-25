<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Portfolio;
use App\Models\User;
use App\Repositories\TokenPriceRepository;
use Illuminate\Console\Command;
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
        private readonly TokenPriceRepository $tokenPriceRepository
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $this->user = User::inRandomOrder()->first();
        if (!$this->user) {
            $this->user = User::factory()->create();
        }

        $this->call(RefreshPortfolioAssetsFlatCommand::class);

        for( $i = 2; $i <= 7; $i++) {
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
            'asset_count' => $size,
            'rebalance_threshold_percent' => config('rebalax.rebalance.simple.threshold_percent'),
            'last_rebalanced_at' => null,
        ]);

        // Create an assets collection for the portfolio
        $assets = [];
        $tokens = $this->getRareTokens($size);
        foreach ($tokens as $token) {
            $quantity = round(
                $this->initialAmount / $this->allTokens[$token],
                18
            );
            $assets[] = [
                'token_symbol' => $token,
                'target_allocation_percent' => 100.0 / $size,
                'quantity' => $quantity,
                'initial_quantity' => $quantity,
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

    private function getRareTokens(int $count): array
    {
        $assetList = array_map(fn($i) => 'asset_' . $i, range(1, $count));
        $assetStr = implode(', ', $assetList);

        $this->refreshAllTokens();
        $whereAssets = [];
        $randomTokens = [];
        for($assetIterator = 1; $assetIterator <= $count-1; $assetIterator++) {
            $randomToken = $this->getRandomToken();
            $randomTokens[] = $randomToken['symbol'];
        }
        $restOfTokens = array_keys($this->tokens);
        foreach ($restOfTokens as $token) {
            $tmp = array_merge($randomTokens, [$token]);
            sort($tmp);
            $whereAssets[] = array_combine($assetList, $tmp);
        }

        $rows = DB::select("
            SELECT $assetStr, COUNT(*) AS portfolio_count
            FROM tmp_portfolio_assets_flat
            WHERE
                asset_count = $count
                AND (" . implode(' OR ', array_map(fn($a) => '(' . implode(' AND ', array_map(fn($k, $v) => "$k = '$v'", array_keys($a), array_values($a))) . ')', $whereAssets)) . ")
            GROUP BY $assetStr
            ORDER BY portfolio_count ASC
            LIMIT 100
        ");
        $existingPortfolios = collect($rows);

        foreach ($whereAssets as $whereAsset) {
            $found = $existingPortfolios->first(fn($r) =>
                collect($whereAsset)->every(fn($v, $k) => $r->$k === $v)
            );
            if (!$found) {
                $existingPortfolios->push((object)array_merge($whereAsset, [
                    'portfolio_count' => 0,
                ]));
            }

        }

        $this->refreshAllTokens();

        $result = (array) $existingPortfolios->sortBy('portfolio_count')->first();
        unset($result['portfolio_count']);

        return array_values($result);
    }
}
