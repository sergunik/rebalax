<?php
declare(strict_types=1);

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sign-in', fn() => view('auth/sign-in'))->name('sign-in');
Route::get('/sign-up', fn() => view('auth/sign-up'))->name('sign-up');

Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');
