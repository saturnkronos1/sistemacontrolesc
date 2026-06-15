<div>
    <flux:main>
        <x-page-header title="Reinscripciones" />

        {{-- Origen --}}
        <div class="mb-4 rounded-lg border border-borde bg-white p-4">
            <h2 class="mb-3 text-sm font-semibold text-zinc-500 uppercase">Grupo de origen</h2>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <flux:select wire:model.live="ciclo_escolar_id" placeholder="Seleccionar ciclo escolar">
                    @foreach($ciclosEscolares as $ciclo)
                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }} ({{ $ciclo->estatus }})</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="source_grupo_id" placeholder="Seleccionar grupo">
                    @foreach($sourceGrupos as $grupo)
                        <option value="{{ $grupo->id }}">
                            {{ $grupo->grado?->nombre }} - {{ $grupo->nombre }}
                        </option>
                    @endforeach
                </flux:select>

                <div class="flex items-end">
                    <flux:button wire:click="cargarAlumnos" variant="primary" :disabled="!$source_grupo_id">
                        Cargar alumnos
                    </flux:button>
                </div>
            </div>
        </div>

        @if($cargado)
            {{-- Tabla de alumnos --}}
            <div class="mb-4 rounded-lg border border-borde bg-white p-4">
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

                <div class="overflow-x-auto rounded-lg border border-borde">
                    <table class="min-w-full divide-y divide-borde">
                        <thead class="bg-tabla-encabezado">
                            <tr>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap w-10">
                                    <input type="checkbox" wire:click="toggleAll" {{ count($selected) === count($alumnos) && count($alumnos) > 0 ? 'checked' : '' }} class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap hidden sm:table-cell">Matrícula</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Grado actual</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-borde bg-white">
                            @forelse($alumnos as $alumno)
                                @php $alumnoId = $alumno['id']; @endphp
                                <tr class="hover:bg-hover">
                                    <td class="px-4 py-3 text-center">
                                        <input type="checkbox" wire:model="selected.{{ $alumnoId }}" value="{{ $alumnoId }}" class="rounded border-zinc-300 text-indigo-600 focus:ring-indigo-500" />
                                    </td>
                                    <td class="px-4 py-3 text-sm font-mono hidden sm:table-cell">{{ $alumno['matricula'] }}</td>
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

            {{-- Destino --}}
            <div class="mb-6 rounded-lg border border-borde bg-white p-4">
                <h2 class="text-sm font-semibold text-zinc-500 uppercase mb-3">Grupo de destino</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:select wire:model.live="target_ciclo_escolar_id" placeholder="Seleccionar ciclo destino">
                        @foreach($ciclosEscolares as $ciclo)
                            <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }} ({{ $ciclo->estatus }})</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model.live="target_grupo_id" placeholder="Seleccionar grupo destino">
                        @foreach($targetGrupos as $grupo)
                            <option value="{{ $grupo->id }}">
                                {{ $grupo->grado?->nombre }} - {{ $grupo->nombre }}
                            </option>
                        @endforeach
                    </flux:select>
                </div>

                @if($target_ciclo_escolar_id && $target_ciclo_escolar_id == $ciclo_escolar_id)
                    <div class="mt-3 rounded-lg bg-amber-50 border border-amber-200 px-4 py-2 text-sm text-amber-700">
                        ⚠️ El ciclo de destino es el mismo que el de origen. Una reinscripción generalmente mueve alumnos al <strong>siguiente ciclo escolar</strong>.
                    </div>
                @endif

                <div class="mt-4 flex justify-end">
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
        @endif
    </flux:main>
</div>
