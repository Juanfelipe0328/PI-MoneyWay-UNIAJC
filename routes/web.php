<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\DashboardController;

// Inicio: manda al dashboard (si no hay sesión, Laravel lo lleva al login)
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Solo visitantes (sin sesión iniciada)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/registro', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->name('register.post');
});

// Solo usuarios con sesión iniciada
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/presupuestos', [DashboardController::class, 'storeBudget'])->name('budgets.store');
    Route::delete('/presupuestos/{id}', [DashboardController::class, 'destroyBudget'])->name('budgets.destroy');

    Route::get('/registrar', [MovementController::class, 'index'])->name('movements.index');
    Route::post('/registrar', [MovementController::class, 'store'])->name('movements.store');

    Route::get('/movimientos/{id}/editar', [MovementController::class, 'edit'])->name('movements.edit');
    Route::put('/movimientos/{id}', [MovementController::class, 'update'])->name('movements.update');
    Route::delete('/movimientos/{id}', [MovementController::class, 'destroy'])->name('movements.destroy');
});
