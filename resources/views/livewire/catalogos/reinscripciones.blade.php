<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Reinscripciones" />
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold lg:hidden">Reinscripciones</h1>
            </div>

            {{-- Selectores --}}
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:select wire:model.live="source_grupo_id" placeholder="Grupo de origen">
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}">
                            {{ $grupo->grado?->nombre }} - {{ $grupo->nombre }} ({{ $grupo->cicloEscolar?->nombre ?? 'Sin ciclo' }})
                        </option>
                    @endforeach
                </flux:select>

                <div class="flex items-end">
                    <flux:button wire:click="cargarAlumnos" variant="primary" :disabled="!$source_grupo_id">
                        Cargar alumnos
                    </flux:button>
                </div>
            </div>

            @if($cargado)
                <div class="mb-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-sm font-semibold text-zinc-500 uppercase">
                            Alumnos activos en el grupo de origen
                            <span class="ml-2 text-xs font-normal text-zinc-400">({{ count($alumnos) }} alumno(s))</span>
                        </h2>
                        @if(count($alumnos))
                            <flux:button wire:click="toggleAll" size="sm" inset="top bottom">
                                {{ count($selected) === count($alumnos) ? 'Deseleccionar todos' : 'Seleccionar todos' }}
                            </flux:button>
                        @endif
                    </div>

                    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap w-10">
                                        <input type="checkbox" wire:click="toggleAll" {{ count($selected) === count($alumnos) && count($alumnos) > 0 ? 'checked' : '' }} class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Matrícula</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Grado actual</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                                @forelse($alumnos as $alumno)
                                    @php $alumnoId = $alumno['id']; @endphp
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-4 py-3 text-center">
                                            <input type="checkbox" wire:model="selected.{{ $alumnoId }}" value="{{ $alumnoId }}" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                                        </td>
                                        <td class="px-4 py-3 text-sm font-mono">{{ $alumno['matricula'] }}</td>
                                        <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                                            {{ $alumno['persona']['apellido_paterno'] }} {{ $alumno['persona']['apellido_materno'] }}, {{ $alumno['persona']['nombre'] }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">{{ $alumno['grado']['nombre'] ?? '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-12 text-center text-zinc-500">
                                            No hay alumnos activos en este grupo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Target selection --}}
                <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-sm font-semibold text-zinc-500 uppercase mb-3">Grupo de destino</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:select wire:model.live="target_grupo_id" placeholder="Seleccionar grupo destino">
                            @foreach($grupos as $grupo)
                                <option value="{{ $grupo->id }}">
                                    {{ $grupo->grado?->nombre }} - {{ $grupo->nombre }} ({{ $grupo->cicloEscolar?->nombre ?? 'Sin ciclo' }})
                                </option>
                            @endforeach
                        </flux:select>

                        <div class="flex items-end">
                            <flux:button
                                wire:click="reinscribir"
                                variant="primary"
                                :disabled="!$target_grupo_id || count($selected) === 0"
                                wire:confirm="¿Reinscribir {{ count($selected) }} alumno(s) al grupo seleccionado?"
                            >
                                Reinscribir seleccionados
                            </flux:button>
                        </div>
                    </div>
                </div>
            @endif
        </flux:main>
    {{-- </x-layouts::app.sidebar> --}}
</div>
