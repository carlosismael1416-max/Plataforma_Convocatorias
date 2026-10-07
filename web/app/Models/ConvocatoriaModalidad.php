<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConvocatoriaModalidad extends Model
{
    protected $table = 'convocatoria_modalidades';

    protected $fillable = [
        'convocatoria_id',
        'clave',
        'nombre',
        'moneda',
        'monto_maximo_total',
        'estado_documental',
        'fuentes_documentales',
    ];

    protected function casts(): array
    {
        return [
            'monto_maximo_total' => 'decimal:2',
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

    public function etapas(): HasMany
    {
        return $this->hasMany(
            ConvocatoriaModalidadEtapa::class,
            'modalidad_id'
        )->orderBy('numero');
    }
}
