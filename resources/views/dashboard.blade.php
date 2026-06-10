<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <x-page-header title="Dashboard" />

        {{-- Título del ciclo activo --}}
        <h2 class="text-xl font-bold text-[#185c7a]">
            CICLO ESCOLAR {{ $cicloActivo?->nombre ?? 'Sin ciclo activo' }}
        </h2>

        {{-- Cards de resumen --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            @can('alumnos.listar')
                <a href="{{ route('alumnos.index') }}" wire:navigate
                   class="relative flex flex-col items-center justify-center gap-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-8 transition-colors hover:bg-neutral-50 dark:hover:bg-zinc-700/50">
                    <flux:icon name="users" class="h-12 w-12 text-[#185c7a]" />
                    <span class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">Alumnos</span>
                    <span class="text-4xl font-bold text-zinc-900 dark:text-white">{{ $totalAlumnos }}</span>
                    <span class="text-sm text-zinc-500">Inscritos</span>
                </a>
            @endcan

            @can('docentes.listar')
                <a href="{{ route('docentes.index') }}" wire:navigate
                   class="relative flex flex-col items-center justify-center gap-3 rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-8 transition-colors hover:bg-neutral-50 dark:hover:bg-zinc-700/50">
                    <flux:icon name="user-group" class="h-12 w-12 text-[#185c7a]" />
                    <span class="text-lg font-semibold text-zinc-700 dark:text-zinc-300">Docentes</span>
                    <span class="text-4xl font-bold text-zinc-900 dark:text-white">{{ $totalDocentes }}</span>
                    <span class="text-sm text-zinc-500">Activos</span>
                </a>
            @endcan

            {{-- Promedios por Grado (carrusel) --}}
            <div class="relative flex flex-col items-center justify-center rounded-xl border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-zinc-800 p-6"
                 x-data="{
                     current: 0,
                     interval: null,
                     promedios: @js($promedios),
                     get total() { return this.promedios.length },
                     next() { this.current = (this.current + 1) % this.total },
                     start() { this.interval = setInterval(() => this.next(), 4000) },
                     stop() { clearInterval(this.interval); this.interval = null },
                 }"
                 x-init="start()"
                 @mouseenter="stop()"
                 @mouseleave="start()">
                <span class="text-lg font-semibold text-zinc-700 dark:text-zinc-300 mb-4">Promedios por Grado</span>

                <template x-for="(g, i) in promedios" :key="i">
                    <div x-show="current === i"
                         class="flex flex-col items-center gap-1 transition-opacity duration-500"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                        <span class="text-2xl font-bold text-[#185c7a]" x-text="g.nombre"></span>
                        <span class="text-5xl font-bold text-zinc-900 dark:text-white" x-text="g.promedio ?? 'N/A'"></span>
                    </div>
                </template>

                {{-- Si no hay promedios --}}
                <div x-show="promedios.length === 0" class="text-sm text-zinc-500 py-4">
                    Sin calificaciones registradas
                </div>

                {{-- Dots --}}
                <div x-show="promedios.length > 1" class="flex gap-2 mt-4">
                    <template x-for="(g, i) in promedios" :key="'dot-' + i">
                        <button @click="current = i"
                                class="h-2 rounded-full transition-all duration-300"
                                :class="current === i ? 'w-5 bg-[#185c7a]' : 'w-2 bg-zinc-300 dark:bg-zinc-600'"></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Carrusel de imágenes --}}
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700">
            <x-carousel :images="['heroes/carrusel2/a1.png', 'heroes/carrusel2/a2.png', 'heroes/carrusel2/a3.png']" />
        </div>
    </div>
</x-layouts::app>
