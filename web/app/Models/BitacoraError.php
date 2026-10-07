<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BitacoraError extends Model
{
    protected $table = 'bitacora_errores';

    protected $fillable = [
        'ejecucion_scraping_id',
        'ejecucion_fuente_id',
        'fuente_id',
        'tipo_error',
        'codigo_error',
        'mensaje',
        'detalle',
        'stack_trace',
        'url',
        'resuelto',
        'fecha_resolucion',
        'user_id_resolucion',
    ];

    protected function casts(): array
    {
        return [
            'resuelto' => 'boolean',
            'fecha_resolucion' => 'datetime',
        ];
    }

    public function ejecucionScraping(): BelongsTo
    {
        return $this->belongsTo(EjecucionScraping::class);
    }

    public function ejecucionFuente(): BelongsTo
    {
        return $this->belongsTo(EjecucionFuente::class);
    }

    public function fuente(): BelongsTo
    {
        return $this->belongsTo(Fuente::class);
    }

    public function usuarioResolucion(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id_resolucion'
        );
    }
}
