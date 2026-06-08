<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ciclos_escolares', function (Blueprint $table) {
            $table->string('estatus', 20)->default('pendiente')->after('fecha_fin');
            $table->boolean('autocreado')->default(false)->after('estatus');
        });

        // Migrate existing data: activo = 1 → estatus = 'activo', activo = 0 → 'pendiente'
        DB::table('ciclos_escolares')
            ->where('activo', true)
            ->update(['estatus' => 'activo']);

        DB::table('ciclos_escolares')
            ->where('activo', false)
            ->update(['estatus' => 'pendiente']);

        Schema::table('ciclos_escolares', function (Blueprint $table) {
            $table->dropColumn('activo');
        });
    }

    public function down(): void
    {
        Schema::table('ciclos_escolares', function (Blueprint $table) {
            $table->boolean('activo')->default(false)->after('fecha_fin');
        });

        // Restore existing data
        DB::table('ciclos_escolares')
            ->where('estatus', 'activo')
            ->update(['activo' => true]);

        DB::table('ciclos_escolares')
            ->where('estatus', '!=', 'activo')
            ->update(['activo' => false]);

        Schema::table('ciclos_escolares', function (Blueprint $table) {
            $table->dropColumn(['estatus', 'autocreado']);
        });
    }
};
