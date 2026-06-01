<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boleta_observaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('grupo_id')->constrained('grupos')->cascadeOnDelete();
            $table->foreignId('periodo_evaluacion_id')->constrained('periodos_evaluacion')->cascadeOnDelete();
            $table->text('observacion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boleta_observaciones');
    }
};
