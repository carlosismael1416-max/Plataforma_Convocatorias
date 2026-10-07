<?php

namespace App\Filament\Docente\Resources\Convocatorias\Pages;

use App\Filament\Docente\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\User;
use App\Services\ReenvioDocenteConvocatoria;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Auth\Access\AuthorizationException;

class ViewConvocatoria extends ViewRecord
{
    protected static string $resource = ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(
                    fn (): bool =>
                        ConvocatoriaResource::canEdit(
                            $this->getRecord()
                        )
                ),

            Action::make('reenviarRevision')
                ->label('Reenviar a revisión')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(
                    fn (): bool =>
                        ConvocatoriaResource::canEdit(
                            $this->getRecord()
                        )
                        && $this->getRecord()->estado
                            === 'REQUIERE_CORRECCIONES'
                )
                ->modalHeading(
                    'Reenviar convocatoria a revisión'
                )
                ->modalDescription(
                    'Describe las correcciones realizadas. '
                    . 'El administrador volverá a revisar '
                    . 'tu convocatoria.'
                )
                ->schema([
                    Textarea::make('observaciones')
                        ->label('Correcciones realizadas')
                        ->required()
                        ->minLength(5)
                        ->maxLength(5000)
                        ->rows(5),
                ])
                ->modalSubmitActionLabel('Confirmar reenvío')
                ->action(function (array $data): void {
                    $usuario = Filament::auth()->user();

                    if (! $usuario instanceof User) {
                        throw new AuthorizationException(
                            'Debes iniciar sesión.'
                        );
                    }

                    ReenvioDocenteConvocatoria::registrar(
                        $this->getRecord(),
                        $usuario,
                        $data['observaciones']
                    );

                    Notification::make()
                        ->title(
                            'Convocatoria reenviada a revisión'
                        )
                        ->success()
                        ->send();

                    $this->redirect(
                        ConvocatoriaResource::getUrl(
                            'view',
                            [
                                'record' =>
                                    $this->getRecord()->getKey(),
                            ]
                        )
                    );
                }),
        ];
    }
}
