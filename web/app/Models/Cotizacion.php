<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cotizacion extends Model
{
    protected $table = 'cotizaciones';

    protected $fillable = [
        'propuesta_id',
        'presupuesto_item_id',
        'proveedor',
        'concepto',
        'monto',
        'moneda',
        'archivo_url',
        'fecha_cotizacion',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'fecha_cotizacion' => 'date',
        ];
    }

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(Propuesta::class);
    }

    public function presupuestoItem(): BelongsTo
    {
        return $this->belongsTo(
            PresupuestoItem::class,
            'presupuesto_item_id'
        );
    }
}
