<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescubrimientoWeb extends Model
{
    protected $table = 'descubrimientos_web';

    protected $fillable = [
        'titulo',
        'url_original',
        'url_normalizada',
        'url_hash',
        'dominio',
        'metodo_descubrimiento',
        'pagina_origen',
        'tipo',
        'puntaje',
        'relevancia_tematica',
        'estado_temporal',
        'anios_detectados',
        'origen_anio',
        'razones',
        'categoria_objetivo',
        'decision_objetivo',
        'puntaje_objetivo',
        'razones_objetivo',
        'estado',
        'estado_fuente',
        'evidencia',
        'fuente_id',
        'convocatoria_id',
        'descubierto_en',
        'ultimo_visto_en',
        'procesado_en',
        'error_ultimo',
    ];

    protected function casts(): array
    {
        return [
            'puntaje' => 'integer',
            'puntaje_objetivo' => 'integer',

            'anios_detectados' => 'array',
            'razones' => 'array',
            'razones_objetivo' => 'array',
            'evidencia' => 'array',

            'descubierto_en' => 'datetime',
            'ultimo_visto_en' => 'datetime',
            'procesado_en' => 'datetime',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function fuente(): BelongsTo
    {
        return $this->belongsTo(
            Fuente::class,
            'fuente_id'
        );
    }

    public function convocatoria(): BelongsTo
    {
        return $this->belongsTo(
            Convocatoria::class,
            'convocatoria_id'
        );
    }

    /**
     * Título para mostrar: repara el texto mal decodificado ("AmÃ©rico") de
     * registros antiguos y, si no hay título (p. ej. sitemaps), lo deriva de
     * la ruta de la URL. No modifica el registro.
     */
    public function tituloLegible(): string
    {
        $titulo = trim((string) $this->titulo);

        if ($titulo !== '' && str_contains($titulo, 'Ã')) {
            $reparado = mb_convert_encoding($titulo, 'ISO-8859-1', 'UTF-8');

            if (mb_check_encoding($reparado, 'UTF-8')) {
                $titulo = $reparado;
            }
        }

        if ($titulo !== '') {
            return $titulo;
        }

        $ruta = (string) parse_url((string) $this->url_original, PHP_URL_PATH);
        $tramo = collect(explode('/', trim($ruta, '/')))
            ->reverse()
            ->first(fn (string $parte): bool => preg_match('/[a-z]{3,}/i', $parte) === 1);

        if (! $tramo) {
            return (string) ($this->url_original ?: 'Sin título');
        }

        $tramo = preg_replace('/\.(html?|php|aspx?)$/i', '', urldecode($tramo));
        $tramo = preg_replace('/[-_]+\d{4,}$/', '', $tramo);

        return '(sin título) ' . ucfirst(str_replace(['-', '_'], ' ', $tramo));
    }
}
