<?php

namespace App\Filament\Resources\Convocatorias\Pages;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateConvocatoria extends CreateRecord
{
    protected static string $resource =
        ConvocatoriaResource::class;

    protected function mutateFormDataBeforeCreate(
        array $data
    ): array {
        $data['estado'] =
            'PENDIENTE_REVISION';

        $data['origen'] =
            'MANUAL';

        $data['subido_por_user_id'] =
            auth()->id();

        $data['fecha_extraccion'] =
            now();

        return $data;
    }
}
