<?php

namespace App\Filament\Resources\DescubrimientoWebs\Pages;

use App\Filament\Resources\DescubrimientoWebs\DescubrimientoWebResource;
use App\Models\DescubrimientoWeb;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

/**
 * Candidatos que el buscador encontró en internet (no son convocatorias:
 * esas ya pasaron extracción y validación). Pestaña de Motor de extracción.
 */
class ListDescubrimientoWebs extends ListRecords
{
    protected static string $resource = DescubrimientoWebResource::class;

    protected string $view = 'filament.admin.descubrimientos.list-descubrimientos';

    protected ?string $heading = '';

    /** Estados en que el motor todavía decide o reintenta. */
    public const EN_ESPERA = ['NUEVO', 'REVISION', 'APROBADO', 'EN_PROCESO', 'ERROR_TEMPORAL', 'REVISION_FINAL'];

    public function getTabs(): array
    {
        return [
            'todos' => Tab::make('Todos'),
            'en_espera' => Tab::make('En espera')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('estado', self::EN_ESPERA)),
            'convertidos' => Tab::make('Convertidos')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('convocatoria_id')),
            'descartados' => Tab::make('Descartados')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'DESCARTADO')),
            'con_error' => Tab::make('Con error')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('estado', ['ERROR', 'FUENTE_INACCESIBLE'])),
        ];
    }

    protected function getViewData(): array
    {
        $contar = fn (?\Closure $filtro = null): int => DescubrimientoWeb::query()->when($filtro, $filtro)->count();

        return [
            'tarjetas' => [
                ['todos', 'Descubiertos', $contar(), 'Encontrados por el buscador', 'globo', 'green'],
                ['en_espera', 'En espera', $contar(fn ($q) => $q->whereIn('estado', self::EN_ESPERA)), 'El motor los evalúa o reintenta solo', 'reloj', 'yellow'],
                ['convertidos', 'Convertidos', $contar(fn ($q) => $q->whereNotNull('convocatoria_id')), 'Ya son convocatorias', 'aprobado', 'blue'],
                ['descartados', 'Descartados', $contar(fn ($q) => $q->where('estado', 'DESCARTADO')), 'Notas, eventos u otros estados', 'rechazado', 'gray'],
                ['con_error', 'Con error', $contar(fn ($q) => $q->whereIn('estado', ['ERROR', 'FUENTE_INACCESIBLE'])), 'Sitio inaccesible o fallo', 'alerta', 'red'],
            ],
        ];
    }
}
