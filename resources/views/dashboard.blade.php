<x-layouts::app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4">
        <x-page-header title="Dashboard" />

        @if(($sinGrupo ?? false) === true)
            {{-- ─── Docente sin grupo asignado ─── --}}
            <div class="flex flex-1 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-borde bg-white p-12 text-center">
                <flux:icon name="academic-cap" class="h-16 w-16 text-texto-secundario" />
                <h2 class="text-2xl font-bold text-texto">Bienvenido, {{ auth()->user()->name }}</h2>
                <p class="max-w-md text-texto-secundario">
                    No se te ha asignado un grupo todavía. Contactá al administrador del sistema para que te asigne un grupo.
                </p>
                <span class="inline-flex items-center gap-2 rounded-full bg-amber-100 px-4 py-2 text-sm font-medium text-amber-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                    Sin grupo asignado
                </span>
            </div>
        @elseif(isset($grupoAsignado))
            {{-- ─── Docente con grupo asignado ─── --}}
            <div class="grid auto-rows-min gap-4 md:grid-cols-2">
                {{-- Alumnos --}}
                <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-borde bg-white p-8">
                    <flux:icon name="users" class="h-12 w-12 text-primary" />
                    <span class="text-lg font-semibold text-texto">Alumnos en {{ $grupoAsignado->grado?->nombre }} - {{ $grupoAsignado->nombre }}</span>
                    <span class="text-4xl font-bold text-texto">{{ $totalAlumnosGrupo }}</span>
                    <span class="text-sm text-texto-secundario">Inscritos</span>
                </div>

                {{-- Promedio --}}
                <div class="flex flex-col items-center justify-center gap-3 rounded-xl border border-borde bg-white p-8">
                    <flux:icon name="chart-bar" class="h-12 w-12 text-primary" />
                    <span class="text-lg font-semibold text-texto">Promedio General del Grupo</span>
                    <span class="text-4xl font-bold {{ ($promedioGrupo ?? 0) >= 6 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $promedioGrupo ?? 'N/A' }}
                    </span>
                    <span class="text-sm text-texto-secundario">
                        @if($promedioGrupo !== null)
                            {{ $promedioGrupo >= 6 ? '✅ Aprobatorio' : '❌ Reprobatorio' }}
                        @else
                            Sin calificaciones registradas
                        @endif
                    </span>
                </div>
            </div>

            {{-- Carrusel de imágenes --}}
            <div class="relative flex-1 overflow-hidden rounded-xl border border-borde">
                <x-carousel :images="['heroes/carrusel2/a1.png', 'heroes/carrusel2/a2.png', 'heroes/carrusel2/a3.png']" />
            </div>
        @else
            {{-- ─── ADMIN: vista general ─── --}}
            <h2 class="text-xl font-bold text-primary">
                CICLO ESCOLAR {{ $cicloActivo?->nombre ?? 'Sin ciclo activo' }}
            </h2>

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

                    <div x-show="promedios.length === 0" class="text-sm text-texto-secundario py-4">
                        Sin calificaciones registradas
                    </div>

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
            <div class="relative flex-1 overflow-hidden rounded-xl border border-borde">
                <x-carousel :images="['heroes/carrusel2/a1.png', 'heroes/carrusel2/a2.png', 'heroes/carrusel2/a3.png']" />
            </div>
        @endif
    </div>
</x-layouts::app>
