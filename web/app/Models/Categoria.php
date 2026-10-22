<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $table = 'categorias';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function convocatorias(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Convocatoria::class);
    }
}
