<?php

namespace App\Filament\Resources\Fuentes\Tables;

use App\Models\Fuente;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FuentesTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->withCount('convocatorias'))
            ->columns([
                // Nombre con su dirección debajo; estado claro (si requiere
                // JavaScript el ciclo la omite). Antes eran 7 columnas.
                TextColumn::make('nombre')
                    ->label('Fuente')
                    ->description(fn (Fuente $record): string => $record->url_base)
                    ->weight('semibold')
                    ->searchable(['nombre', 'url_base'])
                    ->sortable()
                    ->wrap()
                    ->grow(),

                TextColumn::make('activa')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state, Fuente $record): string => ! $state
                        ? 'Inactiva'
                        : ($record->requiere_javascript ? 'Omitida' : 'Activa'))
                    ->color(fn ($state, Fuente $record): string => ! $state ? 'gray' : ($record->requiere_javascript ? 'warning' : 'success'))
                    ->description(fn (Fuente $record): ?string => $record->requiere_javascript
                        ? 'Requiere JavaScript: el ciclo no la consulta'
                        : null),

                TextColumn::make('convocatorias_count')
                    ->label('Convocatorias')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('frecuencia_scraping')
                    ->label('Frecuencia')
                    ->formatStateUsing(fn ($state): string => match (true) {
                        ! $state => '—',
                        (int) $state % 1440 === 0 => 'Cada ' . ((int) $state / 1440 === 1 ? 'día' : ((int) $state / 1440) . ' días'),
                        (int) $state % 60 === 0 => 'Cada ' . ((int) $state / 60) . ' h',
                        default => 'Cada ' . $state . ' min',
                    }),

                TextColumn::make('ultima_ejecucion')
                    ->label('Última consulta')
                    ->since()
                    ->dateTimeTooltip('d/m/Y H:i')
                    ->placeholder('Nunca')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make(
                    'activa'
                )
                    ->label('Estado')
                    ->options([
                        '1' => 'Activa',
                        '0' => 'Inactiva',
                    ]),

                SelectFilter::make(
                    'tipo_fuente'
                )
                    ->label(
                        'Tipo de fuente'
                    )
                    ->options([
                        'WEB' =>
                            'Página web',

                        'API' =>
                            'API',

                        'RSS' =>
                            'RSS',

                        'OTRO' =>
                            'Otro',
                    ]),
            ])

            ->recordUrl(
                fn (
                    Fuente $record
                ): string =>
                    route(
                        'filament.admin.pages.gestionar-fuente',
                        [
                            'record' =>
                                $record->id,
                        ]
                    )
            )

            ->recordActions([
                EditAction::make()->label('Editar')->iconButton()->tooltip('Editar'),
            ])

            ->defaultSort(
                'nombre'
            );
    }
}
