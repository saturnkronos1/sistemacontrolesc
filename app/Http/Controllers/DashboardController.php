<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Grupo;
use App\Models\User;
use App\Support\CicloActivoService;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(CicloActivoService $cicloActivoService)
    {
        $user = auth()->user();

        // ─── Docente: vista personalizada ───
        if ($user->hasRole('Docente')) {
            $grupoAsignado = Grupo::where('docente_id', $user->id)->with('grado', 'cicloEscolar')->first();

            if (! $grupoAsignado) {
                return view('dashboard', [
                    'sinGrupo' => true,
                    'grupoAsignado' => null,
                    'totalAlumnosGrupo' => 0,
                    'promedioGrupo' => null,
                ]);
            }

            $totalAlumnosGrupo = Alumno::where('grupo_id', $grupoAsignado->id)
                ->where('estatus', 'activo')
                ->count();

            $promedioGrupo = Calificacion::where('grupo_id', $grupoAsignado->id)
                ->avg('calificacion');

            $promedioGrupo = $promedioGrupo !== null ? round((float) $promedioGrupo, 1) : null;

            return view('dashboard', [
                'sinGrupo' => false,
                'grupoAsignado' => $grupoAsignado,
                'totalAlumnosGrupo' => $totalAlumnosGrupo,
                'promedioGrupo' => $promedioGrupo,
            ]);
        }

        // ─── Admin: vista general ───
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
