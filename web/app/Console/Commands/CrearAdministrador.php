<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

/**
 * Crea (o reactiva) la cuenta de administrador en una instalación nueva.
 * La contraseña se pide de forma oculta; nunca va en la línea de comandos.
 */
class CrearAdministrador extends Command
{
    protected $signature = 'itsva:crear-admin {email : Correo institucional} {--nombre=Administrador : Nombre}';

    protected $description = 'Crea la cuenta de administrador de la plataforma';

    public function handle(): int
    {
        $rol = Role::query()->where('nombre', 'ADMINISTRADOR')->first();

        if (! $rol) {
            $this->error('No existe el rol ADMINISTRADOR. Ejecuta antes: php artisan db:seed');

            return self::FAILURE;
        }

        $email = strtolower(trim((string) $this->argument('email')));
        $password = (string) $this->secret('Contraseña (mínimo 8, con letras y números)');

        $validacion = Validator::make(
            ['email' => $email, 'password' => $password],
            ['email' => ['required', 'email'], 'password' => ['required', Password::min(8)->letters()->numbers()]],
        );

        if ($validacion->fails()) {
            foreach ($validacion->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $usuario = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => (string) $this->option('nombre'),
                'password' => Hash::make($password),
                'role_id' => $rol->id,
                'estado' => true,
            ],
        );

        $this->info("Administrador listo: {$usuario->email}. Entra en /login.");

        return self::SUCCESS;
    }
}
