<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promocion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alumno_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ciclo_origen_id')->constrained('ciclos_escolares')->cascadeOnDelete();
            $table->foreignId('ciclo_destino_id')->constrained('ciclos_escolares')->cascadeOnDelete();
            $table->foreignId('grado_origen_id')->constrained('grados');
            $table->foreignId('grado_destino_id')->constrained('grados');
            $table->foreignId('grupo_origen_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->foreignId('grupo_destino_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->string('tipo', 30)->default('promocion_automatica');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promocion_logs');
    }
};
