<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcesamientoDocumento extends Model
{
    protected $table = 'procesamientos_documentos';

    protected $fillable = [
        'convocatoria_archivo_id',
        'ejecucion_fuente_id',
        'motor_extraccion',
        'requiere_ocr',
        'paginas',
        'estado',
        'texto_extraido',
        'datos_extraidos',
        'hash_documento',
        'mensaje_error',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected function casts(): array
    {
        return [
            'requiere_ocr' => 'boolean',
            'paginas' => 'integer',
            'datos_extraidos' => 'array',
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
        ];
    }

    public function archivo(): BelongsTo
    {
        return $this->belongsTo(
            ConvocatoriaArchivo::class,
            'convocatoria_archivo_id'
        );
    }

    public function ejecucionFuente(): BelongsTo
    {
        return $this->belongsTo(EjecucionFuente::class);
    }
}
