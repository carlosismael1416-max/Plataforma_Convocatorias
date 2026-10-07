<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConvocatoriaRequisito extends Model
{
    protected $table = 'convocatoria_requisitos';

    protected $fillable = [
        'convocatoria_id',
        'titulo',
        'descripcion',
        'obligatorio',
        'tipo_requisito',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'obligatorio' => 'boolean',
            'orden' => 'integer',
        ];
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }
public function propuestasRequisitos(): HasMany
{
    return $this->hasMany(
        PropuestaRequisito::class,
        'convocatoria_requisito_id'
    );
}
}
