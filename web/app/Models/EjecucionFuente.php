<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EjecucionFuente extends Model
{
    protected $table = 'ejecucion_fuentes';

    protected $fillable = [
        'ejecucion_scraping_id',
        'fuente_id',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'registros_encontrados',
        'registros_nuevos',
        'registros_actualizados',
        'duplicados',
        'errores',
        'http_status',
        'duracion_segundos',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'duracion_segundos' => 'decimal:2',
        ];
    }

    public function ejecucionScraping(): BelongsTo
    {
        return $this->belongsTo(EjecucionScraping::class);
    }

    public function fuente(): BelongsTo
    {
        return $this->belongsTo(Fuente::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(ProcesamientoDocumento::class);
    }

    public function erroresRegistrados(): HasMany
    {
        return $this->hasMany(BitacoraError::class);
    }
}
