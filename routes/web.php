<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\RecurringController;

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

    // Dashboard (acepta ?mes=2026-11)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/presupuestos', [DashboardController::class, 'storeBudget'])->name('budgets.store');
    Route::delete('/presupuestos/{id}', [DashboardController::class, 'destroyBudget'])->name('budgets.destroy');

    // Movimientos (acepta ?mes=2026-11)
    Route::get('/registrar', [MovementController::class, 'index'])->name('movements.index');
    Route::post('/registrar', [MovementController::class, 'store'])->name('movements.store');

    Route::get('/movimientos/{id}/editar', [MovementController::class, 'edit'])->name('movements.edit');
    Route::put('/movimientos/{id}', [MovementController::class, 'update'])->name('movements.update');
    Route::delete('/movimientos/{id}', [MovementController::class, 'destroy'])->name('movements.destroy');

    // Extracto mensual tipo banco (acepta ?mes=2026-11)
    Route::get('/extracto', [StatementController::class, 'index'])->name('statement.index');

    // Sueldo y gastos fijos (se registran solos cada mes)
    Route::get('/fijos', [RecurringController::class, 'index'])->name('recurring.index');
    Route::post('/fijos', [RecurringController::class, 'store'])->name('recurring.store');
    Route::get('/fijos/{id}/editar', [RecurringController::class, 'edit'])->name('recurring.edit');
    Route::put('/fijos/{id}', [RecurringController::class, 'update'])->name('recurring.update');
    Route::patch('/fijos/{id}/pausar', [RecurringController::class, 'toggle'])->name('recurring.toggle');
    Route::delete('/fijos/{id}', [RecurringController::class, 'destroy'])->name('recurring.destroy');
});
