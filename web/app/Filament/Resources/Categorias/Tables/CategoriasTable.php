<?php

namespace App\Filament\Resources\Categorias\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CategoriasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('convocatorias'))
            ->defaultSort('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Categoría')
                    ->description(fn ($record): ?string => $record->descripcion ? \Illuminate\Support\Str::limit($record->descripcion, 120) : null)
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('convocatorias_count')
                    ->label('Convocatorias')
                    ->alignCenter()
                    ->sortable(),
                IconColumn::make('estado')
                    ->label('Activa')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make()->label('Editar')->iconButton()->tooltip('Editar'),
                DeleteAction::make()->label('Eliminar')->iconButton()->tooltip('Eliminar')->visible(fn ($record): bool => (int) $record->convocatorias_count === 0),
            ])
            ->emptyStateHeading('Sin categorías');
    }
}
