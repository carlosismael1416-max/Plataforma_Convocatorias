<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresupuestoItem extends Model
{
    protected $table = 'presupuesto_items';

    protected $fillable = [
        'propuesta_id',
        'concepto',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'categoria_gasto',
        'moneda',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(Propuesta::class);
    }

    public function cotizaciones(): HasMany
    {
        return $this->hasMany(
            Cotizacion::class,
            'presupuesto_item_id'
        );
    }
}
