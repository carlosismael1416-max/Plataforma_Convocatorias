<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvocatoriaApoyo extends Model
{
    protected $table = 'convocatoria_apoyos';

    protected $fillable = [
        'convocatoria_id',
        'clave',
        'concepto',
        'componente',
        'monto_maximo',
        'moneda',
        'unidad',
        'incluido_en_clave',
        'condiciones',
        'fuentes_documentales',
        'estado_documental',
    ];

    protected function casts(): array
    {
        return [
            'monto_maximo' => 'decimal:2',
            'condiciones' => 'array',
            'fuentes_documentales' => 'array',
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
