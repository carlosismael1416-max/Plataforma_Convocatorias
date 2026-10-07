<?php

namespace App\Filament\Directivo\Pages;

use App\Models\Convocatoria;
use App\Models\Notificacion;
use App\Models\NotificacionPreferencia;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class Notificaciones extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-bell';

    protected static ?string $navigationLabel = 'Notificaciones';

    protected static ?int $navigationSort = 7;


    protected string $view =
        'filament.directivo.pages.notificaciones';

    protected static ?string $slug =
        'notificaciones';

    public string $filtro = 'todas';

    public bool $mostrarConfiguracion =
        false;

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
        NotificacionPreferencia::firstOrCreate(
            [
                'user_id' =>
                    auth()->id(),
            ],
            [
                'convocatorias_revision' =>
                    true,

                'fechas_proximas' =>
                    true,

                'nuevas_propuestas' =>
                    true,

                'avisos_sistema' =>
                    false,
            ]
        );
    }

    public function getTitle(): string
    {
        return 'Notificaciones';
    }

    public function establecerFiltro(
        string $filtro
    ): void {
        if (
            ! in_array(
                $filtro,
                [
                    'todas',
                    'sin_leer',
                    'revisiones',
                    'convocatorias',
                    'sistema',
                    'hoy',
                ],
                true
            )
        ) {
            return;
        }

        $this->filtro =
            $filtro;
    }

    public function alternarConfiguracion(): void
    {
        $this->mostrarConfiguracion =
            ! $this->mostrarConfiguracion;
    }

    public function marcarComoLeida(
        int $id
    ): void {
        $notificacion =
            $this->notificacionDelUsuario(
                $id
            );

        if ($notificacion->leida) {
            return;
        }

        $notificacion->update([
            'leida' =>
                true,

            'fecha_lectura' =>
                now(),
        ]);
    }

    public function marcarTodasComoLeidas(): void
    {
        $cantidad =
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
                    'leida' =>
                        true,

                    'fecha_lectura' =>
                        now(),

                    'updated_at' =>
                        now(),
                ]);

        Notification::make()
            ->title(
                $cantidad > 0
                    ? 'Notificaciones actualizadas'
                    : 'No hay notificaciones sin leer'
            )
            ->body(
                $cantidad > 0
                    ? sprintf(
                        '%d notificación(es) marcadas como leídas.',
                        $cantidad
                    )
                    : 'Todas las notificaciones ya estaban leídas.'
            )
            ->success()
            ->send();
    }

    public function abrirNotificacion(
        int $id
    ): void {
        $notificacion =
            $this->notificacionDelUsuario(
                $id
            );

        if (
            ! $notificacion->leida
        ) {
            $notificacion->update([
                'leida' =>
                    true,

                'fecha_lectura' =>
                    now(),
            ]);
        }

        $url =
            $this->urlNotificacion(
                $notificacion
            );

        if ($url === null) {
            Notification::make()
                ->title(
                    'Sin página relacionada'
                )
                ->body(
                    'Esta notificación no tiene un destino asociado.'
                )
                ->info()
                ->send();

            return;
        }

        $this->redirect(
            $url,
            navigate: true
        );
    }

    public function alternarPreferencia(
        string $campo
    ): void {
        if (
            ! in_array(
                $campo,
                [
                    'convocatorias_revision',
                    'fechas_proximas',
                    'nuevas_propuestas',
                    'avisos_sistema',
                ],
                true
            )
        ) {
            return;
        }

        $preferencia =
            $this->preferencias();

        $preferencia->update([
            $campo =>
                ! (bool)
                $preferencia
                    ->getAttribute(
                        $campo
                    ),
        ]);
    }

    protected function getViewData(): array
    {
        $usuarioId =
            (int) auth()->id();

        $base =
            Notificacion::query()
                ->where(
                    'user_id',
                    $usuarioId
                );

        $sinLeer =
            (clone $base)
                ->where(
                    'leida',
                    false
                )
                ->count();

        $total =
            (clone $base)
                ->count();

        $actividadHoy =
            (clone $base)
                ->whereDate(
                    'created_at',
                    today()
                )
                ->count();

        $porRevisar =
            Convocatoria::query()
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->count();

        $proximosCierres =
            Convocatoria::query()
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->whereNotNull(
                    'fecha_cierre'
                )
                ->whereBetween(
                    'fecha_cierre',
                    [
                        today(),
                        today()
                            ->copy()
                            ->addDays(
                                30
                            ),
                    ]
                )
                ->count();

        $query =
            Notificacion::query()
                ->with([
                    'convocatoria',
                    'propuesta',
                ])
                ->where(
                    'user_id',
                    $usuarioId
                );

        $this->aplicarFiltro(
            $query
        );

        $notificaciones =
            $query
                ->latest()
                ->paginate(
                    10
                );

        $notificaciones
            ->getCollection()
            ->transform(
                fn (
                    Notificacion $item
                ): array =>
                    $this->presentar(
                        $item
                    )
            );

        return [
            'notificaciones' =>
                $notificaciones,

            'total' =>
                $total,

            'sinLeer' =>
                $sinLeer,

            'actividadHoy' =>
                $actividadHoy,

            'porRevisar' =>
                $porRevisar,

            'proximosCierres' =>
                $proximosCierres,

            'preferencias' =>
                $this->preferencias(),
        ];
    }

    private function aplicarFiltro(
        Builder $query
    ): void {
        match ($this->filtro) {
            'sin_leer' =>
                $query->where(
                    'leida',
                    false
                ),

            'revisiones' =>
                $query->where(
                    function (
                        Builder $q
                    ): void {
                        $q
                            ->whereRaw(
                                "
                                UPPER(
                                    COALESCE(
                                        tipo,
                                        ''
                                    )
                                )
                                LIKE '%REVISION%'
                                "
                            )
                            ->orWhereRaw(
                                "
                                UPPER(
                                    COALESCE(
                                        tipo,
                                        ''
                                    )
                                )
                                LIKE '%CORRECCION%'
                                "
                            )
                            ->orWhereRaw(
                                "
                                UPPER(
                                    COALESCE(
                                        tipo,
                                        ''
                                    )
                                )
                                LIKE '%APROB%'
                                "
                            );
                    }
                ),

            'convocatorias' =>
                $query->whereNotNull(
                    'convocatoria_id'
                ),

            'sistema' =>
                $query->where(
                    function (
                        Builder $q
                    ): void {
                        $q
                            ->whereRaw(
                                "
                                UPPER(
                                    COALESCE(
                                        tipo,
                                        ''
                                    )
                                )
                                LIKE '%SISTEMA%'
                                "
                            )
                            ->orWhereRaw(
                                "
                                UPPER(
                                    COALESCE(
                                        tipo,
                                        ''
                                    )
                                )
                                LIKE '%EXTRACCION%'
                                "
                            );
                    }
                ),

            'hoy' =>
                $query->whereDate(
                    'created_at',
                    today()
                ),

            default =>
                null,
        };
    }

    private function presentar(
        Notificacion $item
    ): array {
        [
            $icono,
            $color,
        ] = $this->apariencia(
            $item
        );

        return [
            'id' =>
                $item->id,

            'titulo' =>
                $item->titulo,

            'mensaje' =>
                $item->mensaje,

            'tipo' =>
                $item->tipo,

            'leida' =>
                (bool)
                $item->leida,

            'fecha' =>
                $item->created_at
                    ?->locale('es')
                    ->diffForHumans()
                ?? 'Sin fecha',

            'icono' =>
                $icono,

            'color' =>
                $color,

            'tiene_destino' =>
                $this->urlNotificacion(
                    $item
                ) !== null,
        ];
    }

    private function apariencia(
        Notificacion $item
    ): array {
        $tipo =
            strtoupper(
                (string)
                $item->tipo
            );

        if (
            str_contains(
                $tipo,
                'PROPUESTA'
            )
        ) {
            return [
                'P',
                'purple',
            ];
        }

        if (
            str_contains(
                $tipo,
                'CIERRE'
            )
            ||
            str_contains(
                $tipo,
                'FECHA'
            )
        ) {
            return [
                '!',
                'yellow',
            ];
        }

        if (
            str_contains(
                $tipo,
                'ERROR'
            )
            ||
            str_contains(
                $tipo,
                'CORRECCION'
            )
        ) {
            return [
                '!',
                'red',
            ];
        }

        if (
            str_contains(
                $tipo,
                'APROB'
            )
            ||
            str_contains(
                $tipo,
                'REVISION'
            )
        ) {
            return [
                '✓',
                'green',
            ];
        }

        if (
            str_contains(
                $tipo,
                'SISTEMA'
            )
            ||
            str_contains(
                $tipo,
                'EXTRACCION'
            )
        ) {
            return [
                'S',
                'yellow',
            ];
        }

        return [
            'C',
            'purple',
        ];
    }

    private function urlNotificacion(
        Notificacion $item
    ): ?string {
        if (
            $item->convocatoria_id
            !== null
        ) {
            $convocatoria =
                $item->convocatoria;

            if (
                $convocatoria
                &&
                $convocatoria->estado
                    === 'PENDIENTE_REVISION'
            ) {
                return route(
                    'filament.directivo.pages.revisar-convocatoria',
                    [
                        'record' =>
                            $convocatoria->id,
                    ]
                );
            }

            if ($convocatoria) {
                return route(
                    'filament.directivo.pages.detalle-convocatoria',
                    [
                        'record' =>
                            $convocatoria->id,
                    ]
                );
            }
        }

        if (
            $item->propuesta_id
            !== null
        ) {
            return route(
                'filament.directivo.pages.reportes',
                [
                    'seccion' =>
                        'propuestas',
                ]
            );
        }

        return null;
    }

    private function preferencias():
        NotificacionPreferencia
    {
        return NotificacionPreferencia::firstOrCreate(
            [
                'user_id' =>
                    auth()->id(),
            ]
        );
    }

    private function notificacionDelUsuario(
        int $id
    ): Notificacion {
        return Notificacion::query()
            ->where(
                'user_id',
                auth()->id()
            )
            ->findOrFail(
                $id
            );
    }
}
