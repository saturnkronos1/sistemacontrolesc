<?php

namespace App\Models;

use Database\Factories\AlumnoFamiliaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumnoFamilia extends Model
{
    /** @use HasFactory<AlumnoFamiliaFactory> */
    use HasFactory;

    protected $table = 'alumno_familia';

    protected $fillable = [
        'alumno_id',
        'persona_id',
        'parentesco',
    ];

    /** @return BelongsTo<Alumno, $this> */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }
}
