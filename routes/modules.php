<?php

use App\Livewire\Catalogos\Alumnos;
use App\Livewire\Catalogos\Asistencia;
use App\Livewire\Catalogos\Boleta;
use App\Livewire\Catalogos\Calificaciones;
use App\Livewire\Catalogos\CiclosEscolares;
use App\Livewire\Catalogos\Docentes;
use App\Livewire\Catalogos\Grupos;
use App\Livewire\Catalogos\Materias;
use App\Livewire\Catalogos\PadresFamilia;
use App\Livewire\Catalogos\PeriodosEvaluacion;
use App\Livewire\Catalogos\Reinscripciones;
use App\Livewire\Catalogos\Reportes;
use App\Livewire\Catalogos\TutorDashboard;
use App\Livewire\Catalogos\Usuarios;
use Illuminate\Support\Facades\Route;

// ─── Módulos del sistema (fuera del wrapper de Teams) ───

Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard (también existe en {current_team}/dashboard para el starter kit)
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // Catálogos
    Route::prefix('catalogos')->name('catalogos.')->group(function () {
        Route::get('/', fn () => redirect()->route('ciclos-escolares.index'))->name('index');
    });

    // Ciclos Escolares
    Route::prefix('ciclos-escolares')->name('ciclos-escolares.')->group(function () {
        Route::get('/', CiclosEscolares::class)->name('index');
    });

    // Periodos de Evaluación
    Route::prefix('periodos-evaluacion')->name('periodos-evaluacion.')->group(function () {
        Route::get('/', PeriodosEvaluacion::class)->name('index');
    });

    // Materias
    Route::prefix('materias')->name('materias.')->group(function () {
        Route::get('/', Materias::class)->name('index');
    });

    // Usuarios
    Route::prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', Usuarios::class)->name('index');
    });

    // Grupos
    Route::prefix('grupos')->name('grupos.')->group(function () {
        Route::get('/', Grupos::class)->name('index');
    });

    // Docentes
    Route::prefix('docentes')->name('docentes.')->group(function () {
        Route::get('/', Docentes::class)->name('index');
    });

    // Alumnos
    Route::prefix('alumnos')->name('alumnos.')->group(function () {
        Route::get('/', Alumnos::class)->name('index');
    });

    // Padres de Familia
    Route::prefix('padres-familia')->name('padres-familia.')->group(function () {
        Route::get('/', PadresFamilia::class)->name('index');
    });

    // Calificaciones
    Route::prefix('calificaciones')->name('calificaciones.')->group(function () {
        Route::get('/', Calificaciones::class)->name('index');
    });

    // Asistencia
    Route::prefix('asistencia')->name('asistencia.')->group(function () {
        Route::get('/', Asistencia::class)->name('index');
    });

    // Reinscripciones
    Route::prefix('reinscripciones')->name('reinscripciones.')->group(function () {
        Route::get('/', Reinscripciones::class)->name('index');
    });

    // Boleta
    Route::prefix('boleta')->name('boleta.')->group(function () {
        Route::get('/', Boleta::class)->name('index');
    });

    // Reportes
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/', Reportes::class)->name('index');
    });

    // Tutor
    Route::prefix('tutor')->name('tutor.')->group(function () {
        Route::get('/', TutorDashboard::class)->name('dashboard');
    });

});
