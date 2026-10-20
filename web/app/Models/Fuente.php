<?php

namespace App\Models;

use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Model;

class Fuente extends Model
{
    use RegistraActividad;

    protected $table = 'fuentes';

    protected $fillable = [
        'nombre',
        'url_base',
        'tipo_fuente',
        'selector_config',
        'requiere_javascript',
        'activa',
        'frecuencia_scraping',
        'ultima_ejecucion',
    ];

    protected function casts(): array
    {
        return [
            'selector_config' => 'array',
            'requiere_javascript' => 'boolean',
            'activa' => 'boolean',
            'frecuencia_scraping' => 'integer',
            'ultima_ejecucion' => 'datetime',
        ];
    }

    public function convocatorias(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Convocatoria::class, 'fuente_id');
    }

    protected function accionActividad(): array
    {
        return ['FUENTE', 'la fuente'];
    }

    protected function nombreActividad(): string
    {
        return (string) $this->nombre;
    }
}
