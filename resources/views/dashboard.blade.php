<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <x-page-header title="Dashboard" />

        {{-- Título del ciclo activo --}}
        <h2 class="text-xl font-bold text-primary">
            CICLO ESCOLAR {{ $cicloActivo?->nombre ?? 'Sin ciclo activo' }}
        </h2>

        {{-- Cards de resumen --}}
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            @can('alumnos.listar')
                <a href="{{ route('alumnos.index') }}" wire:navigate
                   class="relative flex flex-col items-center justify-center gap-3 rounded-xl border border-borde bg-white p-8 transition-colors hover:bg-hover">
                    <flux:icon name="users" class="h-12 w-12 text-primary" />
                    <span class="text-lg font-semibold text-texto">Alumnos</span>
                    <span class="text-4xl font-bold text-texto">{{ $totalAlumnos }}</span>
                    <span class="text-sm text-texto-secundario">Inscritos</span>
                </a>
            @endcan

            @can('docentes.listar')
                <a href="{{ route('docentes.index') }}" wire:navigate
                   class="relative flex flex-col items-center justify-center gap-3 rounded-xl border border-borde bg-white p-8 transition-colors hover:bg-hover">
                    <flux:icon name="user-group" class="h-12 w-12 text-primary" />
                    <span class="text-lg font-semibold text-texto">Docentes</span>
                    <span class="text-4xl font-bold text-texto">{{ $totalDocentes }}</span>
                    <span class="text-sm text-texto-secundario">Activos</span>
                </a>
            @endcan

            {{-- Promedios por Grado (carrusel) --}}
            <div class="relative flex flex-col items-center justify-center rounded-xl border border-borde bg-white p-6"
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
                <span class="text-lg font-semibold text-texto mb-4">Promedios por Grado</span>

                <template x-for="(g, i) in promedios" :key="i">
                    <div x-show="current === i"
                         class="flex flex-col items-center gap-1 transition-opacity duration-500"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
                    <span class="text-2xl font-bold text-primary" x-text="g.nombre"></span>
                    <span class="text-5xl font-bold text-texto" x-text="g.promedio ?? 'N/A'"></span>
                    </div>
                </template>

                {{-- Si no hay promedios --}}
                <div x-show="promedios.length === 0" class="text-sm text-texto-secundario py-4">
                    Sin calificaciones registradas
                </div>

                {{-- Dots --}}
                <div x-show="promedios.length > 1" class="flex gap-2 mt-4">
                    <template x-for="(g, i) in promedios" :key="'dot-' + i">
                        <button @click="current = i"
                                class="h-2 rounded-full transition-all duration-300"
                                :class="current === i ? 'w-5 bg-primary' : 'w-2 bg-borde'"></button>
                    </template>
                </div>
            </div>
        </div>

        {{-- Carrusel de imágenes --}}
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-borde">
            <x-carousel :images="['heroes/carrusel2/a1.png', 'heroes/carrusel2/a2.png', 'heroes/carrusel2/a3.png']" />
        </div>
    </div>
</x-layouts::app>
