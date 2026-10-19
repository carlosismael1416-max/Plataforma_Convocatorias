<?php

namespace App\Filament\Directivo\Pages;

use App\Filament\Docente\Pages\HistorialConvocatorias as PaginaDocente;

/** El mismo historial de convocatorias cerradas, en el panel directivo. */
class HistorialConvocatorias extends PaginaDocente
{
    protected static ?int $navigationSort = 6;
}
