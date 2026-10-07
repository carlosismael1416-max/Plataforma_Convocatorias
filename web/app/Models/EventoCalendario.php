<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoCalendario extends Model
{
    protected $table = 'eventos_calendario';

    protected $fillable = [
        'user_id',
        'convocatoria_id',
        'titulo',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'tipo_evento',
        'recordatorio',
        'minutos_recordatorio',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_fin' => 'datetime',
            'recordatorio' => 'boolean',
            'minutos_recordatorio' => 'integer',
            'estado' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
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
