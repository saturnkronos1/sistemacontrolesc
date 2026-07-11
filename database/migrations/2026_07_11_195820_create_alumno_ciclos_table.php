<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumno_ciclos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ciclo_escolar_id')->constrained('ciclos_escolares')->cascadeOnDelete();
            $table->foreignId('grado_id')->constrained('grados');
            $table->foreignId('grupo_id')->nullable()->constrained()->nullOnDelete();
            $table->string('estatus', 20)->default('activo');
            $table->timestamps();

            $table->unique(['alumno_id', 'ciclo_escolar_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumno_ciclos');
    }
};
