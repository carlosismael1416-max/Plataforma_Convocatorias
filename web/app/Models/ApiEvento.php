<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiEvento extends Model
{
    protected $table = 'api_eventos';

    protected $fillable = [
        'convocatoria_id',
        'ejecucion_scraping_id',
        'tipo_evento',
        'endpoint',
        'metodo_http',
        'payload',
        'codigo_respuesta',
        'respuesta',
        'estado',
        'intentos',
        'fecha_envio',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'respuesta' => 'array',
            'intentos' => 'integer',
            'fecha_envio' => 'datetime',
        ];
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }

    public function ejecucionScraping(): BelongsTo
    {
        return $this->belongsTo(EjecucionScraping::class);
    }
}
