<?php

namespace App\Support;

use App\Models\CicloEscolar;
use Illuminate\Support\Collection;
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
     * Get all active school cycles, ordered by name.
     *
     * Used in Livewire render() for filter selects across multiple components.
     * Not cached since the list is cheap to query and could change between renders.
     *
     * @return Collection<int, CicloEscolar>
     */
    public function getAll(): Collection
    {
        return CicloEscolar::activo()->orderBy('nombre')->get();
    }

    /**
     * Invalidate the cached active cycle so the next call re-queries.
     */
    public function refresh(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
