<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RefreshPortfolioAssetsFlatCommand extends Command
{
    protected $signature = 'app:refresh-portfolio-assets-flat';

    protected $description = 'Refresh the tmp_portfolio_assets_flat table with the latest data';

    public function handle(): int
    {
        DB::table('tmp_portfolio_assets_flat')->truncate();

        $query = '
            WITH ranked_assets AS (
                SELECT
                    a.portfolio_id,
                    a.token_symbol,
                    ROW_NUMBER() OVER (
                        PARTITION BY a.portfolio_id
                        ORDER BY a.token_symbol ASC
                    ) AS rn
                FROM portfolio_assets a
                JOIN portfolios p ON p.id = a.portfolio_id
            )
            SELECT
                p.id AS portfolio_id,
                MAX(r.rn) as asset_count,
                MAX(CASE WHEN r.rn = 1 THEN r.token_symbol END) AS asset_1,
                MAX(CASE WHEN r.rn = 2 THEN r.token_symbol END) AS asset_2,
                MAX(CASE WHEN r.rn = 3 THEN r.token_symbol END) AS asset_3,
                MAX(CASE WHEN r.rn = 4 THEN r.token_symbol END) AS asset_4,
                MAX(CASE WHEN r.rn = 5 THEN r.token_symbol END) AS asset_5,
                MAX(CASE WHEN r.rn = 6 THEN r.token_symbol END) AS asset_6,
                MAX(CASE WHEN r.rn = 7 THEN r.token_symbol END) AS asset_7,
                MAX(CASE WHEN r.rn = 8 THEN r.token_symbol END) AS asset_8,
                MAX(CASE WHEN r.rn = 9 THEN r.token_symbol END) AS asset_9,
                MAX(CASE WHEN r.rn = 10 THEN r.token_symbol END) AS asset_10
            FROM portfolios p
            JOIN ranked_assets r ON r.portfolio_id = p.id
            GROUP BY p.id
        ';

        DB::statement(
            sprintf('INSERT INTO tmp_portfolio_assets_flat
                (portfolio_id, asset_count, asset_1, asset_2, asset_3, asset_4, asset_5, asset_6, asset_7, asset_8, asset_9, asset_10)
                %s', $query)
        );

        return Command::SUCCESS;
    }
}
