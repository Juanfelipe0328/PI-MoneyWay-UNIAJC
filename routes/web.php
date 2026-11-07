<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovementController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/registrar', [MovementController::class, 'create']);
Route::post('/registrar', [MovementController::class, 'store'])->name('movements.store');