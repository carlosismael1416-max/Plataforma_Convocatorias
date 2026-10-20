<?php

namespace App\Filament\Concerns;

use Filament\Actions\Action;

/**
 * «Guardar borrador» en los pasos de la propuesta: guarda sin sacar al
 * usuario de la página y el aviso ofrece ir a la vista previa.
 */
trait AvisaBorradorGuardado
{
    protected function accionesBorrador(): array
    {
        return [
            Action::make('vistaPrevia')
                ->label('Ver vista previa')
                ->button()
                ->url(route('filament.' . filament()->getId() . '.pages.vista-previa-propuesta', [
                    'propuesta' => $this->propuestaId,
                ])),
        ];
    }
}
