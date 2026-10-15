<?php

namespace App\Filament\Resources\Convocatorias\Pages;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConvocatorias extends ListRecords
{
    protected static string $resource =
        ConvocatoriaResource::class;

    protected string $view =
        'filament.admin.convocatorias.list-convocatorias';

    public function getTitle(): string
    {
        return 'Gestión de Convocatorias';
    }

    public function getSubheading(): ?string
    {
        return 'Supervisa las convocatorias detectadas por el motor y las registradas manualmente antes de su publicación en la plataforma.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(
                    'Nueva convocatoria'
                ),
        ];
    }

    protected function getViewData(): array
    {
        $total =
            Convocatoria::query()
                ->count();

        $pendientes =
            Convocatoria::query()
                ->where(
                    'estado',
                    'PENDIENTE_REVISION'
                )
                ->count();

        // Mismo criterio que el docente: publicadas y vigentes.
        $publicadasActivas =
            Convocatoria::query()
                ->visibles()
                ->count();

        $cerradasArchivadas =
            Convocatoria::query()
                ->where(fn ($q) => $q->historial()->orWhere('estado', 'ARCHIVADA'))
                ->count();

        $scraping =
            Convocatoria::query()
                ->where(
                    'origen',
                    'SCRAPING'
                )
                ->count();

        $manuales =
            Convocatoria::query()
                ->where(
                    'origen',
                    'MANUAL'
                )
                ->count();

        return [
            'totalConvocatorias' =>
                $total,

            'pendientesRevision' =>
                $pendientes,

            'publicadasActivas' =>
                $publicadasActivas,

            'cerradasArchivadas' =>
                $cerradasArchivadas,

            'totalScraping' =>
                $scraping,

            'totalManuales' =>
                $manuales,
        ];
    }
}
