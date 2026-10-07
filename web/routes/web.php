<?php

use App\Http\Controllers\AccesoUnificadoController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::get('/login', [
    AccesoUnificadoController::class,
    'mostrar',
])->name('login');

Route::post('/login', [
    AccesoUnificadoController::class,
    'ingresar',
])
    ->middleware('throttle:5,1')
    ->name('login.submit');

Route::post('/logout', [
    AccesoUnificadoController::class,
    'salir',
])
    ->middleware('auth')
    ->name('logout');
