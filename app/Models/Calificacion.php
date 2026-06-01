<?php

namespace App\Models;

use Database\Factories\CalificacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Calificacion extends Model
{
    /** @use HasFactory<CalificacionFactory> */
    use HasFactory;

    protected $table = 'calificaciones';

    protected $fillable = [
        'alumno_id',
        'grupo_id',
        'materia_id',
        'periodo_evaluacion_id',
        'calificacion',
    ];

    protected function casts(): array
    {
        return [
            'calificacion' => 'decimal:2',
        ];
    }

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

    /** @return BelongsTo<Materia, $this> */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    /** @return BelongsTo<PeriodoEvaluacion, $this> */
    public function periodoEvaluacion(): BelongsTo
    {
        return $this->belongsTo(PeriodoEvaluacion::class);
    }

    /** @return HasMany<CalificacionLog, $this> */
    public function logs(): HasMany
    {
        return $this->hasMany(CalificacionLog::class);
    }
}
