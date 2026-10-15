<?php

namespace App\Filament\Docente;

use App\Filament\Docente\Pages;
use App\Models\Convocatoria;
use App\Models\Propuesta;
use App\Models\User;
use App\Models\UsuarioConvocatoria;
use App\Services\Avisos;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Dashboard docente según el diseño de Figma (DashboardDocente): banner de
 * bienvenida, estadísticas, convocatorias recomendadas, próximos
 * vencimientos, propuestas recientes y actividad. Todo con datos reales.
 */
class Dashboard extends BaseDashboard
{
    protected string $view = 'filament.docente.dashboard';

    protected static ?string $title = 'Dashboard';

    // El título ya aparece en el header del panel.
    protected ?string $heading = '';

    /** Estados de propuesta que siguen en preparación. */
    private const PROPUESTA_EN_PROCESO = ['BORRADOR', 'GENERADA', 'EDITANDO', 'LISTA'];

    public function guardar(int $convocatoriaId): void
    {
        $this->seguimiento($convocatoriaId)?->save();

        Notification::make()
            ->title('Convocatoria guardada en Mis convocatorias')
            ->success()
            ->send();
    }

    public function alternarFavorita(int $convocatoriaId): void
    {
        $seguimiento = $this->seguimiento($convocatoriaId);

        if (! $seguimiento) {
            return;
        }

        $seguimiento->es_favorita = ! $seguimiento->es_favorita;
        $seguimiento->save();
    }

    /**
     * Seguimiento del usuario para una convocatoria publicada y vigente
     * (se crea como GUARDADA si no existía). Null si no está disponible.
     */
    private function seguimiento(int $convocatoriaId): ?UsuarioConvocatoria
    {
        $disponible = $this->disponibles()->whereKey($convocatoriaId)->exists();

        if (! $disponible) {
            Notification::make()
                ->title('Convocatoria no disponible')
                ->warning()
                ->send();

            return null;
        }

        $seguimiento = UsuarioConvocatoria::query()->firstOrNew([
            'user_id' => auth()->id(),
            'convocatoria_id' => $convocatoriaId,
        ]);

        if (! $seguimiento->exists) {
            $seguimiento->es_favorita = false;
            $seguimiento->prioridad = 'MEDIA';
            $seguimiento->estado_seguimiento = 'GUARDADA';
        }

        return $seguimiento;
    }

    private function disponibles(): Builder
    {
        return Convocatoria::query()
            ->whereIn('estado', ['PUBLICADA', 'ACTIVA'])
            ->noVencidas();
    }

    protected function getViewData(): array
    {
        /** @var User $usuario */
        $usuario = auth()->user();
        $hoy = Carbon::today();

        Avisos::generarAvisosDeCierre($usuario);

        $seguimientos = UsuarioConvocatoria::query()
            ->where('user_id', $usuario->id)
            ->get(['convocatoria_id', 'es_favorita']);

        $guardadas = $seguimientos->pluck('convocatoria_id')->all();
        $favoritas = $seguimientos->where('es_favorita', true)->pluck('convocatoria_id')->all();

        $propuestas = Propuesta::query()
            ->where('user_id', $usuario->id);

        return [
            'nombre' => trim($usuario->name . ' ' . ($usuario->apellidos ?? '')),
            'departamento' => $usuario->departamento?->nombre,
            'fecha' => $hoy->copy()->locale('es')->isoFormat('D [de] MMMM [de] YYYY'),

            'estadisticas' => [
                [
                    'etiqueta' => 'Convocatorias activas',
                    'valor' => $this->disponibles()->count(),
                    'sub' => '+' . $this->disponibles()->where('created_at', '>=', now()->subWeek())->count() . ' esta semana',
                    'icono' => 'libro',
                    'color' => 'azul',
                    'url' => Pages\BuscarConvocatorias::getUrl(),
                ],
                [
                    'etiqueta' => 'Mis convocatorias',
                    'valor' => count($guardadas),
                    'sub' => $this->disponibles()
                        ->whereIn('id', $guardadas)
                        ->whereNotNull('fecha_cierre')
                        ->whereDate('fecha_cierre', '<=', $hoy->copy()->addDays(14))
                        ->count() . ' por vencer pronto',
                    'icono' => 'marcador',
                    'color' => 'morado',
                    'url' => Pages\MisConvocatorias::getUrl(),
                ],
                [
                    'etiqueta' => 'Propuestas en proceso',
                    'valor' => (clone $propuestas)->whereIn('estado', self::PROPUESTA_EN_PROCESO)->count(),
                    'sub' => (clone $propuestas)->where('estado', 'LISTA')->count() . ' lista(s) para enviar',
                    'icono' => 'documento',
                    'color' => 'verde',
                    'url' => Pages\Propuestas::getUrl(),
                ],
                [
                    'etiqueta' => 'Próximos cierres',
                    'valor' => $this->disponibles()
                        ->whereNotNull('fecha_cierre')
                        ->whereDate('fecha_cierre', '<=', $hoy->copy()->addDays(30))
                        ->count(),
                    'sub' => 'En los próximos 30 días',
                    'icono' => 'reloj',
                    'color' => 'ambar',
                    'url' => Pages\Calendario::getUrl(),
                ],
            ],

            'recomendadas' => $this->disponibles()
                ->with(['organismo', 'categoria'])
                ->orderByRaw('fecha_cierre IS NULL, fecha_cierre ASC')
                ->limit(4)
                ->get()
                ->map(fn (Convocatoria $c): array => $this->tarjeta($c, $hoy) + [
                    'guardada' => in_array($c->id, $guardadas, true),
                    'favorita' => in_array($c->id, $favoritas, true),
                ])
                ->all(),

            'vencimientos' => $this->vencimientos($guardadas, $hoy),

            'propuestasRecientes' => (clone $propuestas)
                ->with('convocatoria:id,titulo')
                ->withCount(['objetivos', 'itemsPresupuesto', 'actividadesCronograma', 'entregables'])
                ->latest('updated_at')
                ->limit(3)
                ->get()
                ->map(fn (Propuesta $p): array => [
                    'id' => $p->id,
                    'titulo' => $p->titulo,
                    'convocatoria' => $p->convocatoria?->titulo,
                    'estado' => $p->estado,
                    'avance' => $this->avance($p),
                ])
                ->all(),

            'actividad' => [
                ['etiqueta' => 'Guardadas', 'valor' => count($guardadas), 'color' => '#1A4B8C'],
                ['etiqueta' => 'Favoritas', 'valor' => count($favoritas), 'color' => '#7C3AED'],
                ['etiqueta' => 'Propuestas', 'valor' => (clone $propuestas)->count(), 'color' => '#059669'],
            ],
        ];
    }

    /**
     * Cierres próximos de las convocatorias guardadas por el usuario; si no
     * tiene guardadas, los de todas las disponibles.
     */
    private function vencimientos(array $guardadas, Carbon $hoy): array
    {
        $consulta = $this->disponibles()->whereNotNull('fecha_cierre');

        if ($guardadas !== []) {
            $consulta->whereIn('id', $guardadas);
        }

        return $consulta
            ->orderBy('fecha_cierre')
            ->limit(4)
            ->get()
            ->map(function (Convocatoria $c) use ($hoy): array {
                $dias = (int) $hoy->diffInDays($c->fecha_cierre);

                return [
                    'id' => $c->id,
                    'titulo' => $c->titulo,
                    'dias' => $dias,
                    'cierre' => $c->fecha_cierre->copy()->locale('es')->isoFormat('D MMM YYYY'),
                    'prioridad' => $dias <= 14 ? 'alta' : ($dias <= 30 ? 'media' : 'baja'),
                ];
            })
            ->all();
    }

    private function tarjeta(Convocatoria $c, Carbon $hoy): array
    {
        $dias = $c->fecha_cierre ? (int) $hoy->diffInDays($c->fecha_cierre) : null;

        return [
            'id' => $c->id,
            'titulo' => $c->titulo,
            'organismo' => $c->organismo?->nombre ?? 'Organismo no especificado',
            'categoria' => $c->categoria?->nombre,
            'monto' => $c->monto_maximo !== null
                ? '$' . number_format((float) $c->monto_maximo, 0, '.', ',') . ' ' . ($c->moneda ?: 'MXN')
                : 'No especificado',
            'cierre' => $c->fecha_cierre?->copy()->locale('es')->isoFormat('D MMM'),
            'dias' => $dias,
            'porCerrar' => $dias !== null && $dias <= 7,
        ];
    }

    /**
     * Avance de una propuesta: secciones con contenido de las cinco
     * (redacción, objetivos, presupuesto, cronograma y entregables).
     */
    private function avance(Propuesta $p): int
    {
        $secciones = [
            filled($p->resumen) && filled($p->justificacion),
            $p->objetivos_count > 0,
            $p->items_presupuesto_count > 0,
            $p->actividades_cronograma_count > 0,
            $p->entregables_count > 0,
        ];

        return (int) round(100 * count(array_filter($secciones)) / count($secciones));
    }
}
