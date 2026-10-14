<?php

namespace App\Providers;

use App\Http\Responses\SalidaUnificada;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            LogoutResponse::class,
            SalidaUnificada::class
        );

        // Todas las llaves son enteras: sin esto, /recurso/create (o
        // cualquier texto) caía en la ruta de detalle y PostgreSQL
        // respondía 500 en lugar de 404. Va en register() porque
        // Filament crea sus rutas antes del boot() de la aplicación.
        $this->app['router']->pattern('record', '[0-9]+');
    }

    public function boot(): void
    {
        //
    }
}
