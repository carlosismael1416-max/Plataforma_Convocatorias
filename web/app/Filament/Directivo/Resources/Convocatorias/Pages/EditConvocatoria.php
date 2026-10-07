<?php

namespace App\Filament\Directivo\Resources\Convocatorias\Pages;

use App\Filament\Directivo\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EditConvocatoria extends EditRecord
{
    protected static string $resource = ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function handleRecordUpdate(
        Model $record,
        array $data
    ): Model {
        $usuario = Filament::auth()->user();

        if (
            ! $usuario instanceof User
            || ! $usuario->estado
            || ! $usuario->tieneRol('DIRECTIVO')
        ) {
            throw new AuthorizationException(
                'No tienes permiso para editar convocatorias.'
            );
        }

        return DB::transaction(function () use (
            $record,
            $data,
            $usuario
        ): Model {
            // Volver a consultar el estado dentro de la transacción.
            $convocatoria = Convocatoria::query()
                ->lockForUpdate()
                ->findOrFail($record->getKey());

            if (
                (int) $convocatoria->subido_por_user_id
                !== (int) $usuario->getKey()
            ) {
                throw new AuthorizationException(
                    'Esta convocatoria no te pertenece.'
                );
            }

            if (! in_array(
                $convocatoria->estado,
                [
                    'PENDIENTE_REVISION',
                    'REQUIERE_CORRECCIONES',
                ],
                true
            )) {
                throw ValidationException::withMessages([
                    'estado' =>
                        'Esta convocatoria ya no se puede editar. '
                        . 'Actualiza la página.',
                ]);
            }

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

            $convocatoria->fill(
                Arr::only($data, $camposPermitidos)
            );

            $convocatoria->save();

            return $convocatoria;
        });
    }
}
