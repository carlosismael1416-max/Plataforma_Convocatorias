<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource =
        RoleResource::class;

    protected function mutateFormDataBeforeSave(
        array $data
    ): array {
        $record =
            $this->getRecord();

        if (
            RoleResource::esRolBase(
                $record
            )
        ) {
            $data['nombre'] =
                $record->nombre;

            $data['estado'] =
                true;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Ver'),

            DeleteAction::make()
                ->label('Eliminar rol')
                ->visible(
                    fn (): bool =>
                        RoleResource::canDelete(
                            $this->getRecord()
                        )
                ),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Rol actualizado correctamente';
    }
}
