<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fuente extends Model
{
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
}
