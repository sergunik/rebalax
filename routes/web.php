<?php

declare(strict_types=1);

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

//Route::get('/login', fn() => view('auth/login'))->name('login');
//Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
//
//Route::get('/register', fn() => view('auth/register'))->name('register');
//Route::post('/register', [AuthController::class, 'register'])->name('register.store');
//
//Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
//
//Route::middleware(['auth'])->group(function () {
//    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
//});

Route::get('/metrics', MetricsController::class)->name('metrics');
