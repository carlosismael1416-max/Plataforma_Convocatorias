<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AjusteSistema extends Model
{
    protected $table = 'ajustes_sistema';

    protected $primaryKey = 'clave';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['clave', 'valor', 'actualizado_por'];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actualizado_por');
    }
}
