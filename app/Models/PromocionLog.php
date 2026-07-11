<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromocionLog extends Model
{
    protected $fillable = [
        'alumno_id',
        'ciclo_origen_id',
        'ciclo_destino_id',
        'grado_origen_id',
        'grado_destino_id',
        'grupo_origen_id',
        'grupo_destino_id',
        'tipo',
    ];

    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    public function cicloOrigen(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_origen_id');
    }

    public function cicloDestino(): BelongsTo
    {
        return $this->belongsTo(CicloEscolar::class, 'ciclo_destino_id');
    }

    public function gradoOrigen(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_origen_id');
    }

    public function gradoDestino(): BelongsTo
    {
        return $this->belongsTo(Grado::class, 'grado_destino_id');
    }

    public function grupoOrigen(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_origen_id');
    }

    public function grupoDestino(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'grupo_destino_id');
    }
}
