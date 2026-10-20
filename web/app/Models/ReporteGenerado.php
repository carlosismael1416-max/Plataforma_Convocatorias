<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un reporte exportado desde Reportes (historial y «generar de nuevo»). */
class ReporteGenerado extends Model
{
    protected $table = 'reportes_generados';

    protected $fillable = ['user_id', 'tipo', 'formato', 'filtros', 'total'];

    protected function casts(): array
    {
        return ['filtros' => 'array', 'total' => 'integer'];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
