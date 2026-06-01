<?php

namespace App\Models;

use Database\Factories\GrupoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
    /** @use HasFactory<GrupoFactory> */
    use HasFactory;

    protected $fillable = [
        'grado_id',
        'ciclo_escolar_id',
        'docente_id',
        'nombre',
    ];

    /** @return BelongsTo<Grado, $this> */
    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    /** @return BelongsTo<CicloEscolar, $this> */
    public function cicloEscolar(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class);
    }

    /** @return BelongsTo<User, $this> */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'docente_id');
    }

    /** @return HasMany<Alumno, $this> */
    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
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
}
