<?php

namespace App\Filament\Admin\Pages;

use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Perfil extends Page
{
    protected string $view =
        'filament.admin.pages.perfil';

    protected static ?string $navigationLabel =
        'Perfil';

    protected static ?string $slug =
        'perfil';

    protected static ?int $navigationSort =
        8;

    public string $nombre = '';

    public ?string $apellidos = null;

    public string $email = '';

    public string $passwordActual = '';

    public string $passwordNueva = '';

    public string $passwordNuevaConfirmacion = '';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'ADMINISTRADOR'
            );
    }

    public function mount(): void
    {
        $usuario =
            $this->usuarioAutenticado();

        $this->nombre =
            (string) $usuario->name;

        $this->apellidos =
            $usuario->apellidos;

        $this->email =
            (string) $usuario->email;
    }

    public function getTitle(): string
    {
        return 'Perfil';
    }

    public function getSubheading(): ?string
    {
        return 'Consulta y actualiza la información principal de tu cuenta administrativa.';
    }

    public function guardarPerfil(): void
    {
        $usuario =
            $this->usuarioAutenticado();

        $datos =
            $this->validate([
                'nombre' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'apellidos' => [
                    'nullable',
                    'string',
                    'max:150',
                ],

                'email' => [
                    'required',
                    'email',
                    'max:255',

                    Rule::unique(
                        'users',
                        'email'
                    )->ignore(
                        $usuario->id
                    ),
                ],
            ], [
                'nombre.required' =>
                    'El nombre es obligatorio.',

                'email.required' =>
                    'El correo es obligatorio.',

                'email.email' =>
                    'Ingresa un correo válido.',

                'email.unique' =>
                    'Ese correo ya está registrado.',
            ]);

        $usuario->update([
            'name' =>
                trim(
                    $datos['nombre']
                ),

            'apellidos' =>
                filled(
                    $datos['apellidos']
                )
                    ? trim(
                        $datos['apellidos']
                    )
                    : null,

            'email' =>
                trim(
                    $datos['email']
                ),
        ]);

        $this->nombre =
            (string) $usuario->name;

        $this->apellidos =
            $usuario->apellidos;

        $this->email =
            (string) $usuario->email;

        Notification::make()
            ->title(
                'Perfil actualizado'
            )
            ->body(
                'Tus datos personales se guardaron correctamente.'
            )
            ->success()
            ->send();
    }

    public function cambiarPassword(): void
    {
        $usuario =
            $this->usuarioAutenticado();

        $this->validate([
            'passwordActual' => [
                'required',
                'string',
            ],

            'passwordNueva' => [
                'required',
                'string',
                'min:8',
                'max:255',
            ],

            'passwordNuevaConfirmacion' => [
                'required',
                'same:passwordNueva',
            ],
        ], [
            'passwordActual.required' =>
                'Escribe tu contraseña actual.',

            'passwordNueva.required' =>
                'Escribe la nueva contraseña.',

            'passwordNueva.min' =>
                'La nueva contraseña debe tener al menos 8 caracteres.',

            'passwordNuevaConfirmacion.required' =>
                'Confirma la nueva contraseña.',

            'passwordNuevaConfirmacion.same' =>
                'Las contraseñas nuevas no coinciden.',
        ]);

        if (
            ! Hash::check(
                $this->passwordActual,
                $usuario->password
            )
        ) {
            $this->addError(
                'passwordActual',
                'La contraseña actual no es correcta.'
            );

            return;
        }

        $usuario->password =
            $this->passwordNueva;

        $usuario->save();

        $this->passwordActual = '';
        $this->passwordNueva = '';
        $this->passwordNuevaConfirmacion = '';

        Notification::make()
            ->title(
                'Contraseña actualizada'
            )
            ->body(
                'Tu contraseña se cambió correctamente.'
            )
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $usuario =
            $this->usuarioAutenticado();

        $usuario->load([
            'role',
            'departamento',
        ]);

        $permisos =
            $usuario->role
                ? $usuario
                    ->role
                    ->permisos()
                    ->orderBy('nombre')
                    ->pluck('nombre')
                : collect();

        $nombreCompleto =
            trim(
                implode(
                    ' ',
                    array_filter([
                        $usuario->name,
                        $usuario->apellidos,
                    ])
                )
            );

        $iniciales =
            mb_strtoupper(
                mb_substr(
                    (string) $usuario->name,
                    0,
                    1
                )
                .
                (
                    filled(
                        $usuario->apellidos
                    )
                        ? mb_substr(
                            (string)
                            $usuario->apellidos,
                            0,
                            1
                        )
                        : ''
                )
            );

        return [
            'usuario' =>
                $usuario,

            'permisos' =>
                $permisos,

            'nombreCompleto' =>
                $nombreCompleto,

            'iniciales' =>
                $iniciales,
        ];
    }

    private function usuarioAutenticado(): User
    {
        $usuario =
            auth()->user();

        abort_unless(
            $usuario instanceof User
                && (bool) $usuario->estado
                && $usuario->tieneRol(
                    'ADMINISTRADOR'
                ),
            403
        );

        return $usuario;
    }
}
