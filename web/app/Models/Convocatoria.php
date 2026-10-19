<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Convocatoria extends Model
{
    protected $table = 'convocatorias';

    protected $fillable = [
                'convocatoria_padre_id',
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
        'tipo',
        'dirigido_a',
    ];

    /** Tipos visibles: etiqueta, icono corto y color (fondo, texto). */
    public const TIPOS = [
        'FINANCIAMIENTO' => ['Financiamiento', '💰', '#DBEAFE', '#1D4ED8'],
        'BECA' => ['Beca', '🎓', '#EDE9FE', '#6D28D9'],
        'PREMIO' => ['Premio / reconocimiento', '🏆', '#FEF3C7', '#B45309'],
        'CONCURSO' => ['Concurso', '🥇', '#FCE7F3', '#BE185D'],
        'EVENTO' => ['Evento', '📅', '#CFFAFE', '#0E7490'],
        'FORMACION' => ['Formación', '📚', '#D1FAE5', '#047857'],
    ];

    /** A quién va dirigida o a quién puede ayudar. */
    public const AUDIENCIAS = [
        'INSTITUCION' => 'Institución',
        'DOCENTES' => 'Docentes e investigadores',
        'ESTUDIANTES' => 'Estudiantes',
    ];

    /** [etiqueta, icono, fondo, texto] del tipo; las antiguas sin tipo cuentan como financiamiento. */
    public function infoTipo(): array
    {
        return self::TIPOS[$this->tipo ?? 'FINANCIAMIENTO'] ?? self::TIPOS['FINANCIAMIENTO'];
    }

    /** Etiquetas de a quién va dirigida (vacío si no se sabe). */
    public function etiquetasDirigidoA(): array
    {
        return array_values(array_filter(array_map(
            fn ($clave) => self::AUDIENCIAS[$clave] ?? null,
            (array) ($this->dirigido_a ?? [])
        )));
    }

	public function usuariosSeguimiento(): HasMany
{
    return $this->hasMany(
        UsuarioConvocatoria::class,
        'convocatoria_id'
    );
}

    /**
     * Excluye las convocatorias cuya fecha de cierre ya pasó.
     * Las que no tienen fecha se conservan (filtro "sin fecha").
     */
    /**
     * Lo que ven todos los usuarios como convocatoria abierta (docente,
     * directivo y admin usan el mismo criterio): publicada y no vencida.
     */
    public function scopeVisibles(Builder $query): Builder
    {
        return $query
            ->whereIn('estado', ['PUBLICADA', 'ACTIVA'])
            ->where(fn (Builder $q) => $q->whereNull('tipo')->orWhere('tipo', '<>', 'NO_APLICA'))
            ->noVencidas();
    }

    /** Historial: cerradas o publicadas cuya fecha de cierre ya pasó. */
    public function scopeHistorial(Builder $query): Builder
    {
        // Resultados, vacantes y no elegibles (tipo NO_APLICA) no son
        // convocatorias que alguien hubiera podido aprovechar.
        return $query->where(
            fn (Builder $q) => $q->whereNull('tipo')->orWhere('tipo', '<>', 'NO_APLICA')
        )->where(
            fn (Builder $q) => $q
                ->where('estado', 'CERRADA')
                ->orWhere(
                    fn (Builder $p) => $p
                        ->whereIn('estado', ['PUBLICADA', 'ACTIVA'])
                        ->whereDate('fecha_cierre', '<', today())
                )
        );
    }

    /** Filtra por tipo (FINANCIAMIENTO incluye las antiguas sin tipo) y por audiencia. */
    public function scopeDeTipo(Builder $query, ?string $tipo): Builder
    {
        if (! $tipo || ! isset(self::TIPOS[$tipo])) {
            return $query;
        }

        return $tipo === 'FINANCIAMIENTO'
            ? $query->where(fn (Builder $q) => $q->whereNull('tipo')->orWhere('tipo', 'FINANCIAMIENTO'))
            : $query->where('tipo', $tipo);
    }

    public function scopeDirigidaA(Builder $query, ?string $audiencia): Builder
    {
        if (! $audiencia || ! isset(self::AUDIENCIAS[$audiencia])) {
            return $query;
        }

        return $query->whereJsonContains('dirigido_a', $audiencia);
    }

    public function scopeNoVencidas(Builder $query): Builder
    {
        return $query->where(
            fn (Builder $consulta) => $consulta
                ->whereNull('fecha_cierre')
                ->orWhereDate('fecha_cierre', '>=', today())
        );
    }

    protected function casts(): array
    {
        return [
            'fecha_publicacion' => 'date',
            'fecha_inicio' => 'date',
            'fecha_cierre' => 'date',
            'dirigido_a' => 'array',
            'fecha_extraccion' => 'datetime',

            'monto_minimo' => 'decimal:2',
            'monto_maximo' => 'decimal:2',
        ];
    }

    public function padre(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'convocatoria_padre_id'
        );
    }

    public function capitulos(): HasMany
    {
        return $this->hasMany(
            self::class,
            'convocatoria_padre_id'
        )->orderBy('id');
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

            $convocatoria->url_hash = static::hashUrl($url);
        });
    }

    /**
     * Misma normalización que extractor/app/urls.py: esquema y host en
     * minúsculas, sin "www.", sin puerto ni fragmento, sin barra final
     * (salvo la raíz) y conservando la query. Ambos lados deben producir
     * el mismo url_hash para que el índice único detecte duplicados.
     */
    public static function normalizarUrl(string $url): string
    {
        $partes = parse_url(trim($url));

        if ($partes === false || ! isset($partes['host'])) {
            return rtrim(trim($url), '/');
        }

        $host = strtolower($partes['host']);

        if (str_starts_with($host, 'www.')) {
            $host = substr($host, 4);
        }

        $ruta = ($partes['path'] ?? '') !== '' ? $partes['path'] : '/';

        if ($ruta !== '/') {
            $ruta = rtrim($ruta, '/');
        }

        $resultado = strtolower($partes['scheme'] ?? 'https')
            . '://' . $host . $ruta;

        if (($partes['query'] ?? '') !== '') {
            $resultado .= '?' . $partes['query'];
        }

        return $resultado;
    }

    public static function hashUrl(string $url): string
    {
        return hash('sha256', static::normalizarUrl($url));
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

    public function seguimiento(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ConvocatoriaSeguimiento::class);
    }
}
