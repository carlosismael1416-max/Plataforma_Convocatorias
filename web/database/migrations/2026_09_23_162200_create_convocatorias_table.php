<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocatorias', function (Blueprint $table) {
            $table->id();

            $table->string('titulo', 500);

            $table->text('descripcion')->nullable();
            $table->text('objetivo')->nullable();

            $table->date('fecha_publicacion')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_cierre')->nullable()->index();

            $table->decimal('monto_minimo', 15, 2)->nullable();
            $table->decimal('monto_maximo', 15, 2)->nullable();

            $table->string('moneda', 10)->default('MXN');
            $table->string('modalidad', 100)->nullable();
            $table->string('ubicacion', 255)->nullable();

            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->nullOnDelete();

            $table->foreignId('organismo_id')
                ->nullable()
                ->constrained('organismos')
                ->nullOnDelete();

            $table->foreignId('fuente_id')
                ->nullable()
                ->constrained('fuentes')
                ->nullOnDelete();

            $table->text('url_original')->nullable();

            $table->string('url_hash', 64)
                ->nullable()
                ->unique();

            $table->string('contenido_hash', 64)
                ->nullable()
                ->index();

            $table->string('origen', 30)
                ->default('MANUAL');

            $table->string('estado', 30)
                ->default('PENDIENTE_REVISION')
                ->index();

            $table->timestampTz('fecha_extraccion')
                ->nullable();

            $table->timestamps();
        });

        DB::statement("
            ALTER TABLE convocatorias
            ADD CONSTRAINT chk_convocatorias_fechas
            CHECK (
                fecha_inicio IS NULL
                OR fecha_cierre IS NULL
                OR fecha_cierre >= fecha_inicio
            )
        ");

        DB::statement("
            ALTER TABLE convocatorias
            ADD CONSTRAINT chk_convocatorias_montos
            CHECK (
                (monto_minimo IS NULL OR monto_minimo >= 0)
                AND (monto_maximo IS NULL OR monto_maximo >= 0)
                AND (
                    monto_minimo IS NULL
                    OR monto_maximo IS NULL
                    OR monto_maximo >= monto_minimo
                )
            )
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('convocatorias');
    }
};
