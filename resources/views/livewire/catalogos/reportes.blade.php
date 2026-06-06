<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Reportes" />

            {{-- Nav tabs --}}
            <div class="mb-6 flex flex-wrap gap-2 border-b border-zinc-200 pb-2 dark:border-zinc-700">
                <flux:button
                    wire:click="$set('reporte', 'concentrado')"
                    :variant="$reporte === 'concentrado' ? 'primary' : 'ghost'"
                >
                    Concentrado
                </flux:button>
                <flux:button
                    wire:click="$set('reporte', 'kardex')"
                    :variant="$reporte === 'kardex' ? 'primary' : 'ghost'"
                >
                    Kardex
                </flux:button>
                <flux:button
                    wire:click="$set('reporte', 'inasistencias')"
                    :variant="$reporte === 'inasistencias' ? 'primary' : 'ghost'"
                >
                    Inasistencias
                </flux:button>
                <flux:button
                    wire:click="$set('reporte', 'alumnos-por-tutor')"
                    :variant="$reporte === 'alumnos-por-tutor' ? 'primary' : 'ghost'"
                >
                    Alumnos por Tutor
                </flux:button>
            </div>

            {{-- Filters --}}
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Grupo filter for concentrado, kardex, inasistencias --}}
                @if(in_array($reporte, ['concentrado', 'kardex', 'inasistencias']))
                    <flux:select wire:model.live="grupo_id" placeholder="Seleccionar grupo">
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->grado?->nombre }} - {{ $grupo->nombre }} ({{ $grupo->cicloEscolar?->nombre ?? 'Sin ciclo' }})</option>
                        @endforeach
                    </flux:select>
                @endif

                {{-- Periodo filter for concentrado --}}
                @if($reporte === 'concentrado')
                    <flux:select wire:model.live="periodo_id" placeholder="Todos los periodos">
                        <option value="">Todos los periodos</option>
                        @foreach($periodos as $periodo)
                            <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                        @endforeach
                    </flux:select>
                    <div></div>
                @endif

                {{-- Alumno filter for kardex --}}
                @if($reporte === 'kardex')
                    <flux:select wire:model="alumno_id" placeholder="Seleccionar alumno">
                        @foreach($alumnosSelect as $alumno)
                            <option value="{{ $alumno['id'] }}">
                                {{ $alumno['persona']['apellido_paterno'] }} {{ $alumno['persona']['apellido_materno'] }}, {{ $alumno['persona']['nombre'] }} ({{ $alumno['matricula'] }})
                            </option>
                        @endforeach
                    </flux:select>
                    <div></div>
                @endif

                {{-- Date filters for inasistencias --}}
                @if($reporte === 'inasistencias')
                    <flux:input type="date" wire:model.live="fecha_desde" placeholder="Fecha desde" />
                    <flux:input type="date" wire:model.live="fecha_hasta" placeholder="Fecha hasta" />
                @endif

                {{-- Search for alumnos-por-tutor --}}
                @if($reporte === 'alumnos-por-tutor')
                    <flux:input wire:model.live="search" placeholder="Buscar por nombre del tutor..." />
                    <div></div>
                    <div></div>
                @endif
            </div>

            {{-- Consultar button --}}
            @if(in_array($reporte, ['concentrado', 'kardex', 'inasistencias']))
                <div class="mb-6">
                    <flux:button
                        wire:click="cargar"
                        variant="primary"
                        :disabled="($reporte === 'concentrado' && !$grupo_id) || ($reporte === 'kardex' && !$alumno_id) || ($reporte === 'inasistencias' && !$grupo_id)"
                    >
                        Consultar
                    </flux:button>
                </div>
            @endif

            {{-- ─── CONCENTRADO ─── --}}
            @if($cargado && $reporte === 'concentrado')
                <div class="mb-4">
                    <div class="text-sm text-zinc-500 mb-2">
                        <span class="font-medium">Periodo:</span> {{ $periodo_id ? $periodos->firstWhere('id', (int)$periodo_id)?->nombre : 'Todos los periodos' }}
                    </div>
                </div>

                @if(count($alumnos) > 0)
                    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Alumno</th>
                                    @foreach($materias as $materia)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">{{ $materia->nombre }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Promedio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                                @foreach($alumnos as $alumno)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                                            {{ $alumno['persona']['apellido_paterno'] }} {{ $alumno['persona']['apellido_materno'] }}, {{ $alumno['persona']['nombre'] }}
                                        </td>
                                        @foreach($materias as $materia)
                                            <td class="px-4 py-3 text-center text-sm font-mono">
                                                @php
                                                    $val = $calificaciones[$alumno['id']][$materia->id] ?? [];
                                                    $notas = collect($periodos->toArray())->map(fn($p) => $val[$p['id']] ?? null)->filter();
                                                    $prom = $notas->count() > 0 ? round($notas->avg(), 1) : null;
                                                @endphp
                                                @if($prom !== null)
                                                    <span class="{{ $prom >= 6 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                        {{ number_format($prom, 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-4 py-3 text-center text-sm font-mono font-semibold">
                                            @if(($promedios[$alumno['id']] ?? null) !== null)
                                                <span class="{{ $promedios[$alumno['id']] >= 6 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                    {{ number_format($promedios[$alumno['id']], 1) }}
                                                </span>
                                            @else
                                                <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900">
                        No hay alumnos activos en este grupo.
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:button wire:click="descargarPDF" variant="primary" icon="arrow-down-tray">
                        Descargar PDF
                    </flux:button>
                </div>
            @endif

            {{-- ─── KARDEX ─── --}}
            @if($cargado && $reporte === 'kardex')
                <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <h2 class="text-lg font-semibold mb-3">Datos del Alumno</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div>
                            <span class="text-zinc-500">Matrícula:</span>
                            <span class="ml-1 font-mono font-medium">{{ $alumnoData['matricula'] ?? '—' }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-zinc-500">Nombre:</span>
                            <span class="ml-1 font-medium">{{ $alumnoData['persona']['apellido_paterno'] ?? '' }} {{ $alumnoData['persona']['apellido_materno'] ?? '' }}, {{ $alumnoData['persona']['nombre'] ?? '' }}</span>
                        </div>
                    </div>
                </div>

                @forelse($kardexData as $cicloItem)
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-md font-semibold text-blue-600 dark:text-blue-400">
                                {{ $cicloItem['ciclo']?->nombre ?? 'Ciclo desconocido' }}
                            </h3>
                            <span class="text-sm text-zinc-500">{{ $cicloItem['grado'] }} - {{ $cicloItem['grupo'] }}</span>
                        </div>

                        <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                                <thead class="bg-zinc-50 dark:bg-zinc-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Materia</th>
                                        @foreach($cicloItem['periodos'] as $periodo)
                                            <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">{{ $periodo->nombre }}</th>
                                        @endforeach
                                        <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Promedio</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                                    @foreach($cicloItem['materias'] as $materia)
                                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                            <td class="px-4 py-3 text-sm font-medium">{{ $materia->nombre }}</td>
                                            @foreach($cicloItem['periodos'] as $periodo)
                                                <td class="px-4 py-3 text-center text-sm font-mono">
                                                    @php $nota = $cicloItem['calificaciones'][$materia->id][$periodo->id] ?? null; @endphp
                                                    @if($nota !== null)
                                                        <span class="{{ $nota >= 6 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                            {{ number_format((float)$nota, 1) }}
                                                        </span>
                                                    @else
                                                        <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td class="px-4 py-3 text-center text-sm font-mono font-semibold">
                                                @php
                                                    $vals = collect($cicloItem['periodos']->toArray())->map(fn($p) => $cicloItem['calificaciones'][$materia->id][$p['id']] ?? null)->filter();
                                                    $prom = $vals->count() > 0 ? round($vals->avg(), 1) : null;
                                                @endphp
                                                @if($prom !== null)
                                                    {{ number_format($prom, 1) }}
                                                @else
                                                    <span class="text-zinc-300 dark:text-zinc-600">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @empty
                    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900">
                        No se encontraron calificaciones para este alumno.
                    </div>
                @endforelse

                <div class="flex justify-end">
                    <flux:button wire:click="descargarPDF" variant="primary" icon="arrow-down-tray">
                        Descargar PDF
                    </flux:button>
                </div>
            @endif

            {{-- ─── INASISTENCIAS ─── --}}
            @if($cargado && $reporte === 'inasistencias')
                @if(count($inasistenciasData) > 0)
                    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Alumno</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-green-600 uppercase whitespace-nowrap">Asistió</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-red-600 uppercase whitespace-nowrap">Faltas</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-amber-600 uppercase whitespace-nowrap">Retardos</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-blue-600 uppercase whitespace-nowrap">Justificados</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                                @foreach($inasistenciasData as $item)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">{{ $item['persona']['apellido_paterno'] }} {{ $item['persona']['apellido_materno'] }}, {{ $item['persona']['nombre'] }}</td>
                                        <td class="px-4 py-3 text-center text-sm font-mono text-green-600">{{ $item['asistio'] }}</td>
                                        <td class="px-4 py-3 text-center text-sm font-mono text-red-600">{{ $item['falta'] }}</td>
                                        <td class="px-4 py-3 text-center text-sm font-mono text-amber-600">{{ $item['retardo'] }}</td>
                                        <td class="px-4 py-3 text-center text-sm font-mono text-blue-600">{{ $item['justificado'] }}</td>
                                        <td class="px-4 py-3 text-center text-sm font-mono font-semibold">{{ $item['total'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900">
                        No se encontraron asistencias para este grupo.
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:button wire:click="descargarPDF" variant="primary" icon="arrow-down-tray">
                        Descargar PDF
                    </flux:button>
                </div>
            @endif

            {{-- ─── ALUMNOS POR TUTOR ─── --}}
            @if($cargado && $reporte === 'alumnos-por-tutor')
                @if(count($tutoresData) > 0)
                    <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700 mb-6">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Tutor</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Teléfono</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Hijos</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Alumnos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                                @foreach($tutoresData as $tutor)
                                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                        <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">{{ $tutor['nombre_completo'] }}</td>
                                        <td class="px-4 py-3 text-sm">{{ $tutor['telefono'] }}</td>
                                        <td class="px-4 py-3 text-center text-sm font-mono">{{ $tutor['children_count'] }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @foreach($tutor['children'] as $child)
                                                <span class="inline-block mr-2 mb-1 rounded bg-zinc-100 px-2 py-0.5 text-xs dark:bg-zinc-700">
                                                    {{ $child['alumno']?->persona?->apellido_paterno ?? '' }} {{ $child['alumno']?->persona?->apellido_materno ?? '' }}, {{ $child['alumno']?->persona?->nombre ?? '' }}
                                                    @if($child['parentesco'])
                                                        <span class="text-zinc-400">({{ $child['parentesco'] }})</span>
                                                    @endif
                                                </span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="mb-6 rounded-lg border border-zinc-200 bg-white p-6 text-center text-sm text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900">
                        @if($search)
                            No se encontraron tutores con el nombre "{{ $search }}".
                        @else
                            No hay tutores registrados.
                        @endif
                    </div>
                @endif

                <div class="flex justify-end">
                    <flux:button wire:click="descargarPDF" variant="primary" icon="arrow-down-tray">
                        Descargar PDF
                    </flux:button>
                </div>
            @endif
        </flux:main>
    {{-- </x-layouts::app.sidebar> --}}
</div>
