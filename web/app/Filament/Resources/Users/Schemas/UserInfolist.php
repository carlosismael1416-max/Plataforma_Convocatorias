<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** Detalle del usuario: cabecera de perfil y secciones de cuenta y actividad. */
class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 2])
            ->components([
                View::make('filament.admin.detalle.cabecera-usuario')
                    ->columnSpanFull(),

                Section::make('Cuenta')
                    ->icon(Heroicon::OutlinedIdentification)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nombre'),
                        TextEntry::make('apellidos')->label('Apellidos')->placeholder('—'),
                        TextEntry::make('email')->label('Correo institucional')->copyable()->columnSpanFull(),
                        TextEntry::make('role.nombre')
                            ->label('Rol')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => ucfirst(mb_strtolower((string) $state)))
                            ->color(fn (?string $state): string => match ($state) {
                                'ADMINISTRADOR' => 'success',
                                'DIRECTIVO' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('departamento.nombre')->label('Departamento')->placeholder('Sin departamento'),
                    ]),

                Section::make('Actividad')
                    ->icon(Heroicon::OutlinedClock)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->formatStateUsing(fn ($state): string => $state ? 'Activo' : 'Inactivo')
                            ->color(fn ($state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('ultimo_acceso')
                            ->label('Último acceso')
                            ->since()
                            ->dateTimeTooltip('d/m/Y H:i')
                            ->placeholder('Nunca ha entrado'),
                        TextEntry::make('created_at')->label('Registrado')->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')->label('Última actualización')->since()->dateTimeTooltip('d/m/Y H:i'),
                    ]),
            ]);
    }
}
