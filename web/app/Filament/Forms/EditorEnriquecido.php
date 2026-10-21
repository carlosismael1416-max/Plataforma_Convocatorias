<?php

namespace App\Filament\Forms;

use App\Rules\LongitudTexto;
use App\Support\TextoEnriquecido;
use Filament\Forms\Components\Field;

/**
 * Campo de formulario con el editor tipo Word (fuente, tamaño, color,
 * listas…). Guarda HTML saneado; el límite cuenta solo el texto.
 */
class EditorEnriquecido extends Field
{
    protected string $view = 'filament.forms.editor-enriquecido';

    protected int $limite = 10000;

    protected string $marcador = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->dehydrateStateUsing(fn (?string $state): ?string => TextoEnriquecido::sanear($state) ?: null);
        $this->rule(fn (): LongitudTexto => new LongitudTexto($this->limite));
    }

    public function limite(int $caracteres): static
    {
        $this->limite = $caracteres;

        return $this;
    }

    public function marcador(string $texto): static
    {
        $this->marcador = $texto;

        return $this;
    }

    public function getLimite(): int
    {
        return $this->limite;
    }

    public function getMarcador(): string
    {
        return $this->marcador;
    }
}
