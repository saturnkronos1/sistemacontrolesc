<?php

namespace App\Http\Middleware;

use App\Models\CicloEscolar;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class CicloActivoMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $cicloActivo = CicloEscolar::where('activo', true)->first();
        } catch (\Exception) {
            $cicloActivo = null;
        }

        View::share('cicloActivo', $cicloActivo);

        if (! $cicloActivo && ! $request->routeIs('catalogos.*')) {
            session()->flash('warning', 'No hay un ciclo escolar activo. Configure uno antes de operar.');
        }

        return $next($request);
    }
}
