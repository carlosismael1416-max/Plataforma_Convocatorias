<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescubrimientoWeb extends Model
{
    protected $table = 'descubrimientos_web';

    protected $fillable = [
        'titulo',
        'url_original',
        'url_normalizada',
        'url_hash',
        'dominio',
        'metodo_descubrimiento',
        'pagina_origen',
        'tipo',
        'puntaje',
        'relevancia_tematica',
        'estado_temporal',
        'anios_detectados',
        'origen_anio',
        'razones',
        'categoria_objetivo',
        'decision_objetivo',
        'puntaje_objetivo',
        'razones_objetivo',
        'estado',
        'estado_fuente',
        'evidencia',
        'fuente_id',
        'convocatoria_id',
        'descubierto_en',
        'ultimo_visto_en',
        'procesado_en',
        'error_ultimo',
    ];

    protected function casts(): array
    {
        return [
            'puntaje' => 'integer',
            'puntaje_objetivo' => 'integer',

            'anios_detectados' => 'array',
            'razones' => 'array',
            'razones_objetivo' => 'array',
            'evidencia' => 'array',

            'descubierto_en' => 'datetime',
            'ultimo_visto_en' => 'datetime',
            'procesado_en' => 'datetime',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function fuente(): BelongsTo
    {
        return $this->belongsTo(
            Fuente::class,
            'fuente_id'
        );
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(
            Convocatoria::class,
            'convocatoria_id'
        );
    }
}
