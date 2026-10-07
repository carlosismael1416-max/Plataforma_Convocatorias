<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(
        Schema $schema
    ): Schema {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('name')
                    ->label('Nombre'),

                TextEntry::make(
                    'apellidos'
                )
                    ->label('Apellidos')
                    ->placeholder('—'),

                TextEntry::make('email')
                    ->label(
                        'Correo institucional'
                    )
                    ->copyable()
                    ->columnSpanFull(),

                TextEntry::make(
                    'role.nombre'
                )
                    ->label('Rol')
                    ->badge()
                    ->placeholder('Sin rol'),

                TextEntry::make(
                    'departamento.nombre'
                )
                    ->label('Departamento')
                    ->placeholder(
                        'Sin departamento'
                    ),

                IconEntry::make('estado')
                    ->label(
                        'Usuario activo'
                    )
                    ->boolean(),

                TextEntry::make(
                    'ultimo_acceso'
                )
                    ->label('Último acceso')
                    ->dateTime(
                        'd/m/Y H:i'
                    )
                    ->placeholder(
                        'Sin acceso registrado'
                    ),

                TextEntry::make(
                    'created_at'
                )
                    ->label(
                        'Fecha de registro'
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
