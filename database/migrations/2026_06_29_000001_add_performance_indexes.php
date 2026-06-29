<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índices para búsqueda y ordenamiento en personas
        Schema::table('personas', function (Blueprint $table) {
            $table->index(['apellido_paterno', 'apellido_materno', 'nombre'], 'personas_nombre_completo_index');
        });

        // Índice para filtro por estatus (activosConPersona scope)
        Schema::table('alumnos', function (Blueprint $table) {
            $table->index('estatus', 'alumnos_estatus_index');
        });

        // Índice para consultas por rango de fechas en asistencia
        Schema::table('asistencias', function (Blueprint $table) {
            $table->index('fecha', 'asistencias_fecha_index');
            $table->index(['grupo_id', 'fecha'], 'asistencias_grupo_fecha_index');
        });

        // Índice compuesto para consultas de calificaciones por periodo
        Schema::table('calificaciones', function (Blueprint $table) {
            $table->index(['grupo_id', 'materia_id', 'periodo_evaluacion_id'], 'calificaciones_consulta_index');
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropIndex('personas_nombre_completo_index');
        });

        Schema::table('alumnos', function (Blueprint $table) {
            $table->dropIndex('alumnos_estatus_index');
        });

        Schema::table('asistencias', function (Blueprint $table) {
            $table->dropIndex('asistencias_fecha_index');
            $table->dropIndex('asistencias_grupo_fecha_index');
        });

        Schema::table('calificaciones', function (Blueprint $table) {
            $table->dropIndex('calificaciones_consulta_index');
        });
    }
};
