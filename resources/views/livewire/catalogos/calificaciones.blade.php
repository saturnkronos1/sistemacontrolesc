<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Calificaciones" />
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold lg:hidden">Calificaciones</h1>
            </div>

            {{-- Selectores --}}
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <flux:select wire:model.live="grupo_id" placeholder="Seleccionar grupo">
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->grado?->nombre }} - {{ $grupo->nombre }} ({{ $grupo->cicloEscolar?->nombre ?? 'Sin ciclo' }})</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="materia_id" placeholder="Seleccionar materia">
                    @foreach($materias as $materia)
                        <option value="{{ $materia->id }}">{{ $materia->nombre }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="periodo_id" placeholder="Seleccionar periodo">
                    @foreach($periodos as $periodo)
                        <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="mb-6">
                <flux:button wire:click="cargarAlumnos" variant="primary" :disabled="!$grupo_id || !$materia_id || !$periodo_id">
                    Cargar alumnos
                </flux:button>
            </div>

            @if($cargado)
                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Nombre</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Calificación</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                            @forelse($alumnos as $index => $alumno)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-3 text-sm text-zinc-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">
                                        {{ $alumno['persona']['apellido_paterno'] }} {{ $alumno['persona']['apellido_materno'] }}, {{ $alumno['persona']['nombre'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input
                                            type="number"
                                            step="0.5"
                                            min="0"
                                            max="10"
                                            wire:model="notas.{{ $alumno['id'] }}"
                                            class="w-24 rounded-md border-zinc-300 text-center text-sm font-mono shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                                            placeholder="—"
                                        />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-12 text-center text-zinc-500">
                                        No hay alumnos activos en este grupo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex justify-end">
                    <flux:button wire:click="guardar" variant="primary">
                        Guardar calificaciones
                    </flux:button>
                </div>
            @endif
        </flux:main>
    {{-- </x-layouts::app.sidebar> --}}
</div>
