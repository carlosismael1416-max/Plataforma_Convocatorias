<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropuestaEntregable extends Model
{
    protected $table = 'propuesta_entregables';

    protected $fillable = [
        'propuesta_id',
        'cronograma_actividad_id',
        'nombre',
        'descripcion',
        'fecha_limite',
        'estado',
        'fecha_entrega',
    ];

    protected function casts(): array
    {
        return [
            'fecha_limite' => 'date',
            'fecha_entrega' => 'datetime',
        ];
    }

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(Propuesta::class);
    }

    public function actividad(): BelongsTo
    {
        return $this->belongsTo(
            CronogramaActividad::class,
            'cronograma_actividad_id'
        );
    }
}
