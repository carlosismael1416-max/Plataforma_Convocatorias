<?php

namespace App\Filament\Resources\Convocatorias\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RevisionesAdministrativasRelationManager extends RelationManager
{
    protected static string $relationship = 'revisionesAdministrativas';

    protected static ?string $title = 'Historial administrativo';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('accion')
            ->columns([
                TextColumn::make('fecha_decision')
                    ->label('Fecha de revisión')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('revisor.name')
                    ->label('Responsable')
                    ->placeholder('Usuario no disponible'),

                TextColumn::make('accion')
                    ->label('Decisión')
                    ->badge()
                    ->formatStateUsing(
                        fn (string $state): string => match ($state) {
                            'APROBAR' => 'Aprobada',
                            'SOLICITAR_CORRECCIONES' =>
                                'Correcciones solicitadas',
                            'REENVIAR_REVISION' =>
                                'Reenviada a revisión',
                            default => $state,
                        }
                    )
                    ->color(
                        fn (string $state): string => match ($state) {
                            'APROBAR' => 'success',
                            'SOLICITAR_CORRECCIONES' => 'warning',
                            'REENVIAR_REVISION' => 'info',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('estado_anterior')
                    ->label('Estado anterior')
                    ->badge(),

                TextColumn::make('estado_nuevo')
                    ->label('Nuevo estado')
                    ->badge(),

                TextColumn::make('observaciones')
                    ->label('Observaciones')
                    ->placeholder('Sin observaciones')
                    ->wrap(),
            ])
            ->defaultSort('fecha_decision', 'desc')
            ->emptyStateHeading(
                'Sin revisiones administrativas'
            )
            ->emptyStateDescription(
                'Las decisiones del administrador aparecerán aquí.'
            );
    }
}
