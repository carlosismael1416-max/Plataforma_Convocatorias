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
    }

    public function boot(): void
    {
        //
    }
}
