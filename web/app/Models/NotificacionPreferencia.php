<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificacionPreferencia extends Model
{
    protected $table =
        'notificacion_preferencias';

    protected $fillable = [
        'user_id',
        'convocatorias_revision',
        'fechas_proximas',
        'nuevas_propuestas',
        'avisos_sistema',
    ];

    protected function casts(): array
    {
        return [
            'convocatorias_revision' =>
                'boolean',

            'fechas_proximas' =>
                'boolean',

            'nuevas_propuestas' =>
                'boolean',

            'avisos_sistema' =>
                'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }
}
