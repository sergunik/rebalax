<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\PortfolioAnalysisDto;
use App\Models\Portfolio;

interface RebalanceChecker
{
    public function check(Portfolio $portfolio): PortfolioAnalysisDto;
}
