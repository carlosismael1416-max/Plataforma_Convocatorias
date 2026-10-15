<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;

/**
 * Páginas de trabajo compartidas por docente y directivo (Mis convocatorias,
 * calendario y todo el flujo de propuestas). El rol exigido depende del panel
 * en que se abre la página: /docente pide DOCENTE y /directivo pide DIRECTIVO.
 */
trait RolDelPanel
{
    protected static function rolDelPanel(): string
    {
        return Filament::getCurrentPanel()?->getId() === 'directivo'
            ? 'DIRECTIVO'
            : 'DOCENTE';
    }
}
