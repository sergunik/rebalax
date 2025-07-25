<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PriceCollector;
use App\Repositories\CachedTokenPriceRepository;
use App\Repositories\TokenPriceRepository;
use App\Services\ExmoPriceCollector;
use App\Contracts\RebalanceChecker;
use App\Services\SimpleRebalanceService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PriceCollector::class, ExmoPriceCollector::class);
        $this->app->singleton(RebalanceChecker::class, SimpleRebalanceService::class);
        $this->app->singleton(TokenPriceRepository::class, CachedTokenPriceRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
