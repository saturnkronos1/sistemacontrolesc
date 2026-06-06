<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->string('telefono_2', 20)->nullable()->after('telefono');
            $table->string('email', 100)->nullable()->after('telefono_2');
            $table->date('fecha_nacimiento')->nullable()->after('email');
            $table->text('domicilio')->nullable()->after('fecha_nacimiento');
            $table->string('foto_perfil')->nullable()->after('domicilio');
        });
    }

    public function down(): void
    {
        Schema::table('personas', function (Blueprint $table) {
            $table->dropColumn(['telefono_2', 'email', 'fecha_nacimiento', 'domicilio', 'foto_perfil']);
        });
    }
};
