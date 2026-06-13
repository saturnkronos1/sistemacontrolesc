<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\User;
use App\Support\CicloActivoService;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(CicloActivoService $cicloActivoService)
    {
        $cicloActivo = $cicloActivoService->get();
        $cicloActivoId = $cicloActivo?->id;

        $totalAlumnos = $cicloActivoId
            ? Alumno::where('ciclo_escolar_id', $cicloActivoId)
                ->where('estatus', 'activo')
                ->count()
            : 0;

        $totalDocentes = Role::where('name', 'Docente')->exists()
            ? User::role('Docente')->count()
            : 0;

        $promedios = collect();
        if ($cicloActivoId) {
            $promedios = Calificacion::query()
                ->selectRaw('grados.nombre, AVG(calificaciones.calificacion) as promedio')
                ->join('alumnos', 'alumnos.id', '=', 'calificaciones.alumno_id')
                ->join('grados', 'grados.id', '=', 'alumnos.grado_id')
                ->where('alumnos.ciclo_escolar_id', $cicloActivoId)
                ->groupBy('grados.id', 'grados.nombre')
                ->orderBy('grados.nombre')
                ->get();
        }

        return view('dashboard', compact(
            'cicloActivo', 'totalAlumnos', 'totalDocentes', 'promedios'
        ));
    }
}
