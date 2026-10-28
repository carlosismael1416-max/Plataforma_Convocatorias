<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Proyecto de una propuesta de infraestructura (modelo del FAM, Guía
 * DGESUI 2027): hasta 5 por propuesta, priorizados, de uno de seis tipos.
 * Cada tipo pide sus láminas de presentación.
 */
class ProyectoInfraestructura extends Model
{
    public const MAXIMO_POR_PROPUESTA = 5;

    public const TIPOS = [
        'OBRA_NUEVA' => 'Obra nueva',
        'CONTINUIDAD' => 'Obra de continuidad (terminación)',
        'EQUIPAMIENTO' => 'Equipamiento especializado (aulas, talleres, laboratorios, centros de información)',
        'ADECUACION' => 'Adecuaciones y mejoras de espacios existentes',
        'MANTENIMIENTO' => 'Mantenimiento correctivo',
        'SUSTENTABLE' => 'Proyecto técnico en materia de sustentabilidad',
    ];

    public const LAMINAS = [
        'FOTOS_TERRENO' => 'Fotografías del terreno',
        'PROPIEDAD_PREDIO' => 'Documento de propiedad o posesión legal del predio',
        'FOTOS_OBRA_PREVIA' => 'Evidencia fotográfica de la obra (etapa previa)',
        'FOTOS_ESTADO_ACTUAL' => 'Evidencia fotográfica del estado actual de los espacios',
        'PROGRAMA_ARQUITECTONICO' => 'Programa arquitectónico',
        'PLANTA_ARQUITECTONICA' => 'Planta arquitectónica, cortes y fachadas',
        'PLANTA_CONJUNTO' => 'Planta de conjunto',
        'RENDER' => 'Perspectiva o render',
        'ESPECIFICACIONES_EQUIPO' => 'Especificaciones técnicas e imágenes del equipo',
        'CROQUIS_AREA' => 'Plano o croquis del área a intervenir',
        'NORMAS' => 'Normas (NOM, NMX) o certificaciones que inciden en el proyecto',
        'ESTUDIOS_TECNICOS' => 'Mecánica de suelos e impacto ambiental (si resulta apoyado)',
        'OTRA' => 'Otra lámina',
    ];

    /** Láminas que pide la guía por tipo de proyecto (las demás son opcionales). */
    public const LAMINAS_REQUERIDAS = [
        'OBRA_NUEVA' => ['FOTOS_TERRENO', 'PROPIEDAD_PREDIO', 'PROGRAMA_ARQUITECTONICO', 'PLANTA_ARQUITECTONICA', 'PLANTA_CONJUNTO', 'RENDER'],
        'CONTINUIDAD' => ['FOTOS_OBRA_PREVIA', 'PROGRAMA_ARQUITECTONICO', 'PLANTA_ARQUITECTONICA', 'PLANTA_CONJUNTO', 'RENDER'],
        'EQUIPAMIENTO' => ['ESPECIFICACIONES_EQUIPO', 'FOTOS_ESTADO_ACTUAL'],
        'ADECUACION' => ['FOTOS_ESTADO_ACTUAL', 'CROQUIS_AREA'],
        'MANTENIMIENTO' => ['FOTOS_ESTADO_ACTUAL', 'CROQUIS_AREA'],
        'SUSTENTABLE' => ['NORMAS', 'PLANTA_ARQUITECTONICA', 'PLANTA_CONJUNTO', 'RENDER'],
    ];

    protected $table = 'propuesta_proyectos_infraestructura';

    protected $fillable = [
        'propuesta_id', 'prioridad', 'nombre', 'tipo_proyecto', 'objetivo', 'justificacion', 'impacto',
        'descripcion', 'campus', 'municipio', 'coordenadas', 'alumnos_mujeres', 'alumnos_hombres',
        'personal_academico', 'superficie_m2', 'fecha_inicio', 'fecha_fin', 'monto_otros_fondos',
        'financiamiento_previo',
    ];

    protected function casts(): array
    {
        return [
            'prioridad' => 'integer',
            'alumnos_mujeres' => 'integer',
            'alumnos_hombres' => 'integer',
            'personal_academico' => 'integer',
            'superficie_m2' => 'decimal:2',
            'monto_otros_fondos' => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
        ];
    }

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(Propuesta::class);
    }

    public function itemsPresupuesto(): HasMany
    {
        return $this->hasMany(PresupuestoItem::class, 'proyecto_infraestructura_id');
    }

    public function actividades(): HasMany
    {
        return $this->hasMany(CronogramaActividad::class, 'proyecto_infraestructura_id');
    }

    public function laminas(): HasMany
    {
        return $this->hasMany(Evidencia::class, 'proyecto_infraestructura_id');
    }

    public function tipoTexto(): string
    {
        return self::TIPOS[$this->tipo_proyecto] ?? (string) $this->tipo_proyecto;
    }

    /** Monto solicitado al FAM: lo presupuestado para este proyecto. */
    public function montoFam(): float
    {
        return (float) $this->itemsPresupuesto->sum(fn (PresupuestoItem $i) => (float) $i->cantidad * (float) $i->precio_unitario);
    }

    public function montoTotal(): float
    {
        return $this->montoFam() + (float) ($this->monto_otros_fondos ?? 0);
    }

    public function beneficiarios(): int
    {
        return (int) $this->alumnos_mujeres + (int) $this->alumnos_hombres;
    }

    /** @return list<string> Láminas requeridas que aún no tiene. */
    public function laminasFaltantes(): array
    {
        $tiene = $this->laminas->pluck('tipo_lamina')->filter()->unique()->all();

        return array_values(array_diff(self::LAMINAS_REQUERIDAS[$this->tipo_proyecto] ?? [], $tiene));
    }

    /** ¿Tiene lo que la guía pide para presentarlo? */
    public function completo(): bool
    {
        return trim(strip_tags((string) $this->objetivo)) !== ''
            && trim(strip_tags((string) $this->justificacion)) !== ''
            && trim(strip_tags((string) $this->descripcion)) !== ''
            && $this->itemsPresupuesto->isNotEmpty()
            && $this->laminasFaltantes() === [];
    }
}
