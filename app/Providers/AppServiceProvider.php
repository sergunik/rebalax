<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PriceCollector;
use App\Repositories\CachedTokenPriceRepository;
use App\Repositories\TokenPriceRepository;
use App\Services\ExmoPriceCollector;
use App\Contracts\RebalanceChecker;
use App\Services\SimpleRebalanceService;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\ServiceProvider;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis as PrometheusRedis;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PriceCollector::class, ExmoPriceCollector::class);
        $this->app->singleton(RebalanceChecker::class, SimpleRebalanceService::class);
        $this->app->singleton(TokenPriceRepository::class, function () {
            return new CachedTokenPriceRepository(
                app('App\Models\TokenPrice'),
                app(Repository::class),
                config('rebalax.price_collector.cache_ttl'),
            );
        });

        $this->app->singleton(CollectorRegistry::class, function () {
            return new CollectorRegistry(
                new PrometheusRedis([
                    'host' => config('app.redis.host'),
                    'port' => config('app.redis.port'),
                    'persistent_connections' => true,
                ])
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
