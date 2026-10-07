<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Convocatoria extends Model
{
    protected $table = 'convocatorias';

    protected $fillable = [
        'titulo',
        'descripcion',
        'objetivo',
        'fecha_publicacion',
        'fecha_inicio',
        'fecha_cierre',
        'monto_minimo',
        'monto_maximo',
        'moneda',
        'modalidad',
        'ubicacion',
        'categoria_id',
        'organismo_id',
        'fuente_id',
        'subido_por_user_id',
        'url_original',
        'url_hash',
        'contenido_hash',
        'origen',
        'estado',
        'fecha_extraccion',
    ];

	public function usuariosSeguimiento(): HasMany
{
    return $this->hasMany(
        UsuarioConvocatoria::class,
        'convocatoria_id'
    );
}

    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'date',
            'fecha_inicio' => 'date',
            'fecha_cierre' => 'date',
            'fecha_extraccion' => 'datetime',

            'monto_minimo' => 'decimal:2',
            'monto_maximo' => 'decimal:2',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function organismo(): BelongsTo
    {
        return $this->belongsTo(Organismo::class);
    }

    public function fuente(): BelongsTo
    {
        return $this->belongsTo(Fuente::class);
    }

    public function requisitos(): HasMany
    {
        return $this->hasMany(ConvocatoriaRequisito::class)
            ->orderBy('orden');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(ConvocatoriaArchivo::class);
    }

    public function revisiones(): HasMany
    {
        return $this->hasMany(ConvocatoriaRevision::class)
            ->orderByDesc('fecha_revision');
    }

    public function modalidadesFinanciamiento(): HasMany
    {
        return $this->hasMany(
            ConvocatoriaModalidad::class,
            'convocatoria_id'
        );
    }

    public function apoyosFinancieros(): HasMany
    {
        return $this->hasMany(
            ConvocatoriaApoyo::class,
            'convocatoria_id'
        );
    }

    public function revisionesAdministrativas(): HasMany
    {
        return $this->hasMany(
            ConvocatoriaRevisionAdministrativa::class,
            'convocatoria_id'
        )
            ->orderByDesc('fecha_decision')
            ->orderByDesc('id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $convocatoria): void {
            $url = trim(
                (string) $convocatoria->url_original
            );

            if ($url === '') {
                $convocatoria->url_original = null;
                $convocatoria->url_hash = null;

                return;
            }

            $convocatoria->url_original = $url;

            $convocatoria->url_hash = hash(
                'sha256',
                $url
            );
        });
    }
public function propuestas(): HasMany
{
    return $this->hasMany(Propuesta::class);
}

public function propuestasBase(): HasMany
{
    return $this->hasMany(PropuestaBase::class);
}
public function financiamientos(): \Illuminate\Database\Eloquent\Relations\HasMany
{
    return $this->hasMany(
        ConvocatoriaFinanciamiento::class,
        'convocatoria_id'
    );
}

    public function subidoPor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'subido_por_user_id'
        );
    }
}
