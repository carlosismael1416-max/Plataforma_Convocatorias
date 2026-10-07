<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvocatoriaRevision extends Model
{
    protected $table = 'convocatoria_revisions';

    protected $fillable = [
        'convocatoria_id',
        'tipo_revision',
        'fecha_cierre_anterior',
        'fecha_cierre_nueva',
        'hora_cierre',
        'zona_horaria',
        'fuente_tipo',
        'fuente_url',
        'observaciones',
        'requiere_verificacion',
        'fecha_revision',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cierre_anterior' => 'date',
            'fecha_cierre_nueva' => 'date',
            'requiere_verificacion' => 'boolean',
            'fecha_revision' => 'datetime',
        ];
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(
            Convocatoria::class,
            'convocatoria_id'
        );
    }
}
