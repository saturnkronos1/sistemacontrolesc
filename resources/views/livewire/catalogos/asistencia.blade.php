<div>
    <flux:main>
        <x-page-header title="Asistencia" />
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold lg:hidden">Asistencia</h1>
        </div>

        {{-- Tabs solo para roles admin --}}
        @if($esAdmin ?? false)
            <div class="mb-6 flex flex-wrap gap-2 border-b border-borde pb-2">
                <flux:button
                    wire:click="$set('modo', 'pasar-lista')"
                    :variant="$modo === 'pasar-lista' ? 'primary' : 'ghost'"
                >
                    Pasar lista
                </flux:button>
                <flux:button
                    wire:click="$set('modo', 'consulta')"
                    :variant="$modo === 'consulta' ? 'primary' : 'ghost'"
                >
                    Consulta
                </flux:button>
            </div>
        @endif

        @if($modo === 'pasar-lista')
            {{-- ────── Pasar lista ────── --}}
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                @if($esDocente)
                    {{-- Docente: grupo auto-asignado, solo lectura --}}
                    <flux:select wire:model.live="grupo_id" wire:key="pasar-lista-grupo" placeholder="Seleccionar grupo">
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->grado?->nombre }} - {{ $grupo->nombre }} ({{ $grupo->cicloEscolar?->nombre ?? 'Sin ciclo' }})</option>
                        @endforeach
                    </flux:select>
                @else
                    <flux:select wire:model.live="grupo_id" wire:key="pasar-lista-grupo" placeholder="Seleccionar grupo">
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->grado?->nombre }} - {{ $grupo->nombre }} ({{ $grupo->cicloEscolar?->nombre ?? 'Sin ciclo' }})</option>
                        @endforeach
                    </flux:select>
                @endif

                <flux:input type="date" wire:model.live="fecha" label="Fecha" />
            </div>

            @if(!$esDocente)
                <div class="mb-6">
                    <flux:button wire:click="cargarAlumnos" variant="primary" :disabled="!$grupo_id || !$fecha">
                        Cargar alumnos
                    </flux:button>
                </div>
            @endif

            @if($cargado)
                {{-- Alerta modo lectura para docente --}}
                @if($modoLectura)
                    <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                        📋 Asistencia ya registrada para esta fecha. Los datos se muestran en <strong>modo lectura</strong>.
                        Seleccioná otra fecha para registrar una nueva asistencia.
                    </div>
                @endif

                <div class="overflow-x-auto rounded-lg border border-borde">
                    <table class="min-w-full divide-y divide-borde">
                        <thead class="bg-tabla-encabezado">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Nombre</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Estatus</th>
                                @if(!$modoLectura)
                                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Motivo</th>
                                    <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Archivo</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-borde bg-white">
                            @forelse($alumnos as $alumno)
                                @php $alumnoId = $alumno['id']; @endphp
                                <tr class="hover:bg-hover">
                                    <td class="px-4 py-3 text-sm text-zinc-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                                        {{ $alumno['persona']['apellido_paterno'] }} {{ $alumno['persona']['apellido_materno'] }}, {{ $alumno['persona']['nombre'] }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @if($modoLectura)
                                            {{-- Modo lectura: sin botón, solo badge --}}
                                            @switch($estatusList[$alumnoId] ?? '')
                                                @case('asistio')
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-3 py-1.5 text-sm font-medium text-emerald-700">✅ Asistió</span>
                                                    @break
                                                @case('falta')
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-3 py-1.5 text-sm font-medium text-red-700">❌ Falta</span>
                                                    @break
                                                @case('retardo')
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700">⏰ Retardo</span>
                                                    @break
                                                @case('pendiente')
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-700">📄 Justificado</span>
                                                    @break
                                            @endswitch
                                        @else
                                            <button
                                                type="button"
                                                wire:click="cambiarEstatus({{ $alumnoId }})"
                                                class="inline-flex items-center gap-2 rounded-lg px-9 py-4 text-lg font-semibold shadow-sm transition-all duration-150 cursor-pointer border-0
                                                    @switch($estatusList[$alumnoId] ?? 'asistio')
                                                        @case('asistio')
                                                            bg-green-600 hover:bg-green-700 text-black ring-1 ring-green-400
                                                            @break
                                                        @case('falta')
                                                            bg-red-600 hover:bg-red-700 text-black ring-1 ring-red-400
                                                            @break
                                                        @case('retardo')
                                                            bg-yellow-400 hover:bg-yellow-500 text-black ring-1 ring-yellow-300
                                                            @break
                                                        @case('pendiente')
                                                            bg-blue-600 hover:bg-blue-700 text-black ring-1 ring-blue-400
                                                            @break
                                                    @endswitch
                                                "
                                            >
                                                @switch($estatusList[$alumnoId] ?? 'asistio')
                                                    @case('asistio')
                                                        ✅ Asistió
                                                        @break
                                                    @case('falta')
                                                        ❌ Falta
                                                        @break
                                                    @case('retardo')
                                                        ⏰ Retardo
                                                        @break
                                                    @case('pendiente')
                                                        📄 Justificado
                                                        @if($justificanteCompletado[$alumnoId] ?? false)
                                                            ✓
                                                        @endif
                                                        @break
                                                @endswitch
                                            </button>
                                        @endif
                                    </td>
                                    @if(!$modoLectura)
                                        <td class="px-4 py-3">
                                            @if(($estatusList[$alumnoId] ?? '') === 'pendiente')
                                                @if($justificanteCompletado[$alumnoId] ?? false)
                                                    <span class="text-sm text-zinc-600">{{ $justificanteMotivos[$alumnoId] ?? '' }}</span>
                                                @else
                                                    <textarea
                                                        wire:model="justificanteMotivos.{{ $alumnoId }}"
                                                        placeholder="Motivo del justificante..."
                                                        rows="2"
                                                        class="block w-full rounded-lg border border-zinc-200 bg-white px-3 py-2 text-sm shadow-xs focus:border-zinc-400 focus:outline-hidden focus:ring-1 focus:ring-zinc-300"
                                                    ></textarea>
                                                    @error("justificanteMotivos.{$alumnoId}")
                                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                @endif
                                            @else
                                                <span class="text-sm text-zinc-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if(($estatusList[$alumnoId] ?? '') === 'pendiente')
                                                @if($justificanteCompletado[$alumnoId] ?? false)
                                                    <span class="inline-flex items-center gap-1 text-sm text-green-600">
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                        Completado
                                                    </span>
                                                @else
                                                    <input
                                                        type="file"
                                                        wire:model="justificanteArchivos.{{ $alumnoId }}"
                                                        accept=".pdf,.jpg,.png"
                                                        class="block w-full text-sm text-zinc-500 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-zinc-700 hover:file:bg-zinc-200"
                                                    />
                                                    @error("justificanteArchivos.{$alumnoId}")
                                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                @endif
                                            @else
                                                <span class="text-sm text-zinc-400">—</span>
                                            @endif
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="@if($modoLectura) 3 @else 5 @endif" class="px-4 py-12 text-center text-zinc-500">
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
                    @if(!$modoLectura)
                        <flux:button wire:click="guardar" variant="primary">
                            Guardar asistencias
                        </flux:button>
                    @else
                        <span class="text-sm text-blue-600 font-medium">✅ Asistencia registrada</span>
                    @endif
                </div>
            @endif
        @else
            {{-- ────── Consulta ────── --}}
            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
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

                <flux:select wire:model.live="alumno_id" placeholder="Todos los alumnos">
                    @foreach($alumnosConsulta as $alumno)
                        <option value="{{ $alumno->id }}">{{ $alumno->persona->apellido_paterno }} {{ $alumno->persona->apellido_materno }}, {{ $alumno->persona->nombre }}</option>
                    @endforeach
                </flux:select>

                <flux:input type="date" wire:model.live="fecha_desde" label="Fecha desde" />
                <flux:input type="date" wire:model.live="fecha_hasta" label="Fecha hasta" />
            </div>

            <div class="mb-6">
                <flux:button wire:click="consultar" variant="primary" :disabled="!$grupo_id || !$fecha_desde || !$fecha_hasta">
                    Consultar
                </flux:button>
            </div>

            @if($consultado)
                {{-- Resumen --}}
                <div class="mb-4 flex flex-wrap gap-3 text-sm">
                    <span class="rounded-full bg-emerald-100 px-3 py-1 font-medium text-emerald-700">
                        ✅ Asistió: {{ $resumen['asistio'] ?? 0 }}
                    </span>
                    <span class="rounded-full bg-red-100 px-3 py-1 font-medium text-red-700">
                        ❌ Falta: {{ $resumen['falta'] ?? 0 }}
                    </span>
                    <span class="rounded-full bg-amber-100 px-3 py-1 font-medium text-amber-700">
                        ⏰ Retardo: {{ $resumen['retardo'] ?? 0 }}
                    </span>
                    <span class="rounded-full bg-blue-100 px-3 py-1 font-medium text-blue-700">
                        📄 Justificado: {{ $resumen['justificado'] ?? 0 }}
                    </span>
                    <span class="rounded-full bg-zinc-100 px-3 py-1 font-medium text-zinc-700">
                        Total: {{ $resumen['total'] ?? 0 }}
                    </span>
                </div>

                {{-- Tabla de resultados --}}
                <div class="overflow-x-auto rounded-lg border border-borde">
                    <table class="min-w-full divide-y divide-borde">
                        <thead class="bg-tabla-encabezado">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Fecha</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Alumno</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Grupo</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Estatus</th>
                                <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Motivo</th>
                                <th class="px-4 py-3 text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Archivo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-borde bg-white">
                            @forelse($resultados as $item)
                                <tr class="hover:bg-hover">
                                    <td class="px-4 py-3 text-sm text-zinc-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 text-sm whitespace-nowrap">{{ $item->fecha->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                                        {{ $item->alumno?->persona?->apellido_paterno }} {{ $item->alumno?->persona?->apellido_materno }}, {{ $item->alumno?->persona?->nombre }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-600">
                                        {{ $item->grupo?->grado?->nombre }} - {{ $item->grupo?->nombre }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @switch($item->estatus)
                                            @case('asistio')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">✅ Asistió</span>
                                                @break
                                            @case('falta')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-700">❌ Falta</span>
                                                @break
                                            @case('retardo')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">⏰ Retardo</span>
                                                @break
                                            @case('pendiente')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">📄 Pendiente</span>
                                                @break
                                            @case('justificado')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-700">📄 Justificado</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="px-4 py-3 text-sm text-zinc-500">
                                        {{ $item->justificante?->motivo ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($item->justificante?->archivo_path)
                                            <a href="{{ asset('storage/' . $item->justificante->archivo_path) }}" target="_blank" class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 underline">
                                                📎 Descargar
                                            </a>
                                        @else
                                            <span class="text-zinc-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-4 py-12 text-center text-zinc-500">
                                        No se encontraron registros de asistencia para los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($resultados && $resultados->hasPages())
                    <div class="mt-4">
                        {{ $resultados->links() }}
                    </div>
                @endif

                {{-- Botón PDF --}}
                <div class="mt-4 flex justify-end">
                    <flux:button wire:click="descargarPDFConsulta" variant="primary" icon="arrow-down-tray">
                        Descargar PDF
                    </flux:button>
                </div>
            @endif
        @endif
    </flux:main>
</div>
