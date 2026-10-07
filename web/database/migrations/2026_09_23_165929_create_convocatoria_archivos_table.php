<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocatoria_archivos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('convocatoria_id')
                ->constrained('convocatorias')
                ->cascadeOnDelete();

            $table->string('nombre', 255);

            $table->string('tipo_archivo', 50)
                ->nullable();

            $table->string('mime_type', 150)
                ->nullable();

            $table->text('url_archivo')
                ->nullable();

            $table->text('ruta_archivo')
                ->nullable();

            $table->unsignedBigInteger('tamano_bytes')
                ->nullable();

            $table->string('hash_archivo', 128)
                ->nullable();

            $table->timestamps();

            $table->index('convocatoria_id');
            $table->index('hash_archivo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocatoria_archivos');
    }
};
