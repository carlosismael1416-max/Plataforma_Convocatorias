<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Propuesta extends Model
{
    protected $table = 'propuestas';

    protected $fillable = [
        'user_id',
        'convocatoria_id',
        'propuesta_base_id',
        'titulo',
        'resumen',
        'justificacion',
        'metodologia',
        'impacto_esperado',
        'estado',
        'version',
        'fecha_envio',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'fecha_envio' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }

    public function propuestaBase(): BelongsTo
    {
        return $this->belongsTo(PropuestaBase::class);
    }

    public function objetivos(): HasMany
    {
        return $this->hasMany(PropuestaObjetivo::class)
            ->orderBy('orden');
    }

    public function requisitos(): HasMany
    {
        return $this->hasMany(PropuestaRequisito::class);
    }

    public function itemsPresupuesto(): HasMany
    {
        return $this->hasMany(PresupuestoItem::class);
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function actividadesCronograma(): HasMany
    {
        return $this->hasMany(CronogramaActividad::class)
            ->orderBy('orden');
    }

    public function entregables(): HasMany
    {
        return $this->hasMany(PropuestaEntregable::class);
    }
}
