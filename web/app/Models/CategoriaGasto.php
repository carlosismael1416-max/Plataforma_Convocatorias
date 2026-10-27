<?php

namespace App\Models;

use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Partida del Clasificador por Objeto del Gasto (CONAC): capítulo (2000),
 * concepto (2100), partida genérica (211) y partida específica (2111). La
 * administran el admin y el directivo en «Gestión de propuestas».
 *
 * El docente elige una partida: la específica cuando existe y, si no, la
 * genérica (regla del clasificador). El concepto del presupuesto guarda la
 * clave (presupuesto_items.categoria_gasto).
 */
class CategoriaGasto extends Model
{
    use RegistraActividad;

    public const NIVELES = [
        'CAPITULO' => 'Capítulo',
        'CONCEPTO' => 'Concepto',
        'PARTIDA_GENERICA' => 'Partida genérica',
        'PARTIDA_ESPECIFICA' => 'Partida específica',
    ];

    // Clasificador por Tipo de Gasto (CONAC).
    public const TIPOS_GASTO = [
        'CORRIENTE' => 'Gasto corriente',
        'CAPITAL' => 'Gasto de capital',
        'PENSIONES' => 'Pensiones y jubilaciones',
    ];

    protected $table = 'categorias_gasto';

    protected $fillable = ['clave', 'nivel', 'padre_clave', 'nombre', 'descripcion', 'tipo_gasto', 'activa', 'orden'];

    protected function casts(): array
    {
        return ['activa' => 'boolean', 'orden' => 'integer'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $partida): void {
            $partida->nivel = static::nivelDeClave((string) $partida->clave) ?? $partida->nivel;
            $partida->padre_clave = static::padreDeClave((string) $partida->clave);
            $partida->orden ??= ((int) static::query()->max('orden')) + 10;
        });

        static::saved(fn () => static::olvidarCatalogo());
        static::deleted(fn () => static::olvidarCatalogo());
    }

    /** 2000 capítulo, 2100 concepto, 211 genérica, 2111 específica. */
    public static function nivelDeClave(string $clave): ?string
    {
        return match (true) {
            (bool) preg_match('/^[1-9]000$/', $clave) => 'CAPITULO',
            (bool) preg_match('/^[1-9][1-9]00$/', $clave) => 'CONCEPTO',
            (bool) preg_match('/^[1-9][1-9][1-9]$/', $clave) => 'PARTIDA_GENERICA',
            (bool) preg_match('/^[1-9][1-9][1-9][1-9]$/', $clave) => 'PARTIDA_ESPECIFICA',
            default => null,
        };
    }

    public static function padreDeClave(string $clave): ?string
    {
        return match (static::nivelDeClave($clave)) {
            'CONCEPTO' => $clave[0] . '000',
            'PARTIDA_GENERICA' => substr($clave, 0, 2) . '00',
            'PARTIDA_ESPECIFICA' => substr($clave, 0, 3),
            default => null,
        };
    }

    /** @return array<string, self> Catálogo completo, leído una vez por petición. */
    public static function catalogo(): array
    {
        if (! app()->bound('catalogo-gasto')) {
            app()->instance('catalogo-gasto', static::query()->orderBy('orden')->orderBy('clave')->get()->keyBy('clave')->all());
        }

        return app('catalogo-gasto');
    }

    public static function olvidarCatalogo(): void
    {
        app()->forgetInstance('catalogo-gasto');
    }

    /** ¿Se puede asignar a un concepto? (específica, o genérica sin específicas). */
    public function seleccionable(): bool
    {
        if ($this->nivel === 'PARTIDA_ESPECIFICA') {
            return true;
        }

        return $this->nivel === 'PARTIDA_GENERICA'
            && ! collect(static::catalogo())->contains(fn (self $p) => $p->padre_clave === $this->clave);
    }

    /** Activa y con todos sus antecesores activos. */
    public function ofrecida(): bool
    {
        $catalogo = static::catalogo();

        for ($p = $this; $p; $p = $p->padre_clave ? ($catalogo[$p->padre_clave] ?? null) : null) {
            if (! $p->activa) {
                return false;
            }
        }

        return true;
    }

    /**
     * Partidas que puede elegir el docente, agrupadas por
     * «capítulo › concepto». Incluye $actual aunque ya no se ofrezca.
     *
     * @return array<string, array<string, string>>
     */
    public static function opcionesAgrupadas(?string $actual = null): array
    {
        $grupos = [];

        foreach (static::catalogo() as $clave => $p) {
            // Las claves numéricas llegan como enteros: se comparan como texto.
            if (! $p->seleccionable() || (! $p->ofrecida() && (string) $clave !== (string) $actual)) {
                continue;
            }

            $ruta = $p->ruta();
            $grupo = trim(($ruta['capitulo']?->clave ?? '') . ' ' . ($ruta['capitulo']?->nombre ?? '')) . ' › '
                . trim(($ruta['concepto']?->clave ?? '') . ' ' . ($ruta['concepto']?->nombre ?? ''));
            $grupos[$grupo][$clave] = $clave . ' · ' . $p->nombre;
        }

        return $grupos;
    }

    /** @return array<string, string> clave => «clave · nombre» de las partidas elegibles. */
    public static function opciones(?string $actual = null): array
    {
        // Con «+» y no array_merge: las claves (3751) son numéricas y se renumerarían.
        $todas = [];
        foreach (static::opcionesAgrupadas($actual) as $grupo) {
            $todas += $grupo;
        }

        return $todas;
    }

    /** @return array<string, string> clave => «clave · nombre» de todo el catálogo. */
    public static function etiquetas(): array
    {
        return collect(static::catalogo())->map(fn (self $p) => $p->clave . ' · ' . $p->nombre)->all();
    }

    public static function etiqueta(?string $clave): string
    {
        if (blank($clave)) {
            return 'Sin categoría';
        }

        return static::etiquetas()[$clave] ?? Str::title(str_replace('_', ' ', mb_strtolower($clave)));
    }

    public static function buscar(?string $clave): ?self
    {
        return $clave ? (static::catalogo()[$clave] ?? null) : null;
    }

    /** @return array{capitulo: ?self, concepto: ?self, generica: ?self, especifica: ?self} */
    public function ruta(): array
    {
        $catalogo = static::catalogo();
        $ruta = ['capitulo' => null, 'concepto' => null, 'generica' => null, 'especifica' => null];
        $mapa = ['CAPITULO' => 'capitulo', 'CONCEPTO' => 'concepto', 'PARTIDA_GENERICA' => 'generica', 'PARTIDA_ESPECIFICA' => 'especifica'];

        for ($p = $this; $p; $p = $p->padre_clave ? ($catalogo[$p->padre_clave] ?? null) : null) {
            $ruta[$mapa[$p->nivel] ?? 'generica'] = $p;
        }

        return $ruta;
    }

    public function tipoGastoTexto(): string
    {
        return self::TIPOS_GASTO[$this->tipo_gasto] ?? (string) $this->tipo_gasto;
    }

    public function nivelTexto(): string
    {
        return self::NIVELES[$this->nivel] ?? (string) $this->nivel;
    }

    public function tieneHijas(): bool
    {
        return collect(static::catalogo())->contains(fn (self $p) => $p->padre_clave === $this->clave);
    }

    public function usos(): int
    {
        return PresupuestoItem::query()->where('categoria_gasto', $this->clave)->count();
    }

    /** @return Collection<int, array{clave: string, nombre: string, descripcion: ?string, tipo: string}> */
    public static function datosParaDocente(): Collection
    {
        return collect(static::catalogo())
            ->filter(fn (self $p) => $p->seleccionable())
            ->map(function (self $p) {
                $r = $p->ruta();

                return [
                    'capitulo' => $r['capitulo'] ? $r['capitulo']->clave . ' · ' . $r['capitulo']->nombre : null,
                    'concepto' => $r['concepto'] ? $r['concepto']->clave . ' · ' . $r['concepto']->nombre : null,
                    'generica' => $r['generica'] ? $r['generica']->clave . ' · ' . $r['generica']->nombre : null,
                    'especifica' => $r['especifica'] ? $r['especifica']->clave . ' · ' . $r['especifica']->nombre : null,
                    'descripcion' => $p->descripcion,
                    'tipo' => $p->tipoGastoTexto(),
                ];
            });
    }

    protected function accionActividad(): array
    {
        return ['CATALOGO', 'la partida de gasto'];
    }

    protected function nombreActividad(): string
    {
        return $this->clave . ' ' . $this->nombre;
    }
}
