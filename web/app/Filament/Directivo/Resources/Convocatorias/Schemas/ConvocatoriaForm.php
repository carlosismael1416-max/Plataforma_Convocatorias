<?php

namespace App\Filament\Directivo\Resources\Convocatorias\Schemas;

use App\Filament\Docente\Resources\Convocatorias\Schemas\ConvocatoriaForm as FormularioDocente;
use Filament\Schemas\Schema;

/**
 * El directivo sube convocatorias con el mismo formulario que el docente
 * (tarjetas, consejos y aviso de revisión).
 */
class ConvocatoriaForm
{
    public static function configure(Schema $schema): Schema
    {
        return FormularioDocente::configure($schema);
    }
}
