<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reinscripcion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained('alumnos')->cascadeOnDelete();
            $table->foreignId('from_grado_id')->constrained('grados');
            $table->foreignId('from_grupo_id')->constrained('grupos');
            $table->foreignId('from_ciclo_escolar_id')->constrained('ciclos_escolares');
            $table->foreignId('to_grado_id')->constrained('grados');
            $table->foreignId('to_grupo_id')->constrained('grupos');
            $table->foreignId('to_ciclo_escolar_id')->constrained('ciclos_escolares');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reinscripcion_logs');
    }
};
