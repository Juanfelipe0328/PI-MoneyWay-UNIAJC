<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovementController;

// Pagina de inicio: manda al panel (si no hay sesion, Laravel lo lleva al login)
Route::get('/', function () {
    return redirect()->route('movements.index');
});

// Login (solo para visitantes)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Rutas protegidas: requieren iniciar sesion
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Panel y registro de movimientos
    Route::get('/registrar', [MovementController::class, 'index'])->name('movements.index');
    Route::post('/registrar', [MovementController::class, 'store'])->name('movements.store');

    // Editar y eliminar movimientos
    Route::get('/movimientos/{id}/editar', [MovementController::class, 'edit'])->name('movements.edit');
    Route::put('/movimientos/{id}', [MovementController::class, 'update'])->name('movements.update');
    Route::delete('/movimientos/{id}', [MovementController::class, 'destroy'])->name('movements.destroy');
});