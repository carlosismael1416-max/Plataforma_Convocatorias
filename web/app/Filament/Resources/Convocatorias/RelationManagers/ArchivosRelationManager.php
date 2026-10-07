<?php

namespace App\Filament\Resources\Convocatorias\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ArchivosRelationManager extends RelationManager
{
    protected static string $relationship = 'archivos';

    protected static ?string $title = 'Documentos de la convocatoria';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                TextColumn::make('nombre')
                    ->label('Documento')
                    ->searchable()
                    ->wrap()
                    ->url(
                        fn ($record): ?string =>
                            $record->url_archivo
                    )
                    ->openUrlInNewTab(),

                TextColumn::make('tipo_archivo')
                    ->label('Tipo')
                    ->badge(),

                TextColumn::make('mime_type')
                    ->label('Formato'),

                TextColumn::make('url_archivo')
                    ->label('Enlace')
                    ->limit(50)
                    ->copyable(),
            ]);
    }
}
