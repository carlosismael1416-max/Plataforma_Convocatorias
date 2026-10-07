<?php

namespace App\Filament\Docente\Pages;

use App\Models\Convocatoria;
use App\Models\Notificacion;
use App\Models\Propuesta;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class Notificaciones extends Page
{
    protected static ?int $navigationSort = 5;

    protected string $view =
        'filament.docente.pages.notificaciones';

    protected static ?string $slug =
        'notificaciones';

    public string $filtro = 'todas';

    public const FILTROS = [
        'todas',
        'sin_leer',
        'convocatorias',
        'propuestas',
        'fechas',
    ];

    public static function canAccess(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('DOCENTE');
    }

    public static function getNavigationBadge(): ?string
    {
        $usuario = auth()->user();

        if (! $usuario instanceof User) {
            return null;
        }

        $total = Notificacion::query()
            ->where(
                'user_id',
                $usuario->id
            )
            ->where(
                'leida',
                false
            )
            ->count();

        return $total > 0
            ? (string) $total
            : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public function getTitle(): string
    {
        return 'Notificaciones';
    }

    public function cambiarFiltro(
        string $filtro
    ): void {
        if (
            ! in_array(
                $filtro,
                self::FILTROS,
                true
            )
        ) {
            return;
        }

        $this->filtro = $filtro;
    }

    public function marcarLeida(
        int $notificacionId
    ): void {
        $notificacion =
            $this->notificacionDelUsuario(
                $notificacionId
            );

        if (! $notificacion->leida) {
            $notificacion->leida = true;
            $notificacion->fecha_lectura = now();
            $notificacion->save();
        }
    }

    public function marcarNoLeida(
        int $notificacionId
    ): void {
        $notificacion =
            $this->notificacionDelUsuario(
                $notificacionId
            );

        $notificacion->leida = false;
        $notificacion->fecha_lectura = null;
        $notificacion->save();
    }

    public function marcarTodasLeidas(): void
    {
        $actualizadas =
            Notificacion::query()
                ->where(
                    'user_id',
                    auth()->id()
                )
                ->where(
                    'leida',
                    false
                )
                ->update([
                    'leida' => true,
                    'fecha_lectura' => now(),
                    'updated_at' => now(),
                ]);

        if ($actualizadas === 0) {
            Notification::make()
                ->title(
                    'No hay notificaciones pendientes'
                )
                ->info()
                ->send();

            return;
        }

        Notification::make()
            ->title(
                'Notificaciones marcadas como leídas'
            )
            ->success()
            ->send();
    }

    public function abrirNotificacion(
        int $notificacionId
    ): mixed {
        $notificacion =
            $this->notificacionDelUsuario(
                $notificacionId
            );

        if (! $notificacion->leida) {
            $notificacion->leida = true;
            $notificacion->fecha_lectura = now();
            $notificacion->save();
        }

        /*
        |--------------------------------------------------------------------------
        | Propuesta
        |--------------------------------------------------------------------------
        |
        | Nunca confiamos solamente en propuesta_id.
        | La propuesta también debe pertenecer al
        | usuario autenticado.
        |--------------------------------------------------------------------------
        */

        if ($notificacion->propuesta_id) {
            $propuesta =
                Propuesta::query()
                    ->whereKey(
                        $notificacion
                            ->propuesta_id
                    )
                    ->where(
                        'user_id',
                        auth()->id()
                    )
                    ->first();

            if ($propuesta) {
                return redirect()->route(
                    'filament.docente.pages.vista-previa-propuesta',
                    [
                        'propuesta' =>
                            $propuesta->id,
                    ]
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Convocatoria
        |--------------------------------------------------------------------------
        */

        if ($notificacion->convocatoria_id) {
            $convocatoria =
                Convocatoria::query()
                    ->whereKey(
                        $notificacion
                            ->convocatoria_id
                    )
                    ->whereIn(
                        'estado',
                        [
                            'PUBLICADA',
                            'ACTIVA',
                        ]
                    )
                    ->first();

            if ($convocatoria) {
                return redirect()->route(
                    'filament.docente.pages.detalle-convocatoria',
                    [
                        'record' =>
                            $convocatoria->id,
                    ]
                );
            }

            /*
            | Puede tratarse de una convocatoria
            | que ya cerró o dejó de ser pública.
            | En ese caso regresamos al seguimiento
            | personal del Docente.
            */
            return redirect()->route(
                'filament.docente.pages.mis-convocatorias'
            );
        }

        Notification::make()
            ->title(
                'Notificación marcada como leída'
            )
            ->info()
            ->send();

        return null;
    }

    protected function getViewData(): array
    {
        $base =
            Notificacion::query()
                ->where(
                    'user_id',
                    auth()->id()
                );

        $total =
            (clone $base)->count();

        $sinLeer =
            (clone $base)
                ->where(
                    'leida',
                    false
                )
                ->count();

        $totalConvocatorias =
            (clone $base)
                ->whereNotNull(
                    'convocatoria_id'
                )
                ->count();

        $totalPropuestas =
            (clone $base)
                ->whereNotNull(
                    'propuesta_id'
                )
                ->count();

        $avisosFecha =
            (clone $base)
                ->whereIn(
                    'tipo',
                    [
                        'CONVOCATORIA_POR_CERRAR',
                        'PROPUESTA_POR_VENCER',
                    ]
                )
                ->count();

        $consulta =
            (clone $base)
                ->with([
                    'convocatoria',
                    'propuesta',
                ]);

        switch ($this->filtro) {
            case 'sin_leer':
                $consulta->where(
                    'leida',
                    false
                );
                break;

            case 'convocatorias':
                $consulta->whereNotNull(
                    'convocatoria_id'
                );
                break;

            case 'propuestas':
                $consulta->whereNotNull(
                    'propuesta_id'
                );
                break;

            case 'fechas':
                $consulta->whereIn(
                    'tipo',
                    [
                        'CONVOCATORIA_POR_CERRAR',
                        'PROPUESTA_POR_VENCER',
                    ]
                );
                break;
        }

        $notificaciones =
            $consulta
                ->orderByDesc(
                    'created_at'
                )
                ->orderByDesc('id')
                ->get();

        return [
            'notificaciones' =>
                $notificaciones,

            'total' =>
                $total,

            'sinLeer' =>
                $sinLeer,

            'totalConvocatorias' =>
                $totalConvocatorias,

            'totalPropuestas' =>
                $totalPropuestas,

            'avisosFecha' =>
                $avisosFecha,
        ];
    }

    private function notificacionDelUsuario(
        int $notificacionId
    ): Notificacion {
        return Notificacion::query()
            ->whereKey(
                $notificacionId
            )
            ->where(
                'user_id',
                auth()->id()
            )
            ->firstOrFail();
    }
}
