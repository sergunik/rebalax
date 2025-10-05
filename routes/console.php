<?php

use App\Console\Commands\CreateBotWithPortfolioCommand;
use App\Console\Commands\PriceCollectorCommand;
use App\Console\Commands\ReRunSimpleRebalanceCommand;
use App\Console\Commands\RunSimpleRebalanceCommand;
use Illuminate\Support\Facades\Schedule;

Schedule::command(PriceCollectorCommand::class)
    ->everyFiveMinutes()
//    ->runInBackground()
    ->withoutOverlapping();

//Schedule::command(CreateBotWithPortfolioCommand::class)
//    ->everyMinute()
////    ->runInBackground()
//    ->withoutOverlapping();

Schedule::command(RunSimpleRebalanceCommand::class)
    ->everyTwoMinutes()
    ->runInBackground()
    ->withoutOverlapping()
    ->when(function () {
        return config('rebalax.rebalance.simple.enabled', false);
    });

Schedule::command(ReRunSimpleRebalanceCommand::class)
    ->everyFiveMinutes()
    ->runInBackground()
    ->withoutOverlapping();
