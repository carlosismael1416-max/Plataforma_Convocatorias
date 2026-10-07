<?php

namespace App\Filament\Resources\Convocatorias\Tables;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ConvocatoriasTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->columns([
                TextColumn::make('titulo')
                    ->label('Convocatoria')
                    ->searchable()
                    ->sortable()
                    ->limit(55)
                    ->weight('bold')
                    ->wrap(),

                TextColumn::make(
                    'organismo.nombre'
                )
                    ->label('Organismo')
                    ->placeholder(
                        'Sin organismo'
                    )
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make(
                    'categoria.nombre'
                )
                    ->label('Categoría')
                    ->placeholder(
                        'Sin categoría'
                    )
                    ->badge(),

                TextColumn::make(
                    'fuente.nombre'
                )
                    ->label('Fuente')
                    ->placeholder(
                        'Registro manual'
                    )
                    ->toggleable(),

                TextColumn::make(
                    'fecha_cierre'
                )
                    ->label(
                        'Fecha de cierre'
                    )
                    ->date('d/m/Y')
                    ->sortable()
                    ->placeholder(
                        'Sin fecha'
                    ),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            string $state
                        ): string =>
                            match ($state) {
                                'BORRADOR' =>
                                    'Borrador',

                                'PENDIENTE_REVISION' =>
                                    'Pendiente de revisión',

                                'REQUIERE_CORRECCIONES' =>
                                    'Requiere correcciones',

                                'PUBLICADA' =>
                                    'Publicada',

                                'ACTIVA' =>
                                    'Activa',

                                'CERRADA' =>
                                    'Cerrada',

                                'DESCARTADA' =>
                                    'Descartada',

                                'ARCHIVADA' =>
                                    'Archivada',

                                default =>
                                    $state,
                            }
                    )
                    ->color(
                        fn (
                            string $state
                        ): string =>
                            match ($state) {
                                'PUBLICADA',
                                'ACTIVA' =>
                                    'success',

                                'PENDIENTE_REVISION' =>
                                    'warning',

                                'REQUIERE_CORRECCIONES',
                                'DESCARTADA' =>
                                    'danger',

                                'CERRADA',
                                'ARCHIVADA' =>
                                    'gray',

                                'BORRADOR' =>
                                    'info',

                                default =>
                                    'gray',
                            }
                    ),

                TextColumn::make('origen')
                    ->label('Origen')
                    ->badge()
                    ->formatStateUsing(
                        fn (
                            ?string $state
                        ): string =>
                            match ($state) {
                                'SCRAPING' =>
                                    'Bot',

                                'MANUAL' =>
                                    'Manual',

                                default =>
                                    $state
                                    ?? 'Sin especificar',
                            }
                    ),

                TextColumn::make(
                    'fecha_extraccion'
                )
                    ->label('Detectada')
                    ->dateTime(
                        'd/m/Y H:i'
                    )
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(
                        isToggledHiddenByDefault:
                            true
                    ),
            ])

            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'BORRADOR' =>
                            'Borrador',

                        'PENDIENTE_REVISION' =>
                            'Pendiente de revisión',

                        'REQUIERE_CORRECCIONES' =>
                            'Requiere correcciones',

                        'PUBLICADA' =>
                            'Publicada',

                        'ACTIVA' =>
                            'Activa',

                        'CERRADA' =>
                            'Cerrada',

                        'DESCARTADA' =>
                            'Descartada',

                        'ARCHIVADA' =>
                            'Archivada',
                    ]),

                SelectFilter::make(
                    'categoria'
                )
                    ->label('Categoría')
                    ->relationship(
                        'categoria',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make(
                    'organismo'
                )
                    ->label('Organismo')
                    ->relationship(
                        'organismo',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make(
                    'fuente'
                )
                    ->label('Fuente')
                    ->relationship(
                        'fuente',
                        'nombre'
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make(
                    'origen'
                )
                    ->label('Origen')
                    ->options([
                        'SCRAPING' =>
                            'Bot / Scraping',

                        'MANUAL' =>
                            'Carga manual',
                    ]),
            ])

            ->defaultSort(
                'created_at',
                'desc'
            )

            ->recordUrl(
                fn (
                    Convocatoria $record
                ): string =>
                    ConvocatoriaResource::getUrl(
                        'view',
                        [
                            'record' =>
                                $record,
                        ]
                    )
            )

            ->recordActions([
                ViewAction::make()
                    ->label('Ver'),

                EditAction::make()
                    ->label('Editar'),

                DeleteAction::make()
                    ->label('Eliminar')
                    ->requiresConfirmation()
                    ->modalHeading(
                        'Eliminar convocatoria'
                    )
                    ->modalDescription(
                        'Esta acción solamente está disponible para convocatorias sin información relacionada.'
                    )
                    ->successNotificationTitle(
                        'Convocatoria eliminada'
                    )
                    ->visible(
                        fn (
                            Convocatoria $record
                        ): bool =>
                            ConvocatoriaResource::canDelete(
                                $record
                            )
                    ),
            ]);
    }
}
