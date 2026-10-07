<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EjecucionScraping extends Model
{
    protected $table = 'ejecuciones_scraping';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'total_fuentes',
        'fuentes_exitosas',
        'fuentes_fallidas',
        'total_encontradas',
        'nuevas_convocatorias',
        'convocatorias_actualizadas',
        'duplicados_detectados',
        'total_errores',
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

    public function ejecucionesFuentes(): HasMany
    {
        return $this->hasMany(
            EjecucionFuente::class,
            'ejecucion_scraping_id'
        );
    }

    public function errores(): HasMany
    {
        return $this->hasMany(
            BitacoraError::class,
            'ejecucion_scraping_id'
        );
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(
            ApiEvento::class,
            'ejecucion_scraping_id'
        );
    }
}
