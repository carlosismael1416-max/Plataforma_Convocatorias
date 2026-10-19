<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Docente\Pages\HistorialConvocatorias as PaginaDocente;

/** El mismo historial de convocatorias cerradas, en el panel admin. */
class HistorialConvocatorias extends PaginaDocente
{
    protected static ?int $navigationSort = 9;
}
