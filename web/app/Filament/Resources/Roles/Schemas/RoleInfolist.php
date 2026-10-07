<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RoleInfolist
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('nombre')
                    ->label(
                        'Nombre del rol'
                    )
                    ->badge(),

                IconEntry::make('estado')
                    ->label('Rol activo')
                    ->boolean(),

                TextEntry::make(
                    'descripcion'
                )
                    ->label('Descripción')
                    ->placeholder(
                        'Sin descripción'
                    )
                    ->columnSpanFull(),

                TextEntry::make(
                    'users_count'
                )
                    ->label(
                        'Usuarios asignados'
                    )
                    ->state(
                        fn ($record): int =>
                            $record
                                ->users()
                                ->count()
                    ),

                TextEntry::make(
                    'permisos_count'
                )
                    ->label(
                        'Permisos asignados'
                    )
                    ->state(
                        fn ($record): int =>
                            $record
                                ->permisos()
                                ->count()
                    ),

                TextEntry::make(
                    'permisos.descripcion'
                )
                    ->label('Permisos')
                    ->badge()
                    ->listWithLineBreaks()
                    ->placeholder(
                        'Sin permisos asignados'
                    )
                    ->columnSpanFull(),

                TextEntry::make(
                    'created_at'
                )
                    ->label(
                        'Fecha de creación'
                    )
                    ->dateTime(
                        'd/m/Y H:i'
                    )
                    ->placeholder('—'),

                TextEntry::make(
                    'updated_at'
                )
                    ->label(
                        'Última actualización'
                    )
                    ->dateTime(
                        'd/m/Y H:i'
                    )
                    ->placeholder('—'),
            ]);
    }
}
