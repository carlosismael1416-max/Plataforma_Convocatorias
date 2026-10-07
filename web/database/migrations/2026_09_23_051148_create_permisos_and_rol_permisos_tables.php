<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->string('modulo', 100)->nullable();
            $table->timestamps();
        });

        Schema::create('rol_permisos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            $table->foreignId('permiso_id')
                ->constrained('permisos')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(
                ['role_id', 'permiso_id'],
                'rol_permisos_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rol_permisos');
        Schema::dropIfExists('permisos');
    }
};
