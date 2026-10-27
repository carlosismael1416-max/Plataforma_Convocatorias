<?php

namespace App\Filament\Docente\Resources\Convocatorias\Pages;

use App\Filament\Docente\Resources\Convocatorias\ConvocatoriaResource;
use App\Filament\Concerns\ImportaDesdeEnlace;
use App\Models\Convocatoria;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CreateConvocatoria extends CreateRecord
{
    use ImportaDesdeEnlace;

    protected static string $resource = ConvocatoriaResource::class;

    protected static ?string $title = 'Subir convocatoria';

    protected ?string $subheading = 'Pega el enlace y la llenamos por ti, o regístrala a mano.';

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Publicar convocatoria');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->label('Publicar y registrar otra');
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    protected function handleRecordCreation(array $data): Model
    {
        $usuario = Filament::auth()->user();

        if (
            ! $usuario instanceof User
            || ! $usuario->estado
            || ! $usuario->tieneRol('DOCENTE')
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
            'tipo',
            'dirigido_a',
        ];

        if (
            filled($data['fecha_cierre'] ?? null)
            && Carbon::parse($data['fecha_cierre'])->lt(today())
        ) {
            throw ValidationException::withMessages([
                'data.fecha_cierre' => 'La fecha de cierre ya pasó: no se puede publicar una convocatoria vencida.',
            ]);
        }

        // Si el enlace ya era una pendiente interna del motor, se publica esa
        // en vez de crear un duplicado.
        $convocatoria = $this->validarImportacion($data) ?? new Convocatoria();

        $convocatoria->fill(
            Arr::only($data, $camposPermitidos)
        );

        // Estos valores no dependen de los datos enviados
        // desde el navegador.
        $convocatoria->subido_por_user_id = $usuario->getKey();
        $convocatoria->origen = 'MANUAL';
        // Las convocatorias se publican al momento para que todos las vean;
        // lo que revisa el directivo son las propuestas para participar.
        $convocatoria->estado = 'PUBLICADA';

        $convocatoria->save();
        $this->adjuntarArchivoSubido($convocatoria);

        return $convocatoria;
    }
}
