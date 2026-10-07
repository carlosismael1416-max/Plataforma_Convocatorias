<?php

namespace App\Filament\Resources\Convocatorias\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ConvocatoriaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('titulo')
                    ->label('Título de la convocatoria')
                    ->columnSpanFull(),

                TextEntry::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'BORRADOR' => 'Borrador',
                            'PENDIENTE_REVISION' => 'Pendiente de revisión',
                            'REQUIERE_CORRECCIONES' => 'Requiere correcciones',
                            'PUBLICADA' => 'Publicada',
                            'ACTIVA' => 'Activa',
                            'CERRADA' => 'Cerrada',
                            'DESCARTADA' => 'Descartada',
                            'ARCHIVADA' => 'Archivada',
                            default => $state,
                        }
                    ),

                TextEntry::make('origen')
                    ->label('Origen'),

                TextEntry::make('subidoPor.name')
                    ->label('Usuario que la subió')
                    ->placeholder('Sin usuario asignado'),

                TextEntry::make('categoria.nombre')
                    ->label('Categoría')
                    ->placeholder('Sin categoría'),

                TextEntry::make('organismo.nombre')
                    ->label('Organismo')
                    ->placeholder('Sin organismo'),

                TextEntry::make('fuente.nombre')
                    ->label('Fuente web')
                    ->placeholder('Sin fuente'),

                TextEntry::make('descripcion')
                    ->label('Descripción')
                    ->placeholder('Sin descripción')
                    ->columnSpanFull(),

                TextEntry::make('objetivo')
                    ->label('Objetivo')
                    ->placeholder('Sin objetivo')
                    ->columnSpanFull(),

                TextEntry::make('fecha_publicacion')
                    ->label('Fecha de publicación')
                    ->date('d/m/Y')
                    ->placeholder('Sin fecha'),

                TextEntry::make('fecha_inicio')
                    ->label('Fecha de inicio')
                    ->date('d/m/Y')
                    ->placeholder('Sin fecha'),

                TextEntry::make('fecha_cierre')
                    ->label('Fecha de cierre')
                    ->date('d/m/Y')
                    ->placeholder('Sin fecha'),

                TextEntry::make('moneda')
                    ->label('Moneda'),

                TextEntry::make('monto_minimo')
                    ->label('Monto mínimo')
                    ->placeholder('No especificado'),

                TextEntry::make('monto_maximo')
                    ->label('Monto máximo')
                    ->placeholder('No especificado'),

                TextEntry::make('modalidad')
                    ->label('Modalidad')
                    ->placeholder('No especificada'),

                TextEntry::make('ubicacion')
                    ->label('Ubicación')
                    ->placeholder('No especificada'),

                TextEntry::make('url_original')
                    ->label('Enlace original')
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab()
                    ->placeholder('Sin enlace')
                    ->columnSpanFull(),
            ]);
    }
}
