<?php

namespace App\Models;

use Database\Factories\CicloEscolarFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicloEscolar extends Model
{
    /** @use HasFactory<CicloEscolarFactory> */
    use HasFactory;

    protected $table = 'ciclos_escolares';

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estatus',
        'autocreado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'autocreado' => 'boolean',
        ];
    }

    /** @return HasMany<PeriodoEvaluacion, $this> */
    public function periodosEvaluacion(): HasMany
    {
        return $this->hasMany(PeriodoEvaluacion::class);
    }

    /** @return HasMany<Grupo, $this> */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }

    // ── Scopes ───────────────────────────────────

    /** Scope for active cycles (estatus = 'activo'). */
    public function scopeActivo(Builder $query): void
    {
        $query->where('estatus', 'activo');
    }

    // ── Accessors / Mutators ──────────────────────

    /** Backward-compatible accessor for blade/tests that used $ciclo->activo. */
    public function getActivoAttribute(): bool
    {
        return $this->estatus === 'activo';
    }
}
