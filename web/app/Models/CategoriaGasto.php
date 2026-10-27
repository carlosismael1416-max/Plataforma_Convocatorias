<?php

namespace App\Models;

use App\Models\Concerns\RegistraActividad;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Categoría de gasto del presupuesto de una propuesta (Equipo, Viáticos…).
 * La administran el admin y el directivo en «Gestión de propuestas»; los
 * conceptos guardan la clave (presupuesto_items.categoria_gasto).
 */
class CategoriaGasto extends Model
{
    use RegistraActividad;

    protected $table = 'categorias_gasto';

    protected $fillable = ['clave', 'nombre', 'descripcion', 'activa', 'orden'];

    protected function casts(): array
    {
        return ['activa' => 'boolean', 'orden' => 'integer'];
    }

    protected static function booted(): void
    {
        // La clave se fija al crearla (los conceptos la guardan) y no cambia.
        static::creating(function (self $categoria): void {
            if (blank($categoria->clave)) {
                $base = Str::upper(Str::slug(Str::ascii((string) $categoria->nombre), '_')) ?: 'CATEGORIA';
                $clave = Str::limit($base, 36, '');
                $n = 1;
                while (static::query()->where('clave', $clave)->exists()) {
                    $clave = Str::limit($base, 33, '') . '_' . (++$n);
                }
                $categoria->clave = $clave;
            }

            $categoria->orden ??= ((int) static::query()->max('orden')) + 10;
        });
    }

    /** Opciones para elegir (activas, en orden); incluye $actual aunque esté desactivada. */
    public static function opciones(?string $actual = null): array
    {
        return static::query()
            ->where(fn ($q) => $q->where('activa', true)->when($actual, fn ($q) => $q->orWhere('clave', $actual)))
            ->orderBy('orden')
            ->orderBy('nombre')
            ->pluck('nombre', 'clave')
            ->all();
    }

    /** Nombre de todas las categorías (también desactivadas), por clave. */
    public static function etiquetas(): array
    {
        return static::query()->pluck('nombre', 'clave')->all();
    }

    public static function etiqueta(?string $clave): string
    {
        if (blank($clave)) {
            return 'Sin categoría';
        }

        return static::etiquetas()[$clave] ?? Str::title(str_replace('_', ' ', mb_strtolower($clave)));
    }

    public function usos(): int
    {
        return PresupuestoItem::query()->where('categoria_gasto', $this->clave)->count();
    }

    protected function accionActividad(): array
    {
        return ['CATALOGO', 'la categoría de gasto'];
    }

    protected function nombreActividad(): string
    {
        return (string) $this->nombre;
    }
}
