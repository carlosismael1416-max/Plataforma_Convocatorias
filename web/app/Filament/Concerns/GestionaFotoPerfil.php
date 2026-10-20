<?php

namespace App\Filament\Concerns;

use App\Models\User;
use App\Services\FotoPerfil;
use Filament\Notifications\Notification;
use Livewire\WithFileUploads;
use Throwable;

/**
 * Subir y quitar la foto de perfil desde la página Perfil de cualquier panel.
 * La foto se guarda en cuanto se elige el archivo.
 */
trait GestionaFotoPerfil
{
    use WithFileUploads;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $fotoNueva = null;

    public function updatedFotoNueva(): void
    {
        $this->validate(
            ['fotoNueva' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072']],
            [
                'fotoNueva.image' => 'El archivo debe ser una imagen.',
                'fotoNueva.mimes' => 'Usa una imagen JPG, PNG o WebP.',
                'fotoNueva.max' => 'La imagen no debe pesar más de 3 MB.',
            ],
        );

        try {
            FotoPerfil::guardar($this->usuarioDeLaFoto(), $this->fotoNueva);
        } catch (Throwable) {
            $this->addError('fotoNueva', 'No se pudo procesar la imagen. Prueba con otra.');

            return;
        } finally {
            $this->fotoNueva = null;
        }

        Notification::make()->title('Foto de perfil actualizada')->success()->send();
        $this->redirect(static::getUrl(), navigate: false);
    }

    public function quitarFoto(): void
    {
        FotoPerfil::quitar($this->usuarioDeLaFoto());

        Notification::make()->title('Foto de perfil quitada')->success()->send();
        $this->redirect(static::getUrl(), navigate: false);
    }

    private function usuarioDeLaFoto(): User
    {
        return User::query()->whereKey(auth()->id())->firstOrFail();
    }
}
