<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

/**
 * Botón «Volver» en las páginas de detalle: regresa al listado de su
 * sección sin depender del botón «atrás» del navegador.
 */
trait BotonVolver
{
    protected function accionVolver(): Action
    {
        return Action::make('volver')
            ->label('Volver')
            ->icon(Heroicon::OutlinedArrowLeft)
            ->color('gray')
            ->outlined()
            ->url(static::getResource()::getUrl('index'));
    }
}
