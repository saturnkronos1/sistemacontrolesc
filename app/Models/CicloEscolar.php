<?php

namespace App\Models;

use Database\Factories\CicloEscolarFactory;
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
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
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
}
