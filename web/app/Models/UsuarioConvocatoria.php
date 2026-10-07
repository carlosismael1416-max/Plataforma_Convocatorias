<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioConvocatoria extends Model
{
    protected $table = 'usuario_convocatorias';

    public const CREATED_AT = 'fecha_agregado';
    public const UPDATED_AT = 'fecha_actualizacion';

    protected $fillable = [
        'user_id',
        'convocatoria_id',
        'es_favorita',
        'estado_seguimiento',
        'prioridad',
        'notas',
        'ultima_accion',
        'fecha_ultima_accion',
    ];

    protected function casts(): array
    {
        return [
            'es_favorita' => 'boolean',
            'fecha_ultima_accion' => 'datetime',
            'fecha_agregado' => 'datetime',
            'fecha_actualizacion' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(Convocatoria::class);
    }
}
