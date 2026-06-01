<div>
    <x-layouts::app.sidebar>
        <flux:main>
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Boleta de Calificaciones</h1>
            </div>

            {{-- Selectores --}}
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <flux:select wire:model.live="grupo_id" placeholder="Seleccionar grupo">
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->grado?->nombre }} - {{ $grupo->nombre }} ({{ $grupo->cicloEscolar?->nombre ?? 'Sin ciclo' }})</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="alumno_id" placeholder="Seleccionar alumno">
                    @foreach($alumnos as $alumno)
                        <option value="{{ $alumno['id'] }}">
                            {{ $alumno['persona']['apellido_paterno'] }} {{ $alumno['persona']['apellido_materno'] }}, {{ $alumno['persona']['nombre'] }} ({{ $alumno['matricula'] }})
                        </option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="periodo_id" placeholder="Todos los periodos">
                    <option value="">Todos los periodos</option>
                    @foreach($periodos ?? [] as $periodo)
                        <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="mb-6">
                <flux:button wire:click="cargar" variant="primary" :disabled="!$alumno_id">
                    Consultar boleta
                </flux:button>
            </div>

            @if($cargado)
                {{-- Alumno info --}}
                <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-lg font-semibold mb-3">Datos del Alumno</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div>
                            <span class="text-zinc-500">Matrícula:</span>
                            <span class="ml-1 font-mono font-medium">{{ $alumnoData['matricula'] ?? '—' }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-zinc-500">Nombre:</span>
                            <span class="ml-1 font-medium">
                                {{ $alumnoData['persona']['apellido_paterno'] ?? '' }} {{ $alumnoData['persona']['apellido_materno'] ?? '' }}, {{ $alumnoData['persona']['nombre'] ?? '' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-zinc-500">Grado:</span>
                            <span class="ml-1 font-medium">{{ $alumnoData['grado']['nombre'] ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500">Grupo:</span>
                            <span class="ml-1 font-medium">{{ $alumnoData['grupo']['nombre'] ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="text-zinc-500">Ciclo Escolar:</span>
                            <span class="ml-1 font-medium">{{ $alumnoData['grupo']['ciclo_escolar']['nombre'] ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Calificaciones table --}}
                <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Materia</th>
                                @foreach($periodos as $periodo)
                                    <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">{{ $periodo->nombre }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Promedio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                            @foreach($materias as $materia)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="px-4 py-3 text-sm font-medium">{{ $materia->nombre }}</td>
                                    @foreach($periodos as $periodo)
                                        <td class="px-4 py-3 text-center text-sm font-mono">
                                            @php $nota = $calificaciones[$materia->id][$periodo->id] ?? null; @endphp
                                            @if($nota !== null)
                                                <span class="{{ $nota >= 6 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ number_format($nota, 1) }}
                                                </span>
                                            @else
                                                <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center text-sm font-mono font-semibold">
                                        @php
                                            $notasMateria = collect($periodos->toArray())->map(fn($p) => $calificaciones[$materia->id][$p['id']] ?? null)->filter();
                                            $promMateria = $notasMateria->count() > 0 ? round($notasMateria->avg(), 1) : null;
                                        @endphp
                                        @if($promMateria !== null)
                                            {{ number_format($promMateria, 1) }}
                                        @else
                                            <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-800 font-semibold">
                            <tr>
                                <td class="px-4 py-3 text-sm">Promedio General</td>
                                @foreach($periodos as $periodo)
                                    <td class="px-4 py-3 text-center text-sm font-mono">
                                        @if(($promedios[$periodo->id] ?? null) !== null)
                                            <span class="{{ $promedios[$periodo->id] >= 6 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ number_format($promedios[$periodo->id], 1) }}
                                            </span>
                                        @else
                                            <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="px-4 py-3 text-center text-sm font-mono">
                                    @php
                                        $promGeneral = collect($promedios)->filter()->avg();
                                    @endphp
                                    @if($promGeneral)
                                        <span class="{{ $promGeneral >= 6 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ number_format($promGeneral, 1) }}
                                        </span>
                                    @else
                                        <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Observaciones --}}
                @if(count($observaciones))
                    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                        <h3 class="text-sm font-semibold text-zinc-500 uppercase mb-3">Observaciones</h3>
                        @foreach($observaciones as $obs)
                            <div class="mb-2 last:mb-0">
                                @if($obs['periodo_evaluacion'] ?? null)
                                    <span class="text-xs font-medium text-zinc-400">{{ $obs['periodo_evaluacion']['nombre'] }}:</span>
                                @endif
                                <p class="text-sm">{{ $obs['observacion'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Download button --}}
                <div class="flex justify-end">
                    <flux:button wire:click="descargarPDF" variant="primary" icon="arrow-down-tray">
                        Descargar PDF
                    </flux:button>
                </div>
            @endif
        </flux:main>
    </x-layouts::app.sidebar>
</div>
