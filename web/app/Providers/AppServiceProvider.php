<?php

namespace App\Providers;

use App\Http\Responses\SalidaUnificada;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
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
        ResetPassword::toMailUsing(
            function (object $usuario, string $token): MailMessage {
                $minutos = config('auth.passwords.users.expire', 60);

                return (new MailMessage)
                    ->subject('Restablecer tu contraseña | Convocatorias ITSVA')
                    ->greeting('Hola')
                    ->line('Recibimos una solicitud para restablecer la contraseña de tu cuenta en la Plataforma de Gestión de Convocatorias del ITSVA.')
                    ->action(
                        'Crear nueva contraseña',
                        route('password.reset', [
                            'token' => $token,
                            'email' => $usuario->getEmailForPasswordReset(),
                        ])
                    )
                    ->line("El enlace vence en {$minutos} minutos.")
                    ->line('Si no lo solicitaste, ignora este correo: tu contraseña no cambiará.')
                    ->salutation('Instituto Tecnológico Superior de Valladolid');
            }
        );
    }
}
