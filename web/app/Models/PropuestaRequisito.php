<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropuestaRequisito extends Model
{
    protected $table = 'propuesta_requisitos';

    protected $fillable = [
        'propuesta_id',
        'convocatoria_requisito_id',
        'cumplido',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'cumplido' => 'boolean',
        ];
    }

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(Propuesta::class);
    }

    public function requisitoConvocatoria(): BelongsTo
    {
        return $this->belongsTo(
            ConvocatoriaRequisito::class,
            'convocatoria_requisito_id'
        );
    }
}
