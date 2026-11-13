<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/registrar', [MovementController::class, 'create'])
        ->name('movements.create');

    Route::post('/registrar', [MovementController::class, 'store'])
        ->name('movements.store');

    Route::get('/historial', [MovementController::class, 'index'])
        ->name('movements.index');

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.post');
});