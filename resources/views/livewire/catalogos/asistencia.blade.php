<div>
    <flux:main>
        <x-page-header title="Consulta de Asistencia" />

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
    </flux:main>
</div>
