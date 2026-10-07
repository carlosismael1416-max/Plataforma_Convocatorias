<?php

namespace App\Filament\Resources\Convocatorias\Pages;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditConvocatoria extends EditRecord
{
    protected static string $resource =
        ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),

            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading(
                    'Eliminar convocatoria'
                )
                ->modalDescription(
                    'Solo puede eliminarse si no tiene información relacionada.'
                )
                ->visible(
                    fn (): bool =>
                        ConvocatoriaResource::canDelete(
                            $this->getRecord()
                        )
                ),
        ];
    }

    protected function mutateFormDataBeforeSave(
        array $data
    ): array {
        /*
         * El estado no se modifica desde el formulario.
         * Se manejará mediante el flujo administrativo.
         */
        unset(
            $data['estado']
        );

        /*
         * El origen y el usuario que la registró
         * tampoco se modifican al editar.
         */
        unset(
            $data['origen'],
            $data['subido_por_user_id']
        );

        return $data;
    }
}
