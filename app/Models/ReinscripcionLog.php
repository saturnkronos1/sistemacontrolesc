<?php

namespace App\Models;

use Database\Factories\ReinscripcionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReinscripcionLog extends Model
{
    /** @use HasFactory<ReinscripcionLogFactory> */
    use HasFactory;

    protected $table = 'reinscripcion_logs';

    protected $fillable = [
        'alumno_id',
        'from_grado_id',
        'from_grupo_id',
        'from_ciclo_escolar_id',
        'to_grado_id',
        'to_grupo_id',
        'to_ciclo_escolar_id',
        'created_by',
    ];

    /** @return BelongsTo<Alumno, $this> */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /** @return BelongsTo<Grupo, $this> */
    public function fromGrupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'from_grupo_id');
    }

    /** @return BelongsTo<Grupo, $this> */
    public function toGrupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'to_grupo_id');
    }

    /** @return BelongsTo<CicloEscolar, $this> */
    public function fromCicloEscolar(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'from_ciclo_escolar_id');
    }

    /** @return BelongsTo<CicloEscolar, $this> */
    public function toCicloEscolar(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'to_ciclo_escolar_id');
    }

    /** @return BelongsTo<User, $this> */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
