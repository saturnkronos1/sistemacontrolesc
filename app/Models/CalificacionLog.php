<?php

namespace App\Models;

use Database\Factories\CalificacionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalificacionLog extends Model
{
    /** @use HasFactory<CalificacionLogFactory> */
    use HasFactory;

    protected $table = 'calificacion_logs';

    protected $fillable = [
        'calificacion_id',
        'user_id',
        'old_calificacion',
        'new_calificacion',
    ];

    protected function casts(): array
    {
        return [
            'old_calificacion' => 'decimal:2',
            'new_calificacion' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Calificacion, $this> */
    public function calificacion(): BelongsTo
    {
        return $this->belongsTo(Calificacion::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
