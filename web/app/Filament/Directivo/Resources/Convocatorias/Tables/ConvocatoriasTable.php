<?php

namespace App\Filament\Directivo\Resources\Convocatorias\Tables;

use App\Filament\Directivo\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConvocatoriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Convocatoria')
                    ->searchable()
                    ->sortable()
                    ->limit(55),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->placeholder('Sin categoría'),

                TextColumn::make('fecha_cierre')
                    ->label('Fecha de cierre')
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder('Sin fecha'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'PENDIENTE_REVISION' =>
                                'Pendiente de revisión',
                            'REQUIERE_CORRECCIONES' =>
                                'Requiere correcciones',
                            'PUBLICADA' => 'Publicada',
                            'BORRADOR' => 'Borrador',
                            'ACTIVA' => 'Activa',
                            'CERRADA' => 'Cerrada',
                            default => $state,
                        }
                    )
                    ->color(
                        fn (string $state): string => match ($state) {
                            'PUBLICADA', 'ACTIVA' => 'success',
                            'REQUIERE_CORRECCIONES' => 'warning',
                            'PENDIENTE_REVISION' => 'info',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('created_at')
                    ->label('Fecha de registro')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'PENDIENTE_REVISION' =>
                            'Pendiente de revisión',
                        'REQUIERE_CORRECCIONES' =>
                            'Requiere correcciones',
                        'PUBLICADA' => 'Publicada',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->visible(
                        fn (Convocatoria $record): bool =>
                            ConvocatoriaResource::canEdit($record)
                    ),
            ]);
    }
}
