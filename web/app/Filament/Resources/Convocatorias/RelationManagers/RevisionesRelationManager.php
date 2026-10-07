<?php

namespace App\Filament\Resources\Convocatorias\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RevisionesRelationManager extends RelationManager
{
    protected static string $relationship = 'revisiones';

    protected static ?string $title = 'Historial de fechas';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('tipo_revision')
            ->columns([
                TextColumn::make('fuente_tipo')
                    ->label('Fuente')
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'PDF' => 'PDF original',
                            'WEB' => 'Página web',
                            default => $state ?? 'Sin especificar',
                        }
                    )
                    ->badge(),

                TextColumn::make('fecha_cierre_anterior')
                    ->label('Cierre anterior')
                    ->date('d/m/Y')
                    ->placeholder('Sin fecha previa'),

                TextColumn::make('fecha_cierre_nueva')
                    ->label('Fecha de cierre')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('hora_cierre')
                    ->label('Hora'),

                TextColumn::make('zona_horaria')
                    ->label('Zona horaria'),

                TextColumn::make('requiere_verificacion')
                    ->label('Verificación pendiente')
                    ->formatStateUsing(
                        fn (bool $state): string =>
                            $state ? 'Sí' : 'No'
                    )
                    ->badge(),

                TextColumn::make('fuente_url')
                    ->label('Documento o página')
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            $state ? 'Abrir fuente' : 'Sin enlace'
                    )
                    ->url(
                        fn ($record): ?string =>
                            $record->fuente_url
                    )
                    ->openUrlInNewTab(),

                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->wrap(),

                TextColumn::make('fecha_revision')
                    ->label('Fecha de registro')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->defaultSort('fecha_cierre_nueva', 'asc');
    }
}
