<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->foreignId('grupo_id')->nullable()->after('grado_id')->constrained('grupos')->nullOnDelete();
            $table->foreignId('ciclo_escolar_id')->nullable()->after('grupo_id')->constrained('ciclos_escolares')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('grupo_id');
            $table->dropConstrainedForeignId('ciclo_escolar_id');
        });
    }
};
