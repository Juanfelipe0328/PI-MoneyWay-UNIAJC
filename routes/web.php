<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovementController;

// Página de inicio: manda al panel (si no hay sesión, Laravel lo lleva al login)
Route::get('/', function () {
    return redirect()->route('movements.index');
});

// Login (solo para visitantes)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Rutas protegidas (requieren iniciar sesión)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/registrar', [MovementController::class, 'index'])->name('movements.index');
    Route::post('/registrar', [MovementController::class, 'store'])->name('movements.store');
});