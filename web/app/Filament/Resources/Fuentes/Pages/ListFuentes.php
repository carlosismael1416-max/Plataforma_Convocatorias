<?php

namespace App\Filament\Resources\Fuentes\Pages;

use App\Filament\Resources\Fuentes\FuenteResource;
use App\Models\Fuente;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFuentes extends ListRecords
{
    protected static string $resource =
        FuenteResource::class;

    protected string $view =
        'filament.admin.fuentes.list-fuentes';

    public function getTitle(): string
    {
        return 'Fuentes Web';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nueva fuente')
                ->icon(
                    'heroicon-o-plus'
                ),
        ];
    }

    protected function getViewData(): array
    {
        return [
            'totalFuentes' =>
                Fuente::query()
                    ->count(),

            'fuentesActivas' =>
                Fuente::query()
                    ->where(
                        'activa',
                        true
                    )
                    ->count(),

            'fuentesInactivas' =>
                Fuente::query()
                    ->where(
                        'activa',
                        false
                    )
                    ->count(),

            'fuentesJavascript' =>
                Fuente::query()
                    ->where(
                        'requiere_javascript',
                        true
                    )
                    ->count(),
        ];
    }
}
