<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropuestaRevision extends Model
{
    protected $table = 'propuesta_revisiones';

    protected $fillable = [
        'propuesta_id',
        'user_id',
        'accion',
        'estado_anterior',
        'estado_nuevo',
        'observaciones',
    ];

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(Propuesta::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
