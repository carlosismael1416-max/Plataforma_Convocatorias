<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organismo extends Model
{
    protected $table = 'organismos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'sitio_web',
        'tipo_organismo',
        'pais',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }
}
