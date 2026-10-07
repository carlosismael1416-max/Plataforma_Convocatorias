<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvocatoriaFinanciamiento extends Model
{
    protected $table = 'convocatoria_financiamientos';

    protected $fillable = [
        'convocatoria_id',
        'grupo_clave',
        'ejes_estrategicos',
        'etapa_1_anio',
        'etapa_1_monto_maximo',
        'etapa_2_anio',
        'etapa_2_monto_maximo',
        'monto_maximo_total',
        'moneda',
    ];

    protected function casts(): array
    {
        return [
            'ejes_estrategicos' => 'array',
            'etapa_1_anio' => 'integer',
            'etapa_2_anio' => 'integer',
            'etapa_1_monto_maximo' => 'decimal:2',
            'etapa_2_monto_maximo' => 'decimal:2',
            'monto_maximo_total' => 'decimal:2',
        ];
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }
}
