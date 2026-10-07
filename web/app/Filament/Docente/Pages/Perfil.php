<?php

namespace App\Filament\Docente\Pages;

use App\Models\Departamento;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class Perfil extends Page
{
    protected static ?int $navigationSort = 6;

    protected string $view =
        'filament.docente.pages.perfil';

    protected static ?string $slug =
        'perfil';

    public string $name = '';

    public string $apellidos = '';

    public string $email = '';

    public string $departamentoId = '';

    public bool $mostrarCambioPassword = false;

    public string $passwordActual = '';

    public string $passwordNueva = '';

    public string $passwordNuevaConfirmacion = '';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DOCENTE');
    }

    public function getTitle(): string
    {
        return 'Perfil de Usuario';
    }

    public function mount(): void
    {
        $this->cargarFormulario();
    }

    public function guardarCambios(): void
    {
        $usuario =
            $this->usuarioActual();

        $this->resetErrorBag();

        $this->validate([
            'name' => [
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

            'departamentoId' => [
                'nullable',
                'integer',

                Rule::exists(
                    'departamentos',
                    'id'
                )->where(
                    fn ($query) =>
                        $query->where(
                            'estado',
                            true
                        )
                ),
            ],
        ]);

        $usuario->name =
            trim($this->name);

        $usuario->apellidos =
            $this->textoONull(
                $this->apellidos
            );

        $usuario->email =
            trim($this->email);

        $usuario->departamento_id =
            $this->departamentoId !== ''
                ? (int)
                    $this->departamentoId
                : null;

        /*
        |--------------------------------------------------------------------------
        | Importante
        |--------------------------------------------------------------------------
        |
        | No se modifican:
        | role_id
        | estado
        | ultimo_acceso
        |
        | Son datos administrados por el sistema
        | o por el Administrador.
        |--------------------------------------------------------------------------
        */

        $usuario->save();

        $this->cargarFormulario();

        Notification::make()
            ->title(
                'Perfil actualizado'
            )
            ->body(
                'Los datos de tu cuenta se guardaron correctamente.'
            )
            ->success()
            ->send();
    }

    public function cancelarCambios(): void
    {
        $this->resetErrorBag();

        $this->cargarFormulario();

        Notification::make()
            ->title(
                'Cambios descartados'
            )
            ->info()
            ->send();
    }

    public function abrirCambioPassword(): void
    {
        $this->resetErrorBag();

        $this->limpiarPassword();

        $this->mostrarCambioPassword =
            true;
    }

    public function cerrarCambioPassword(): void
    {
        $this->resetErrorBag();

        $this->limpiarPassword();

        $this->mostrarCambioPassword =
            false;
    }

    public function cambiarPassword(): void
    {
        $usuario =
            $this->usuarioActual();

        $this->resetErrorBag();

        $this->validate([
            'passwordActual' => [
                'required',
                'string',
            ],

            'passwordNueva' => [
                'required',
                'string',
                Password::min(8),
            ],

            'passwordNuevaConfirmacion' => [
                'required',
                'string',
                'same:passwordNueva',
            ],
        ], [
            'passwordActual.required' =>
                'Ingresa tu contraseña actual.',

            'passwordNueva.required' =>
                'Ingresa la nueva contraseña.',

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

        /*
        | User tiene cast:
        | 'password' => 'hashed'
        |
        | Por eso Eloquent realiza el hash.
        */
        $usuario->password =
            $this->passwordNueva;

        $usuario->save();

        $this->limpiarPassword();

        $this->mostrarCambioPassword =
            false;

        Notification::make()
            ->title(
                'Contraseña actualizada'
            )
            ->body(
                'Tu contraseña fue cambiada correctamente.'
            )
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $usuario =
            User::query()
                ->with([
                    'role',
                    'departamento',
                ])
                ->whereKey(
                    auth()->id()
                )
                ->firstOrFail();

        $departamentos =
            Departamento::query()
                ->where(
                    'estado',
                    true
                )
                ->orderBy('nombre')
                ->get([
                    'id',
                    'nombre',
                ]);

        $nombreCompleto =
            trim(
                (string) $usuario->name
                . ' '
                . (string) (
                    $usuario->apellidos
                    ?? ''
                )
            );

        $iniciales =
            collect(
                preg_split(
                    '/\s+/u',
                    $nombreCompleto,
                    -1,
                    PREG_SPLIT_NO_EMPTY
                )
            )
                ->take(2)
                ->map(
                    fn (
                        string $parte
                    ): string =>
                        mb_strtoupper(
                            mb_substr(
                                $parte,
                                0,
                                1
                            )
                        )
                )
                ->implode('');

        if ($iniciales === '') {
            $iniciales = 'U';
        }

        return [
            'usuario' =>
                $usuario,

            'departamentos' =>
                $departamentos,

            'nombreCompleto' =>
                $nombreCompleto,

            'iniciales' =>
                $iniciales,
        ];
    }

    private function usuarioActual(): User
    {
        return User::query()
            ->whereKey(
                auth()->id()
            )
            ->firstOrFail();
    }

    private function cargarFormulario(): void
    {
        $usuario =
            $this->usuarioActual();

        $this->name =
            (string) $usuario->name;

        $this->apellidos =
            (string) (
                $usuario->apellidos
                ?? ''
            );

        $this->email =
            (string) $usuario->email;

        $this->departamentoId =
            $usuario->departamento_id
                ? (string)
                    $usuario
                        ->departamento_id
                : '';
    }

    private function limpiarPassword(): void
    {
        $this->passwordActual = '';

        $this->passwordNueva = '';

        $this->passwordNuevaConfirmacion =
            '';
    }

    private function textoONull(
        string $texto
    ): ?string {
        $texto = trim($texto);

        return $texto !== ''
            ? $texto
            : null;
    }
}
