<?php

namespace App\Models;

use Database\Factories\BoletaObservacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoletaObservacion extends Model
{
    /** @use HasFactory<BoletaObservacionFactory> */
    use HasFactory;

    protected $table = 'boleta_observaciones';

    protected $fillable = [
        'alumno_id',
        'grupo_id',
        'periodo_evaluacion_id',
        'observacion',
    ];

    /** @return BelongsTo<Alumno, $this> */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /** @return BelongsTo<Grupo, $this> */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    /** @return BelongsTo<PeriodoEvaluacion, $this> */
    public function periodoEvaluacion(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class);
    }
}
