<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Calificaciones" />

            @if($esDocente && $grupoUnico)
                {{-- Docente: info del grupo asignado --}}
                <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                    📚 Grupo asignado: <strong>{{ $grupoUnico->grado?->nombre }} - {{ $grupoUnico->nombre }}</strong>
                    ({{ $grupoUnico->cicloEscolar?->nombre ?? 'Sin ciclo' }})
                </div>
                <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
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
            @else
                {{-- Selectores --}}
                <div class="mb-4 grid grid-cols-1 sm:grid-cols-4 gap-4">
                    <flux:select wire:model.live="ciclo_escolar_id" placeholder="Seleccionar ciclo escolar">
                        @foreach($ciclosEscolares as $ciclo)
                            <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                        @endforeach
                    </flux:select>

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
            @endif

            @if($cargado)
                <div class="overflow-x-auto rounded-lg border border-borde">
                    <table class="min-w-full divide-y divide-borde">
                        <thead class="bg-tabla-encabezado">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Nombre</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Calificación <span class="text-zinc-400 font-normal">(6.0 - 10.0)</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-borde bg-white">
                            @forelse($alumnos as $index => $alumno)
                                <tr class="hover:bg-hover">
                                    <td class="px-4 py-3 text-sm text-zinc-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">
                                        {{ $alumno['persona']['apellido_paterno'] }} {{ $alumno['persona']['apellido_materno'] }}, {{ $alumno['persona']['nombre'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="6"
                                            max="10"
                                            wire:model="notas.{{ $alumno['id'] }}"
                                            class="w-24 rounded-md border-borde text-center text-sm font-mono shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="—"
                                            onkeydown="
                                                if (!/[\d.]|Backspace|Tab|Arrow|Delete|Home|End/.test(event.key)) {
                                                    event.preventDefault();
                                                }
                                                if (event.key === '.' && this.value.includes('.')) {
                                                    event.preventDefault();
                                                }
                                                if (this.value.includes('.') && this.value.split('.')[1].length >= 2 && !['Backspace','Delete','Tab','Arrow'].includes(event.key)) {
                                                    event.preventDefault();
                                                }
                                            "
                                            oninput="
                                                if (this.value.startsWith('0') && this.value.length > 1 && this.value[1] !== '.') {
                                                    this.value = this.value.replace(/^0+/, '');
                                                }
                                                if (parseFloat(this.value) > 10) this.value = '10';
                                            "
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
