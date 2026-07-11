<?php

namespace App\Events;

use App\Models\CicloEscolar;
use Illuminate\Foundation\Events\Dispatchable;

class CicloActivado
{
    use Dispatchable;

    /**
     * @param CicloEscolar      $ciclo        The cycle that was just activated
     * @param CicloEscolar|null $cicloAnterior The previously active cycle (now finalizado), if any
     */
    public function __construct(
        public CicloEscolar $ciclo,
        public ?CicloEscolar $cicloAnterior = null,
    ) {}
}
