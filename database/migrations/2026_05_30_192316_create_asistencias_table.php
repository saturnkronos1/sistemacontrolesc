<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->date('fecha');
            $table->string('estatus', 20)->default('asistio'); // asistio, falta, retardo, justificado
            $table->timestamps();

            $table->unique(['alumno_id', 'grupo_id', 'fecha'], 'asistencia_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
