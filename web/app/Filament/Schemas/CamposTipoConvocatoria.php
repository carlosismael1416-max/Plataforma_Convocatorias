<?php

namespace App\Filament\Schemas;

use App\Models\Convocatoria;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;

/**
 * Campos «Tipo» y «Dirigida a» de una convocatoria, compartidos por el
 * formulario de subida (docente/directivo) y el del administrador.
 */
class CamposTipoConvocatoria
{
    public static function campos(): array
    {
        return [
            Select::make('tipo')
                ->label('Tipo de convocatoria')
                ->options(collect(Convocatoria::TIPOS)->map(fn (array $t): string => "{$t[1]} {$t[0]}")->all())
                ->default('FINANCIAMIENTO')
                ->required()
                ->native(false),

            CheckboxList::make('dirigido_a')
                ->label('Dirigida a / puede ayudar a')
                ->options(Convocatoria::AUDIENCIAS)
                ->columns(3)
                ->helperText('Marca a quién le sirve: la escuela, docentes e investigadores o estudiantes.'),
        ];
    }
}
