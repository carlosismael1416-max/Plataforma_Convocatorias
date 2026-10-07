<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvocatoriaModalidadEtapa extends Model
{
    protected $table = 'convocatoria_modalidad_etapas';

    protected $fillable = [
        'modalidad_id',
        'numero',
        'anio',
        'monto_maximo',
    ];

    protected function casts(): array
    {
        return [
            'numero' => 'integer',
            'anio' => 'integer',
            'monto_maximo' => 'decimal:2',
        ];
    }

    public function modalidad(): BelongsTo
    {
        return $this->belongsTo(
            ConvocatoriaModalidad::class,
            'modalidad_id'
        );
    }
}
