<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evidencia extends Model
{
    protected $table = 'evidencias';

    protected $fillable = [
        'propuesta_id',
        'propuesta_requisito_id',
        'propuesta_entregable_id',
        'nombre',
        'descripcion',
        'ruta_archivo',
        'url_archivo',
    ];

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(
            Propuesta::class
        );
    }

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(
            PropuestaRequisito::class,
            'propuesta_requisito_id'
        );
    }

    public function entregable(): BelongsTo
    {
        return $this->belongsTo(
            PropuestaEntregable::class,
            'propuesta_entregable_id'
        );
    }
}
