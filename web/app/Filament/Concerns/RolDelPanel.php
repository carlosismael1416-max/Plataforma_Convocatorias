<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;

/**
 * Páginas de trabajo compartidas por docente y directivo (Mis convocatorias,
 * calendario y todo el flujo de propuestas). El rol exigido depende del panel
 * en que se abre la página: /docente pide DOCENTE, /directivo pide DIRECTIVO
 * y /admin (solo el historial) pide ADMINISTRADOR.
 */
trait RolDelPanel
{
    protected static function rolDelPanel(): string
    {
        return match (Filament::getCurrentPanel()?->getId()) {
            'directivo' => 'DIRECTIVO',
            'admin' => 'ADMINISTRADOR',
            default => 'DOCENTE',
        };
    }
}
