<?php

namespace App\Console\Commands;

use App\Actions\Ciclos\ClonarGrupos;
use App\Actions\Ciclos\ClonarPeriodosEvaluacion;
use App\Models\CicloEscolar;
use App\Support\CicloActivoService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AutocrearCiclo extends Command
{
    protected $signature = 'ciclos:autocrear';

    protected $description = 'Auto-crea el ciclo escolar siguiente el 1 de agosto y lo auto-activa tras 5 días';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        // ── 1. Only run from August onwards ──
        if ($now->month < 8) {
            $this->info('No es temporada de auto-creación (mes < agosto).');

            return self::SUCCESS;
        }

        $nombre = $now->year.'-'.($now->year + 1);

        // ── 2. Create the next cycle if it doesn't exist ──
        $ciclo = CicloEscolar::where('nombre', $nombre)->first();

        if (! $ciclo) {
            $ciclo = CicloEscolar::create([
                'nombre' => $nombre,
                'fecha_inicio' => "{$now->year}-08-01",
                'fecha_fin' => ($now->year + 1).'-07-31',
                'estatus' => 'pendiente',
                'autocreado' => true,
            ]);

            $this->info("Ciclo {$nombre} creado con estatus 'pendiente'.");

            // Clone groups from the most recent active or finalizado cycle
            $lastCycle = CicloEscolar::whereIn('estatus', ['activo', 'finalizado'])
                ->where('id', '!=', $ciclo->id)
                ->latest('fecha_inicio')
                ->first();

            if ($lastCycle) {
                $clonedGrupos = app(ClonarGrupos::class)->execute($lastCycle->id, $ciclo->id);
                $this->info("{$clonedGrupos} grupo(s) clonados desde ciclo {$lastCycle->nombre}.");

                $clonedPeriodos = app(ClonarPeriodosEvaluacion::class)->execute($lastCycle->id, $ciclo->id);
                $this->info("{$clonedPeriodos} período(s) de evaluación clonados desde ciclo {$lastCycle->nombre}.");
            }

            app(CicloActivoService::class)->refresh();
        }

        // ── 3. Auto-activate pending cycles after 5 days ──
        $autoActivados = $this->autoActivarPendientes();

        if ($autoActivados > 0) {
            $this->info("{$autoActivados} ciclo(s) auto-activado(s).");
        }

        return self::SUCCESS;
    }

    /**
     * Auto-activate pending auto-created cycles that are older than 5 days.
     */
    private function autoActivarPendientes(): int
    {
        $count = 0;

        $pendientes = CicloEscolar::where('estatus', 'pendiente')
            ->where('autocreado', true)
            ->where('created_at', '<=', Carbon::now()->subDays(5))
            ->get();

        foreach ($pendientes as $ciclo) {
            // Mark the previously active cycle as finalizado
            CicloEscolar::where('estatus', 'activo')
                ->where('id', '!=', $ciclo->id)
                ->update(['estatus' => 'finalizado']);

            $ciclo->update(['estatus' => 'activo']);

            $this->info("Ciclo {$ciclo->nombre} auto-activado.");

            $count++;
        }

        if ($count > 0) {
            app(CicloActivoService::class)->refresh();
        }

        return $count;
    }
}
