<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\Role;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/** Detalle del rol: cabecera, permisos con «Ver todos» y usuarios asignados. */
class RoleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(['default' => 1, 'lg' => 3])
            ->components([
                View::make('filament.admin.detalle.cabecera-rol')
                    ->columnSpanFull(),

                Section::make('Permisos')
                    ->icon(Heroicon::OutlinedShieldCheck)
                    ->description('Lo que pueden hacer los usuarios con este rol.')
                    ->columnSpan(['default' => 1, 'lg' => 2])
                    ->schema([
                        View::make('filament.admin.detalle.lista-plegable')
                            ->viewData(fn (Role $record): array => [
                                'elementos' => $record->permisos()->orderBy('descripcion')->pluck('descripcion')->all(),
                                'visibles' => 8,
                                'vacio' => 'Sin permisos asignados',
                            ]),
                    ]),

                Section::make('Usuarios con este rol')
                    ->icon(Heroicon::OutlinedUsers)
                    ->columnSpan(1)
                    ->schema([
                        View::make('filament.admin.detalle.lista-plegable')
                            ->viewData(fn (Role $record): array => [
                                'elementos' => $record->users()->orderBy('name')->get(['name', 'apellidos'])
                                    ->map(fn ($u) => trim($u->name . ' ' . ($u->apellidos ?? '')))->all(),
                                'visibles' => 6,
                                'vacio' => 'Ningún usuario tiene este rol.',
                            ]),
                        TextEntry::make('updated_at')->label('Última actualización')->since()->dateTimeTooltip('d/m/Y H:i'),
                    ]),
            ]);
    }
}
