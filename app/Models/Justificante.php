<?php

namespace App\Models;

use Database\Factories\JustificanteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Justificante extends Model
{
    /** @use HasFactory<JustificanteFactory> */
    use HasFactory;

    protected $fillable = [
        'asistencia_id',
        'archivo_path',
        'motivo',
    ];

    /** @return BelongsTo<Asistencia, $this> */
    public function asistencia(): BelongsTo
    {
        return $this->belongsTo(Asistencia::class);
    }
}
