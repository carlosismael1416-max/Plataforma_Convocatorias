<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Concerns\BotonVolver;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    use BotonVolver;

    protected static string $resource =
        UserResource::class;

    public function getTitle(): string
    {
        return 'Detalle del usuario';
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->accionVolver(),

            EditAction::make()
                ->label('Editar usuario'),
        ];
    }
}
