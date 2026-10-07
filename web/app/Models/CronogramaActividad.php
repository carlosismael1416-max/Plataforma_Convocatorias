<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CronogramaActividad extends Model
{
    protected $table = 'cronograma_actividades';

    protected $fillable = [
        'propuesta_id',
        'actividad',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'responsable',
        'estado',
        'porcentaje_avance',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'porcentaje_avance' => 'decimal:2',
            'orden' => 'integer',
        ];
    }

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(Propuesta::class);
    }

    public function entregables(): HasMany
    {
        return $this->hasMany(
            PropuestaEntregable::class,
            'cronograma_actividad_id'
        );
    }
}
