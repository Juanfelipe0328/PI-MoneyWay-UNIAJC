<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovementController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/registrar', [MovementController::class, 'create']);
Route::post('/registrar', [MovementController::class, 'store'])->name('movements.store');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');