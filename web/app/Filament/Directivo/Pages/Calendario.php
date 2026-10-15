<?php

namespace App\Filament\Directivo\Pages;

use App\Filament\Docente\Pages\Calendario as PaginaDocente;

/**
 * La misma página del docente, en el panel directivo: el directivo también
 * da seguimiento a convocatorias y prepara sus propias propuestas.
 */
class Calendario extends PaginaDocente
{
    protected static ?int $navigationSort = 7;
}
