<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            @if($vista === 'dashboard')
                {{-- Dashboard: listado de hijos --}}
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold">Mis Hijos</h1>
                </div>

                @if($hijos->isEmpty())
                    <div class="rounded-lg border border-borde bg-white p-8 text-center">
                        <p class="text-zinc-500">No tienes hijos vinculados a tu cuenta de tutor.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($hijos as $familia)
                            @php $alumno = $familia->alumno; @endphp
                            <div class="rounded-lg border border-borde bg-white p-5 ">
                                <div class="flex items-start justify-between mb-3">
                                    <div>
                                        <h3 class="text-lg font-semibold">
                                            {{ $alumno->persona->apellido_paterno }} {{ $alumno->persona->apellido_materno }}, {{ $alumno->persona->nombre }}
                                        </h3>
                                        <p class="text-sm text-zinc-500">
                                            {{ $familia->parentesco }} · Matrícula: {{ $alumno->matricula }}
                                        </p>
                                    </div>
                                    @if($alumno->estatus === 'activo')
                                        <flux:badge color="green" size="sm">Activo</flux:badge>
                                    @else
                                        <flux:badge color="red" size="sm">{{ ucfirst($alumno->estatus) }}</flux:badge>
                                    @endif
                                </div>

                                <div class="text-sm text-zinc-600  mb-4 space-y-1">
                                    <p>Grado: {{ $alumno->grado?->nombre ?? '—' }} · Grupo: {{ $alumno->grupo?->nombre ?? '—' }}</p>
                                    <p>Ciclo: {{ $alumno->cicloEscolar?->nombre ?? '—' }}</p>
                                </div>

                                @if($alumno->estatus === 'activo')
                                    <div class="flex flex-wrap gap-2">
                                        <flux:button wire:click="verCalificaciones({{ $alumno->id }})" icon="chart-bar" size="sm">
                                            Calificaciones
                                        </flux:button>
                                        <flux:button wire:click="verAsistencias({{ $alumno->id }})" icon="calendar-days" size="sm">
                                            Asistencias
                                        </flux:button>
                                        <flux:button wire:click="descargarBoleta({{ $alumno->id }})" icon="arrow-down-tray" size="sm">
                                            Descargar Boleta
                                        </flux:button>
                                    </div>
                                @else
                                    <p class="text-sm text-zinc-400 italic">No disponible — alumno {{ $alumno->estatus }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

            @elseif($vista === 'calificaciones')
                {{-- Calificaciones del alumno --}}
                <div class="mb-4">
                    <flux:button wire:click="volver" icon="arrow-left" size="sm">Volver</flux:button>
                </div>

                <div class="mb-6 rounded-lg border border-borde bg-white p-4 ">
                    <h2 class="text-lg font-semibold mb-1">{{ $alumnoData['persona']['apellido_paterno'] ?? '' }} {{ $alumnoData['persona']['apellido_materno'] ?? '' }}, {{ $alumnoData['persona']['nombre'] ?? '' }}</h2>
                    <p class="text-sm text-zinc-500">
                        {{ $alumnoData['matricula'] ?? '' }} · {{ $alumnoData['grado']['nombre'] ?? '' }} - {{ $alumnoData['grupo']['nombre'] ?? '' }}
                    </p>
                </div>

                {{-- Filtro periodo --}}
                <div class="mb-4">
                    <flux:select wire:model.live="periodo_id" placeholder="Todos los periodos" class="max-w-xs">
                        <option value="">Todos los periodos</option>
                        @foreach($periodos as $periodo)
                            <option value="{{ $periodo->id }}">{{ $periodo->nombre }}</option>
                        @endforeach
                    </flux:select>
                </div>

                {{-- Tabla de calificaciones --}}
                @if($periodos->isNotEmpty() && $materias->isNotEmpty())
                    <div class="overflow-x-auto rounded-lg border border-borde mb-6">
                        <table class="min-w-full divide-y divide-borde">
                            <thead class="bg-tabla-encabezado">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Campo Formativo</th>
                                    @foreach($periodos as $periodo)
                                        <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">{{ $periodo->nombre }}</th>
                                    @endforeach
                                    <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Promedio</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-borde bg-white">
                                @foreach($materias as $materia)
                                    <tr class="hover:bg-hover">
                                        <td class="px-4 py-3 text-sm font-medium">{{ $materia->nombre }}</td>
                                        @foreach($periodos as $periodo)
                                            <td class="px-4 py-3 text-center text-sm font-mono">
                                                @php $nota = $calificaciones[$materia->id][$periodo->id] ?? null; @endphp
                                                @if($nota !== null)
                                                    <span class="{{ $nota >= 6 ? 'text-green-600 ' : 'text-red-600' }}">
                                                        {{ number_format($nota, 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-zinc-300">—</span>
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
                                                <span class="text-zinc-300">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-tabla-encabezado font-semibold">
                                <tr>
                                    <td class="px-4 py-3 text-sm">Promedio General</td>
                                    @foreach($periodos as $periodo)
                                        <td class="px-4 py-3 text-center text-sm font-mono">
                                            @if(($promedios[$periodo->id] ?? null) !== null)
                                                <span class="{{ $promedios[$periodo->id] >= 6 ? 'text-green-600 ' : 'text-red-600' }}">
                                                    {{ number_format($promedios[$periodo->id], 1) }}
                                                </span>
                                            @else
                                                <span class="text-zinc-300">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center text-sm font-mono">
                                        @php
                                            $promGeneral = collect($promedios)->filter()->avg();
                                        @endphp
                                        @if($promGeneral)
                                            <span class="{{ $promGeneral >= 6 ? 'text-green-600 ' : 'text-red-600' }}">
                                                {{ number_format($promGeneral, 1) }}
                                            </span>
                                        @else
                                            <span class="text-zinc-300">—</span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Observaciones --}}
                    @if(count($observaciones))
                        <div class="mb-6 rounded-lg border border-borde bg-white p-4 ">
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

                    <div class="flex justify-end">
                        <flux:button wire:click="descargarBoleta" variant="primary" icon="arrow-down-tray">
                            Descargar Boleta PDF
                        </flux:button>
                    </div>
                @else
                    <div class="rounded-lg border border-borde bg-white p-8 text-center">
                        <p class="text-zinc-500">No hay calificaciones registradas para este alumno.</p>
                    </div>
                @endif

            @elseif($vista === 'asistencias')
                {{-- Asistencias del alumno --}}
                <div class="mb-4">
                    <flux:button wire:click="volver" icon="arrow-left" size="sm">Volver</flux:button>
                </div>

                <div class="mb-6 rounded-lg border border-borde bg-white p-4 ">
                    <h2 class="text-lg font-semibold mb-1">{{ $alumnoData['persona']['apellido_paterno'] ?? '' }} {{ $alumnoData['persona']['apellido_materno'] ?? '' }}, {{ $alumnoData['persona']['nombre'] ?? '' }}</h2>
                    <p class="text-sm text-zinc-500">
                        {{ $alumnoData['matricula'] ?? '' }} · {{ $alumnoData['grado']['nombre'] ?? '' }} - {{ $alumnoData['grupo']['nombre'] ?? '' }}
                    </p>
                </div>

                @if($asistencias->isNotEmpty())
                    <div class="overflow-x-auto rounded-lg border border-borde">
                        <table class="min-w-full divide-y divide-borde">
                            <thead class="bg-tabla-encabezado">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Fecha</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Estatus</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Justificante</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-borde bg-white">
                                @foreach($asistencias as $asistencia)
                                    <tr class="hover:bg-hover">
                                        <td class="px-4 py-3 text-sm">{{ $asistencia->fecha->format('d/m/Y') }}</td>
                                        <td class="px-4 py-3 text-sm">
                                            @switch($asistencia->estatus)
                                                @case('asistio')
                                                    <flux:badge color="green" size="sm">Asistió</flux:badge>
                                                    @break
                                                @case('falta')
                                                    <flux:badge color="red" size="sm">Falta</flux:badge>
                                                    @break
                                                @case('retardo')
                                                    <flux:badge color="amber" size="sm">Retardo</flux:badge>
                                                    @break
                                                @case('justificado')
                                                    <flux:badge color="blue" size="sm">Justificado</flux:badge>
                                                    @break
                                            @endswitch
                                        </td>
                                        <td class="px-4 py-3 text-sm text-zinc-600 ">
                                            {{ $asistencia->justificante?->motivo ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Resumen --}}
                    @php
                        $total = $asistencias->count();
                        $asistio = $asistencias->where('estatus', 'asistio')->count();
                        $faltas = $asistencias->where('estatus', 'falta')->count();
                        $retardos = $asistencias->where('estatus', 'retardo')->count();
                        $justificados = $asistencias->where('estatus', 'justificado')->count();
                    @endphp
                    <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div class="rounded-lg border border-borde bg-white p-3 text-center">
                            <p class="text-2xl font-bold text-green-600">{{ $asistio }}</p>
                            <p class="text-xs text-zinc-500">Asistencias</p>
                        </div>
                        <div class="rounded-lg border border-borde bg-white p-3 text-center">
                            <p class="text-2xl font-bold text-red-600">{{ $faltas }}</p>
                            <p class="text-xs text-zinc-500">Faltas</p>
                        </div>
                        <div class="rounded-lg border border-borde bg-white p-3 text-center">
                            <p class="text-2xl font-bold text-amber-600">{{ $retardos }}</p>
                            <p class="text-xs text-zinc-500">Retardos</p>
                        </div>
                        <div class="rounded-lg border border-borde bg-white p-3 text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ $justificados }}</p>
                            <p class="text-xs text-zinc-500">Justificados</p>
                        </div>
                    </div>
                @else
                    <div class="rounded-lg border border-borde bg-white p-8 text-center">
                        <p class="text-zinc-500">No hay registros de asistencia para este alumno.</p>
                    </div>
                @endif
            @endif
        </flux:main>
    {{-- </x-layouts::app.sidebar> --}}
</div>
