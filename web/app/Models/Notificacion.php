<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'user_id',
        'convocatoria_id',
        'propuesta_id',
        'titulo',
        'mensaje',
        'tipo',
        'leida',
        'fecha_lectura',
    ];

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'fecha_lectura' => 'datetime',
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
            Convocatoria::class
        );
    }

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(
            Propuesta::class
        );
    }
}
