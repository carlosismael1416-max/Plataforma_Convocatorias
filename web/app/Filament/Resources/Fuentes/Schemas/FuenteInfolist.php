<?php

namespace App\Filament\Resources\Fuentes\Schemas;

use App\Models\Fuente;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class FuenteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Fuente')
                    ->icon(Heroicon::OutlinedGlobeAlt)
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nombre')
                            ->label('Nombre de la fuente')
                            ->weight('bold')
                            ->size('lg')
                            ->columnSpanFull(),
                        TextEntry::make('url_base')
                            ->label('Dirección web')
                            ->url(fn (Fuente $record): string => $record->url_base, shouldOpenInNewTab: true)
                            ->color('primary')
                            ->copyable()
                            ->columnSpanFull(),
                        TextEntry::make('tipo_fuente')
                            ->label('Tipo de fuente')
                            ->badge(),
                        TextEntry::make('frecuencia_scraping')
                            ->label('Frecuencia de consulta')
                            ->formatStateUsing(fn ($state): string => match (true) {
                                ! $state => '—',
                                (int) $state % 1440 === 0 => ((int) $state / 1440) . ' día(s)',
                                (int) $state % 60 === 0 => ((int) $state / 60) . ' hora(s)',
                                default => $state . ' minutos',
                            }),
                    ]),

                Section::make('Estado')
                    ->icon(Heroicon::OutlinedSignal)
                    ->columnSpan(1)
                    ->schema([
                        IconEntry::make('activa')
                            ->label('Fuente activa')
                            ->boolean(),
                        IconEntry::make('requiere_javascript')
                            ->label('Requiere JavaScript')
                            ->boolean()
                            ->trueColor('warning')
                            ->falseColor('success')
                            ->helperText(fn (Fuente $record): ?string => $record->requiere_javascript
                                ? 'El ciclo automático la omite (solo HTTP ligero).'
                                : null),
                        TextEntry::make('ultima_ejecucion')
                            ->label('Última consulta')
                            ->since()
                            ->dateTimeTooltip('d/m/Y H:i')
                            ->placeholder('Nunca'),
                    ]),

                Section::make('Registro')
                    ->icon(Heroicon::OutlinedClock)
                    ->columnSpanFull()
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')->label('Registrada')->dateTime('d/m/Y H:i')->placeholder('—'),
                        TextEntry::make('updated_at')->label('Última actualización')->dateTime('d/m/Y H:i')->placeholder('—'),
                    ]),
            ]);
    }
}
