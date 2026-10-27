<?php

use App\Http\Controllers\AccesoUnificadoController;
use App\Http\Controllers\RecuperacionContrasenaController;
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

Route::post('/cuentas/{usuario}/cambiar', [
    AccesoUnificadoController::class,
    'cambiarCuenta',
])
    ->middleware('auth')
    ->name('cuentas.cambiar');

// Un solo inicio de sesión: las URL antiguas de cada panel llevan a /login.
foreach (['admin', 'directivo', 'docente'] as $panel) {
    Route::redirect("/{$panel}/login", '/login');
}

// Recuperación de contraseña (nombres estándar de Laravel: la
// notificación ResetPassword usa la ruta password.reset).
Route::middleware('guest')->group(function (): void {
    Route::get('/recuperar-contrasena', [
        RecuperacionContrasenaController::class,
        'solicitar',
    ])->name('password.request');

    Route::post('/recuperar-contrasena', [
        RecuperacionContrasenaController::class,
        'enviar',
    ])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/restablecer-contrasena/{token}', [
        RecuperacionContrasenaController::class,
        'formulario',
    ])->name('password.reset');

    Route::post('/restablecer-contrasena', [
        RecuperacionContrasenaController::class,
        'restablecer',
    ])
        ->middleware('throttle:5,1')
        ->name('password.update');

    Route::get('/contrasena-actualizada', [
        RecuperacionContrasenaController::class,
        'listo',
    ])->name('password.listo');
});

Route::get('/usuarios/{usuario}/foto', \App\Http\Controllers\FotoPerfilController::class)
    ->middleware('auth')
    ->name('usuarios.foto');
