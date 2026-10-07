<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Convocatoria;
use App\Models\ConvocatoriaRevisionAdministrativa;
use App\Models\Departamento;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Perfil extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationLabel = 'Perfil';

    protected static ?int $navigationSort = 8;


    protected string $view =
        'filament.directivo.pages.perfil';

    protected static ?string $slug =
        'perfil';

    public string $nombre = '';

    public string $apellidos = '';

    public string $email = '';

    public string $departamentoId = '';

    public bool $editando = false;

    public bool $mostrarCambioPassword = false;

    public string $contrasenaActual = '';

    public string $nuevaContrasena = '';

    public string $confirmacionContrasena = '';

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol(
                'DIRECTIVO'
            );
    }

    public function mount(): void
    {
        $this->cargarUsuario();
    }

    public function getTitle(): string
    {
        return 'Perfil';
    }

    public function editar(): void
    {
        $this->editando = true;
    }

    public function cancelarEdicion(): void
    {
        $this->resetValidation();

        $this->cargarUsuario();

        $this->editando = false;
    }

    public function guardarPerfil(): void
    {
        $usuario =
            $this->usuarioActual();

        $datos =
            $this->validate([
                'nombre' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'apellidos' => [
                    'required',
                    'string',
                    'max:255',
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
                    'exists:departamentos,id',
                ],
            ]);

        $usuario->update([
            'name' =>
                trim(
                    $datos[
                        'nombre'
                    ]
                ),

            'apellidos' =>
                trim(
                    $datos[
                        'apellidos'
                    ]
                ),

            'email' =>
                trim(
                    $datos[
                        'email'
                    ]
                ),

            'departamento_id' =>
                $datos[
                    'departamentoId'
                ] !== ''
                    ? (int)
                    $datos[
                        'departamentoId'
                    ]
                    : null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | IMPORTANTE
        |--------------------------------------------------------------------------
        |
        | role_id y estado NO se modifican desde Perfil.
        | Esos campos pertenecen a la administración de usuarios.
        |
        */

        $this->editando = false;

        $this->cargarUsuario();

        Notification::make()
            ->title(
                'Perfil actualizado'
            )
            ->body(
                'La información de tu cuenta fue guardada correctamente.'
            )
            ->success()
            ->send();
    }

    public function alternarCambioPassword(): void
    {
        $this->resetValidation();

        $this->mostrarCambioPassword =
            ! $this->mostrarCambioPassword;

        if (
            ! $this->mostrarCambioPassword
        ) {
            $this->limpiarPassword();
        }
    }

    public function cambiarPassword(): void
    {
        $this->validate([
            'contrasenaActual' => [
                'required',
                'string',
            ],

            'nuevaContrasena' => [
                'required',
                'string',
                'min:8',
                'same:confirmacionContrasena',
            ],

            'confirmacionContrasena' => [
                'required',
                'string',
                'min:8',
            ],
        ]);

        $usuario =
            $this->usuarioActual();

        if (
            ! Hash::check(
                $this->contrasenaActual,
                $usuario->password
            )
        ) {
            $this->addError(
                'contrasenaActual',
                'La contraseña actual no es correcta.'
            );

            return;
        }

        $usuario->password =
            $this->nuevaContrasena;

        $usuario->save();

        $this->limpiarPassword();

        $this->mostrarCambioPassword =
            false;

        Notification::make()
            ->title(
                'Contraseña actualizada'
            )
            ->body(
                'Tu contraseña fue modificada correctamente.'
            )
            ->success()
            ->send();
    }

    protected function getViewData(): array
    {
        $usuario =
            $this->usuarioActual()
                ->load([
                    'role',
                    'departamento',
                ]);

        $departamentos =
            Departamento::query()
                ->orderBy(
                    'nombre'
                )
                ->get();

        $nombreCompleto =
            trim(
                $usuario->name
                . ' '
                . $usuario->apellidos
            );

        $iniciales =
            $this->obtenerIniciales(
                $usuario->name,
                $usuario->apellidos
            );

        $actividad =
            $this->actividadReciente(
                $usuario
            );

        return [
            'usuario' =>
                $usuario,

            'departamentos' =>
                $departamentos,

            'nombreCompleto' =>
                $nombreCompleto,

            'iniciales' =>
                $iniciales,

            'actividad' =>
                $actividad,
        ];
    }

    private function cargarUsuario(): void
    {
        $usuario =
            $this->usuarioActual();

        $this->nombre =
            (string)
            $usuario->name;

        $this->apellidos =
            (string)
            $usuario->apellidos;

        $this->email =
            (string)
            $usuario->email;

        $this->departamentoId =
            $usuario->departamento_id
                !== null
                    ? (string)
                    $usuario->departamento_id
                    : '';
    }

    private function usuarioActual(): User
    {
        return User::query()
            ->whereKey(
                auth()->id()
            )
            ->firstOrFail();
    }

    private function limpiarPassword(): void
    {
        $this->contrasenaActual = '';
        $this->nuevaContrasena = '';
        $this->confirmacionContrasena = '';
    }

    private function obtenerIniciales(
        ?string $nombre,
        ?string $apellidos
    ): string {
        $primera =
            mb_substr(
                trim(
                    (string) $nombre
                ),
                0,
                1
            );

        $segunda =
            mb_substr(
                trim(
                    (string) $apellidos
                ),
                0,
                1
            );

        $resultado =
            mb_strtoupper(
                $primera
                . $segunda
            );

        return $resultado !== ''
            ? $resultado
            : 'U';
    }

    private function actividadReciente(
        User $usuario
    ) {
        $subidas =
            Convocatoria::query()
                ->where(
                    'subido_por_user_id',
                    $usuario->id
                )
                ->latest(
                    'created_at'
                )
                ->limit(
                    5
                )
                ->get()
                ->map(
                    function (
                        Convocatoria $convocatoria
                    ): array {
                        $url =
                            $convocatoria->estado
                            === 'PENDIENTE_REVISION'
                                ? route(
                                    'filament.directivo.pages.revisar-convocatoria',
                                    [
                                        'record' =>
                                            $convocatoria->id,
                                    ]
                                )
                                : route(
                                    'filament.directivo.pages.detalle-convocatoria',
                                    [
                                        'record' =>
                                            $convocatoria->id,
                                    ]
                                );

                        return [
                            'tipo' =>
                                'C',

                            'titulo' =>
                                'Registraste una convocatoria',

                            'detalle' =>
                                $convocatoria->titulo,

                            'fecha_orden' =>
                                $convocatoria->created_at,

                            'fecha' =>
                                $convocatoria
                                    ->created_at
                                    ?->locale('es')
                                    ->diffForHumans()
                                ?? 'Sin fecha',

                            'url' =>
                                $url,
                        ];
                    }
                );

        $revisiones =
            ConvocatoriaRevisionAdministrativa::query()
                ->with(
                    'convocatoria'
                )
                ->where(
                    'revisor_id',
                    $usuario->id
                )
                ->latest(
                    'fecha_decision'
                )
                ->limit(
                    5
                )
                ->get()
                ->map(
                    function (
                        ConvocatoriaRevisionAdministrativa $revision
                    ): array {
                        $convocatoria =
                            $revision->convocatoria;

                        return [
                            'tipo' =>
                                'R',

                            'titulo' =>
                                'Registraste una revisión',

                            'detalle' =>
                                $convocatoria?->titulo
                                ?? 'Convocatoria',

                            'fecha_orden' =>
                                $revision->fecha_decision,

                            'fecha' =>
                                $revision
                                    ->fecha_decision
                                    ?->locale('es')
                                    ->diffForHumans()
                                ?? 'Sin fecha',

                            'url' =>
                                $convocatoria
                                    ? route(
                                        'filament.directivo.pages.detalle-convocatoria',
                                        [
                                            'record' =>
                                                $convocatoria->id,
                                        ]
                                    )
                                    : null,
                        ];
                    }
                );

        return $subidas
            ->merge(
                $revisiones
            )
            ->sortByDesc(
                'fecha_orden'
            )
            ->take(
                5
            )
            ->values();
    }
}
