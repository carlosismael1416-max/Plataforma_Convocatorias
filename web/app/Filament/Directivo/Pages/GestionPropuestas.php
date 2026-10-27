<?php

namespace App\Filament\Directivo\Pages;

use App\Filament\Admin\Pages\GestionPropuestas as PaginaAdmin;

/** La misma «Gestión de propuestas» en el panel directivo. */
class GestionPropuestas extends PaginaAdmin
{
    protected static ?int $navigationSort = 9;
}
