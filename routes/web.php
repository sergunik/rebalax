<?php
declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sign-in', fn() => view('auth/sign-in'))->name('sign-in');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/sign-up', fn() => view('auth/sign-up'))->name('sign-up');
Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
