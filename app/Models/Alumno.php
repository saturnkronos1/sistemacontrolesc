<?php

namespace App\Models;

use Database\Factories\AlumnoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AlumnoCiclo> $alumnoCiclos
 */

class Alumno extends Model
{
    /** @use HasFactory<AlumnoFactory> */
    use HasFactory;

    protected $fillable = [
        'persona_id',
        'grado_id',
        'grupo_id',
        'ciclo_escolar_id',
        'matricula',
        'estatus',
    ];

    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /** @return BelongsTo<Grado, $this> */
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    /** @return BelongsTo<Grupo, $this> */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class);
    }

    /** @return BelongsTo<CicloEscolar, $this> */
    public function cicloEscolar(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class);
    }

    /** @return HasMany<AlumnoFamilia, $this> */
    public function familiares(): HasMany
    {
        return $this->hasMany(AlumnoFamilia::class);
    }

    /** @return HasMany<Calificacion, $this> */
    public function calificaciones(): HasMany
    {
        return $this->hasMany(Calificacion::class);
    }

    /** @return HasMany<Asistencia, $this> */
    public function asistencias(): HasMany
    {
        return $this->hasMany(Asistencia::class);
    }

    /** @return HasMany<BoletaObservacion, $this> */
    public function boletaObservaciones(): HasMany
    {
        return $this->hasMany(BoletaObservacion::class);
    }

    /** @return HasMany<AlumnoCiclo, $this> */
    public function alumnoCiclos(): HasMany
    {
        return $this->hasMany(AlumnoCiclo::class);
    }

    /**
     * Scope para listar alumnos activos con JOIN a personas y orden nominal.
     *
     * Reemplaza el patrón repetido en 7+ Livewire components:
     *   ->where('alumnos.estatus', 'activo')
     *   ->with('persona')
     *   ->join('personas', ...)
     *     ->orderBy(...)
     *   ->select('alumnos.*')
     */
    public function scopeActivosConPersona($query): void
    {
        $query->where('alumnos.estatus', 'activo')
            ->with('persona')
            ->join('personas', 'alumnos.persona_id', '=', 'personas.id')
            ->orderBy('personas.apellido_paterno')
            ->orderBy('personas.apellido_materno')
            ->orderBy('personas.nombre')
            ->select('alumnos.*');
    }
}
