<?php

namespace App\Filament\Resources\Fuentes\Pages;

use App\Filament\Admin\Pages\GestionarFuente;
use App\Filament\Resources\Fuentes\FuenteResource;
use Filament\Resources\Pages\ViewRecord;

/**
 * El detalle de una fuente es «Gestionar fuente» (estado, ejecuciones,
 * errores y convocatorias); esta ruta solo redirige ahí.
 */
class ViewFuente extends ViewRecord
{
    protected static string $resource = FuenteResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $this->redirect(GestionarFuente::getUrl(['record' => $this->getRecord()->id]));
    }
}
