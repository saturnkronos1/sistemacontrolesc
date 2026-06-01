<?php

namespace App\Models;

use Database\Factories\PeriodoEvaluacionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PeriodoEvaluacion extends Model
{
    /** @use HasFactory<PeriodoEvaluacionFactory> */
    use HasFactory;

    protected $table = 'periodos_evaluacion';

    protected $fillable = [
        'ciclo_escolar_id',
        'nombre',
        'orden',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'orden' => 'integer',
        ];
    }

    /** @return BelongsTo<CicloEscolar, $this> */
    public function cicloEscolar(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class);
    }

    /** @return HasMany<Calificacion, $this> */
    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }

    /** @return HasMany<BoletaObservacion, $this> */
    public function boletaObservaciones(): HasMany
    {
        return $this->hasMany(BoletaObservacion::class);
    }
}
