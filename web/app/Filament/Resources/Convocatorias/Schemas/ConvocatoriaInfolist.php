<?php

namespace App\Filament\Resources\Convocatorias\Schemas;

use App\Models\Convocatoria;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** Detalle de convocatoria del admin: cabecera y secciones. */
class ConvocatoriaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                View::make('filament.admin.detalle.cabecera-convocatoria')
                    ->columnSpanFull(),

                Section::make('Descripción y objetivo')
                    ->icon(Heroicon::OutlinedDocumentText)
                    ->columnSpan(['default' => 1, 'lg' => 2])
                    ->schema([
                        TextEntry::make('descripcion')->label('Descripción')->placeholder('Sin descripción')->prose(),
                        TextEntry::make('objetivo')->label('Objetivo')->placeholder('Sin objetivo')->prose(),
                    ]),

                Section::make('Clasificación')
                    ->icon(Heroicon::OutlinedTag)
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('dirigido_a')
                            ->label('Dirigida a')
                            ->badge()
                            ->color('gray')
                            ->formatStateUsing(fn ($state): string => Convocatoria::AUDIENCIAS[$state] ?? (string) $state)
                            ->placeholder('Sin especificar'),
                        TextEntry::make('categoria.nombre')->label('Categoría')->placeholder('Sin categoría'),
                        TextEntry::make('fuente.nombre')->label('Fuente web')->placeholder('Registro manual'),
                        TextEntry::make('subidoPor.name')->label('Subida por')->placeholder('El motor'),
                    ]),

                Section::make('Fechas y apoyo económico')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->columns(['default' => 2, 'md' => 4])
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('fecha_publicacion')->label('Publicación')->date('d/m/Y')->placeholder('Sin fecha'),
                        TextEntry::make('fecha_inicio')->label('Inicio')->date('d/m/Y')->placeholder('Sin fecha'),
                        TextEntry::make('fecha_cierre')->label('Cierre')->date('d/m/Y')->placeholder('Sin fecha'),
                        TextEntry::make('fecha_extraccion')->label('Detectada')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('monto_minimo')->label('Monto mínimo')->money(fn ($record) => $record->moneda ?: 'MXN')->placeholder('No especificado'),
                        TextEntry::make('monto_maximo')->label('Monto máximo')->money(fn ($record) => $record->moneda ?: 'MXN')->placeholder('No especificado'),
                        TextEntry::make('modalidad')->label('Modalidad')->placeholder('No especificada'),
                        TextEntry::make('ubicacion')->label('Ubicación')->placeholder('No especificada'),
                        TextEntry::make('url_original')
                            ->label('Enlace oficial')
                            ->url(fn ($record) => $record->url_original, shouldOpenInNewTab: true)
                            ->color('primary')
                            ->copyable()
                            ->placeholder('Sin enlace')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
