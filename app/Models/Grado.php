<?php

namespace App\Models;

use Database\Factories\GradoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grado extends Model
{
    /** @use HasFactory<GradoFactory> */
    use HasFactory;

    protected $fillable = [
        'nombre',
        'nivel',
    ];

    /** @return HasMany<Materia, $this> */
    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class);
    }

    /** @return HasMany<Alumno, $this> */
    public function alumnos(): HasMany
    {
        return $this->hasMany(Alumno::class);
    }

    /** @return HasMany<Grupo, $this> */
    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class);
    }
}
