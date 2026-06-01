<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete();
            $table->foreignId('periodo_evaluacion_id')->constrained('periodos_evaluacion')->cascadeOnDelete();
            $table->decimal('calificacion', 4, 2)->nullable();
            $table->timestamps();

            $table->unique(['alumno_id', 'grupo_id', 'materia_id', 'periodo_evaluacion_id'], 'calificacion_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
