<?php

namespace App\Filament\Resources\Organismos\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrganismosTable
{
    private const TIPOS = [
        'FEDERAL' => 'Federal', 'ESTATAL' => 'Estatal', 'MUNICIPAL' => 'Municipal', 'UNIVERSIDAD' => 'Universidad',
        'EMPRESA' => 'Empresa', 'INTERNACIONAL' => 'Internacional', 'OTRO' => 'Otro',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('convocatorias'))
            ->defaultSort('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Organismo')
                    ->description(fn ($record): ?string => $record->sitio_web)
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('tipo_organismo')
                    ->label('Tipo')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (?string $state): string => self::TIPOS[$state] ?? (string) $state),
                TextColumn::make('convocatorias_count')
                    ->label('Convocatorias')
                    ->alignCenter()
                    ->sortable(),
                IconColumn::make('estado')
                    ->label('Activo')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()->label('Editar')->iconButton()->tooltip('Editar'),
                DeleteAction::make()->label('Eliminar')->iconButton()->tooltip('Eliminar')->visible(fn ($record): bool => (int) $record->convocatorias_count === 0),
            ])
            ->emptyStateHeading('Sin organismos')
            ->emptyStateDescription('Agrega los organismos que convocan (SECIHTI, consejos estatales…).');
    }
}
