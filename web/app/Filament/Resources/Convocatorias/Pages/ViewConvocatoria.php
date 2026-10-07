<?php

namespace App\Filament\Resources\Convocatorias\Pages;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\User;
use App\Services\RevisionAdministrativaConvocatoria;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Auth\Access\AuthorizationException;

class ViewConvocatoria extends ViewRecord
{
    private const FLUJO_REVISION_HABILITADO = false;

    protected static string $resource = ConvocatoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('aprobar')
                ->label('Aprobar')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(
                    fn (): bool =>
                        self::FLUJO_REVISION_HABILITADO
                        && $this->puedeRevisar()
                        && (
                            $this->getRecord()->fecha_cierre === null
                            || ! $this->getRecord()->fecha_cierre->lt(today())
                        )
                )
                ->requiresConfirmation()
                ->modalHeading('Aprobar convocatoria')
                ->modalDescription(
                    'La convocatoria cambiará al estado PUBLICADA. '
                    . 'La decisión quedará registrada en el historial.'
                )
                ->modalSubmitActionLabel('Confirmar aprobación')
                ->action(function (): void {
                    $this->registrarDecision('APROBAR');
                }),

            Action::make('reenviarRevision')
                ->label('Reenviar a revisión')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->visible(
                    fn (): bool =>
                        self::FLUJO_REVISION_HABILITADO
                        && $this->puedeReenviar()
                )
                ->modalHeading('Reenviar convocatoria a revisión')
                ->modalDescription(
                    'Describe las correcciones realizadas. '
                    . 'El reenvío quedará registrado en el historial.'
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
                    $this->registrarDecision(
                        'REENVIAR_REVISION',
                        $data['observaciones']
                    );
                }),

            Action::make('solicitarCorrecciones')
                ->label('Solicitar correcciones')
                ->icon('heroicon-o-pencil-square')
                ->color('warning')
                ->visible(
                    fn (): bool =>
                        self::FLUJO_REVISION_HABILITADO
                        && $this->puedeRevisar()
                )
                ->modalHeading('Solicitar correcciones')
                ->modalDescription(
                    'Explica qué información debe corregirse. '
                    . 'La observación quedará registrada en el historial.'
                )
                ->schema([
                    Textarea::make('observaciones')
                        ->label('Observaciones')
                        ->placeholder(
                            'Describe los datos que necesitan corrección...'
                        )
                        ->required()
                        ->minLength(5)
                        ->maxLength(5000)
                        ->rows(5),
                ])
                ->modalSubmitActionLabel('Enviar solicitud')
                ->action(function (array $data): void {
                    $this->registrarDecision(
                        'SOLICITAR_CORRECCIONES',
                        $data['observaciones']
                    );
                }),
        ];
    }

    private function puedeRevisar(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('ADMINISTRADOR')
            && $usuario->tienePermiso('aprobar_convocatorias')
            && $this->getRecord()->estado === 'PENDIENTE_REVISION';
    }

    private function puedeReenviar(): bool
    {
        $usuario = auth()->user();

        return $usuario instanceof User
            && (bool) $usuario->estado
            && $usuario->tieneRol('ADMINISTRADOR')
            && $usuario->tienePermiso('aprobar_convocatorias')
            && $this->getRecord()->estado
                === 'REQUIERE_CORRECCIONES';
    }

    private function registrarDecision(
        string $accion,
        ?string $observaciones = null
    ): void {
        $usuario = auth()->user();

        if (! $usuario instanceof User) {
            throw new AuthorizationException(
                'Debes iniciar sesión para revisar convocatorias.'
            );
        }

        // El servicio vuelve a comprobar el permiso
        // y guarda la decisión en una transacción.
        RevisionAdministrativaConvocatoria::registrar(
            $this->getRecord(),
            $usuario,
            $accion,
            $observaciones
        );

        Notification::make()
            ->title(
                match ($accion) {
                    'APROBAR' => 'Convocatoria aprobada',
                    'SOLICITAR_CORRECCIONES' =>
                        'Solicitud de correcciones registrada',
                    'REENVIAR_REVISION' =>
                        'Convocatoria reenviada a revisión',
                    default => 'Decisión registrada',
                }
            )
            ->success()
            ->send();

        // Recargar la página para actualizar el estado
        // y ocultar las acciones que ya no corresponden.
        $this->redirect(
            ConvocatoriaResource::getUrl(
                'view',
                ['record' => $this->getRecord()->getKey()]
            )
        );
    }
}
