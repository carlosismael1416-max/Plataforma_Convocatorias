<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConvocatoriaRevisionAdministrativa extends Model
{
    protected $table = 'convocatoria_revisiones_administrativas';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha_decision' => 'datetime',
        ];
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(
            Convocatoria::class,
            'convocatoria_id'
        );
    }

    public function revisor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'revisor_id'
        );
    }
}
