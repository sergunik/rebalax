<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Balance;

class BalanceRepository
{
    public function findByToken(string $token): ?Balance
    {
        return Balance::where('token_name', $token)->first();
    }

    public function create(string $token, float $amount): Balance
    {
        return Balance::create([
            'token_name' => $token,
            'amount' => $amount,
        ]);
    }
}
