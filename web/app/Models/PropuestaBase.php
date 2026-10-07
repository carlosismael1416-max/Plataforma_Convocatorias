<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropuestaBase extends Model
{
    protected $table = 'propuestas_base';

    protected $fillable = [
        'convocatoria_id',
        'contenido_generado',
        'modelo_ia',
        'prompt_utilizado',
        'version',
        'estado',
        'mensaje_error',
        'fecha_generacion',
    ];

    protected function casts(): array
    {
        return [
            'contenido_generado' => 'array',
            'version' => 'integer',
            'fecha_generacion' => 'datetime',
        ];
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }

    public function propuestas(): HasMany
    {
        return $this->hasMany(Propuesta::class);
    }
}
