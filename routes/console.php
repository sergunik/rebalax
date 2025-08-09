<?php

use App\Console\Commands\CreateBotWithPortfolioCommand;
use App\Console\Commands\PriceCollectorCommand;
use App\Console\Commands\RunSimpleRebalanceCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(PriceCollectorCommand::class)
    ->everyFiveMinutes()
    ->runInBackground()
    ->withoutOverlapping();

Schedule::command(CreateBotWithPortfolioCommand::class)
    ->everyTenMinutes()
    ->runInBackground()
    ->withoutOverlapping();

Schedule::command(RunSimpleRebalanceCommand::class)
    ->everyThirtyMinutes()
    ->runInBackground()
    ->withoutOverlapping()
    ->when(function () {
        return config('rebalax.rebalance.simple.enabled', false);
    });
