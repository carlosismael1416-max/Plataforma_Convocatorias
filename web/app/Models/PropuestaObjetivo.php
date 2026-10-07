<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropuestaObjetivo extends Model
{
    protected $table = 'propuesta_objetivos';

    protected $fillable = [
        'propuesta_id',
        'tipo',
        'descripcion',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
    }

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(Propuesta::class);
    }
}
