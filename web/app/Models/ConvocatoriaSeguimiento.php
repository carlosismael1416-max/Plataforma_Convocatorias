<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lo último que el extractor vio en la página oficial de una convocatoria
 * publicada (lo escribe extractor/app/seguimiento_convocatorias.py).
 */
class ConvocatoriaSeguimiento extends Model
{
    protected $table = 'convocatoria_seguimientos';

    protected function casts(): array
    {
        return [
            'documentos' => 'array',
            'fecha_cierre_web' => 'date',
            'revisado_en' => 'datetime',
            'ultimo_cambio_en' => 'datetime',
        ];
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }
}
