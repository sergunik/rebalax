<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Balance;
use Illuminate\Database\Seeder;

class BalanceSeeder extends Seeder
{
    public function run(): void
    {
        Balance::factory()->count(1)->create();
    }
}
