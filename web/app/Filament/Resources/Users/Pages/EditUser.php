<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource =
        UserResource::class;

    protected function mutateFormDataBeforeSave(
        array $data
    ): array {
        if (
            (int) $this->getRecord()->id
            === (int) auth()->id()
        ) {
            $data['role_id'] =
                $this->getRecord()->role_id;

            $data['estado'] =
                (bool)
                $this->getRecord()->estado;
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Ver'),

            DeleteAction::make()
                ->label('Eliminar usuario')
                ->visible(
                    fn (): bool =>
                        UserResource::canDelete(
                            $this->getRecord()
                        )
                ),
        ];
    }
}
