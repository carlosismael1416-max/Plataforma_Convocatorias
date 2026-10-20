<?php

namespace App\Filament\Directivo\Pages;

use App\Filament\Docente\Pages\BuscarConvocatorias as PaginaDocente;

/**
 * El mismo buscador del docente (mismos filtros, tarjetas y acciones), en el
 * panel directivo. El historial de cerradas es su pestaña «Historial».
 */
class BuscarConvocatorias extends PaginaDocente
{
    protected static ?int $navigationSort = 4;
}
