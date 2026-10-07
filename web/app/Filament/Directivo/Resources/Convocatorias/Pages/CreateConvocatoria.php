<?php

namespace App\Filament\Directivo\Resources\Convocatorias\Pages;

use App\Filament\Directivo\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class CreateConvocatoria extends CreateRecord
{
    protected static string $resource = ConvocatoriaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $usuario = Filament::auth()->user();

        if (
            ! $usuario instanceof User
            || ! $usuario->estado
            || ! $usuario->tieneRol('DIRECTIVO')
        ) {
            throw new AuthorizationException(
                'No tienes permiso para subir convocatorias.'
            );
        }

        // Aceptar exclusivamente los campos del formulario docente.
        $camposPermitidos = [
            'titulo',
            'descripcion',
            'objetivo',
            'categoria_id',
            'organismo_id',
            'fecha_publicacion',
            'fecha_inicio',
            'fecha_cierre',
            'moneda',
            'monto_minimo',
            'monto_maximo',
            'modalidad',
            'ubicacion',
            'url_original',
        ];

        $convocatoria = new Convocatoria();

        $convocatoria->fill(
            Arr::only($data, $camposPermitidos)
        );

        // Estos valores no dependen de los datos enviados
        // desde el navegador.
        $convocatoria->subido_por_user_id = $usuario->getKey();
        $convocatoria->origen = 'MANUAL';
        $convocatoria->estado = 'PENDIENTE_REVISION';

        $convocatoria->save();

        return $convocatoria;
    }
}
