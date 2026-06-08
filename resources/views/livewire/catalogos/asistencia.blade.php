<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Asistencia" />
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold lg:hidden">Asistencia</h1>
            </div>

            {{-- Selectores --}}
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <flux:select wire:model.live="grupo_id" placeholder="Seleccionar grupo">
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->grado?->nombre }} - {{ $grupo->nombre }} ({{ $grupo->cicloEscolar?->nombre ?? 'Sin ciclo' }})</option>
                    @endforeach
                </flux:select>

                <flux:input type="date" wire:model.live="fecha" label="Fecha" />
            </div>

            <div class="mb-6">
                <flux:button wire:click="cargarAlumnos" variant="primary" :disabled="!$grupo_id || !$fecha">
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
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Estatus</th>
                                <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Motivo (justificante)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                            @forelse($alumnos as $index => $alumno)
                                @php $alumnoId = $alumno['id']; @endphp
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-3 text-sm text-zinc-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                                        {{ $alumno['persona']['apellido_paterno'] }} {{ $alumno['persona']['apellido_materno'] }}, {{ $alumno['persona']['nombre'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <flux:select wire:model="estatusList.{{ $alumnoId }}" class="min-w-[140px]">
                                            <option value="asistio">✅ Asistió</option>
                                            <option value="falta">❌ Falta</option>
                                            <option value="retardo">⏰ Retardo</option>
                                            <option value="justificado">📄 Justificado</option>
                                        </flux:select>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if(($estatusList[$alumnoId] ?? '') === 'justificado')
                                            <flux:textarea wire:model="motivos.{{ $alumnoId }}" placeholder="Motivo del justificante..." rows="2" class="min-w-[200px]" />
                                        @else
                                            <span class="text-sm text-zinc-400">—</span>
                                        @endif
                                    </td>
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

                <div class="mt-4 flex items-center justify-between">
                    <div class="text-sm text-zinc-500">
                        <strong>{{ count($alumnos) }}</strong> alumno(s) — Fecha: <strong>{{ \Illuminate\Support\Carbon::parse($fecha)->format('d/m/Y') }}</strong>
                    </div>
                    <flux:button wire:click="guardar" variant="primary">
                        Guardar asistencias
                    </flux:button>
                </div>
            @endif
        </flux:main>
    {{-- </x-layouts::app.sidebar> --}}
</div>
