<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Propuestas de infraestructura (modelo del Fondo de Aportaciones
 * Múltiples): diagnóstico de la capacidad física instalada, hasta 5
 * proyectos priorizados y láminas de presentación por proyecto. Reutilizan
 * el presupuesto (partidas del clasificador), el cronograma y las
 * evidencias, que pueden asignarse a un proyecto.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('propuestas', function (Blueprint $table) {
            // INVESTIGACION (la de siempre) o INFRAESTRUCTURA.
            $table->string('tipo', 30)->default('INVESTIGACION')->after('propuesta_base_id');
            $table->text('diagnostico')->nullable()->after('impacto_esperado');
        });

        Schema::create('propuesta_proyectos_infraestructura', function (Blueprint $table) {
            $table->id();
            $table->foreignId('propuesta_id')->constrained('propuestas')->cascadeOnDelete();
            $table->unsignedSmallInteger('prioridad');
            $table->string('nombre', 300);
            // OBRA_NUEVA, CONTINUIDAD, EQUIPAMIENTO, ADECUACION, MANTENIMIENTO, SUSTENTABLE
            $table->string('tipo_proyecto', 30);
            $table->text('objetivo')->nullable();
            $table->text('justificacion')->nullable();
            $table->text('impacto')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('campus', 255)->nullable();
            $table->string('municipio', 150)->nullable();
            $table->string('coordenadas', 80)->nullable();
            $table->unsignedInteger('alumnos_mujeres')->nullable();
            $table->unsignedInteger('alumnos_hombres')->nullable();
            $table->unsignedInteger('personal_academico')->nullable();
            $table->decimal('superficie_m2', 12, 2)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            // Lo que se pide al FAM sale del presupuesto asignado al proyecto;
            // aquí solo lo que aportan otros fondos.
            $table->decimal('monto_otros_fondos', 14, 2)->nullable();
            $table->text('financiamiento_previo')->nullable();
            $table->timestamps();

            $table->unique(['propuesta_id', 'prioridad']);
        });

        Schema::table('presupuesto_items', function (Blueprint $table) {
            $table->foreignId('proyecto_infraestructura_id')->nullable()
                ->constrained('propuesta_proyectos_infraestructura')->nullOnDelete();
        });

        Schema::table('cronograma_actividades', function (Blueprint $table) {
            $table->foreignId('proyecto_infraestructura_id')->nullable()
                ->constrained('propuesta_proyectos_infraestructura')->nullOnDelete();
        });

        Schema::table('evidencias', function (Blueprint $table) {
            $table->foreignId('proyecto_infraestructura_id')->nullable()
                ->constrained('propuesta_proyectos_infraestructura')->cascadeOnDelete();
            // Lámina de presentación (FOTOS_TERRENO, PLANTA_ARQUITECTONICA…).
            $table->string('tipo_lamina', 60)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('evidencias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proyecto_infraestructura_id');
            $table->dropColumn('tipo_lamina');
        });

        Schema::table('cronograma_actividades', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proyecto_infraestructura_id');
        });

        Schema::table('presupuesto_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('proyecto_infraestructura_id');
        });

        Schema::dropIfExists('propuesta_proyectos_infraestructura');

        Schema::table('propuestas', function (Blueprint $table) {
            $table->dropColumn(['tipo', 'diagnostico']);
        });
    }
};
