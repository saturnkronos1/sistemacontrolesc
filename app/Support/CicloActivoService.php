<?php

namespace App\Support;

use App\Models\CicloEscolar;
use Illuminate\Support\Facades\Cache;

class CicloActivoService
{
    /**
     * Cache key for the active school cycle.
     */
    private const CACHE_KEY = 'ciclo_activo';

    /**
     * Cache TTL in seconds (5 minutes).
     */
    private const CACHE_TTL = 300;

    /**
     * Get the active school cycle model, or null if none is set.
     *
     * We cache only the ID to avoid serialization issues (__PHP_Incomplete_Class)
     * when the model definition changes between cache writes and reads.
     */
    public function get(): ?CicloEscolar
    {
        $id = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return CicloEscolar::activo()->value('id');
        });

        return $id !== null ? CicloEscolar::find($id) : null;
    }

    /**
     * Get the ID of the active school cycle, or null.
     */
    public function getId(): ?int
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return CicloEscolar::activo()->value('id');
        });
    }

    /**
     * Invalidate the cached active cycle so the next call re-queries.
     */
    public function refresh(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
