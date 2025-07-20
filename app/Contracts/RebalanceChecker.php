<?php

declare(strict_types=1);

namespace App\Contracts;

interface RebalanceChecker
{
    public function do(int $batchOffset, int $batchSize): int;
}
