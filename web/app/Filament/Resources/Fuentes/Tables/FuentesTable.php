<?php

namespace App\Filament\Resources\Fuentes\Tables;

use App\Models\Fuente;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FuentesTable
{
    public static function configure(
        Table $table
    ): Table {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Fuente')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make(
                    'url_base'
                )
                    ->label(
                        'Dirección web'
                    )
                    ->limit(45)
                    ->copyable()
                    ->placeholder('—'),

                TextColumn::make(
                    'tipo_fuente'
                )
                    ->label('Tipo')
                    ->badge()
                    ->sortable(),

                IconColumn::make(
                    'requiere_javascript'
                )
                    ->label('JavaScript')
                    ->boolean(),

                IconColumn::make('activa')
                    ->label('Activa')
                    ->boolean()
                    ->sortable(),

                TextColumn::make(
                    'frecuencia_scraping'
                )
                    ->label('Frecuencia')
                    ->formatStateUsing(
                        fn ($state): string =>
                            $state !== null
                                ? number_format(
                                    (int) $state
                                ).' min'
                                : 'Sin definir'
                    )
                    ->sortable(),

                TextColumn::make(
                    'ultima_ejecucion'
                )
                    ->label(
                        'Última consulta'
                    )
                    ->dateTime(
                        'd/m/Y H:i'
                    )
                    ->placeholder(
                        'Sin ejecutar'
                    )
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
                ViewAction::make()
                    ->label('Ver'),

                EditAction::make()
                    ->label('Editar'),
            ])

            ->defaultSort(
                'nombre'
            );
    }
}
