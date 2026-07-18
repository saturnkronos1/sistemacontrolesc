<div>
    <flux:main>
        <x-page-header title="Pasar lista" />

        <div class="mb-4 grid grid-cols-1 sm:grid-cols-3 gap-4">
            @if($esDocente)
                <div class="flex items-center gap-2 rounded-lg border border-borde bg-white px-4 py-2 text-sm text-texto">
                    <flux:icon name="academic-cap" class="h-5 w-5 text-primary shrink-0" />
                    <span>
                        Grupo: <strong>{{ $grupos->first()?->grado?->nombre }} - {{ $grupos->first()?->nombre }}</strong>
                    </span>
                </div>
            @else
                <flux:select wire:model.live="ciclo_escolar_id" placeholder="Seleccionar ciclo escolar">
                    @foreach($ciclosEscolares as $ciclo)
                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                    @endforeach
                </flux:select>

                <flux:select wire:model.live="grupo_id" wire:key="pasar-lista-grupo" placeholder="Seleccionar grupo">
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->grado?->nombre }} - {{ $grupo->nombre }} ({{ $grupo->cicloEscolar?->nombre ?? 'Sin ciclo' }})</option>
                    @endforeach
                </flux:select>
            @endif

            <flux:input type="date" wire:model="fecha" label="Fecha" />
        </div>

        @if(!$esDocente)
            <div class="mb-6">
                <flux:button wire:click="cargarAlumnos" variant="primary" :disabled="!$grupo_id || !$fecha">
                    Cargar alumnos
                </flux:button>
            </div>
        @endif

        @if($cargado)
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
                                    {{ $alumno['persona']['apellido_paterno'] }} {{ $alumno['persona']['apellido_materno'] }} {{ $alumno['persona']['nombre'] }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($modoLectura)
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
                                                <div class="space-y-1">
                                                    @php $tieneArchivo = isset($justificanteArchivos[$alumnoId]) && $justificanteArchivos[$alumnoId] !== null; @endphp
                                                    <label class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-2 text-sm font-medium cursor-pointer transition-colors @if($tieneArchivo) border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-400 @else border-blue-300 bg-blue-50 text-blue-700 hover:bg-blue-100 hover:border-blue-400 @endif">
                                                        @if($tieneArchivo)
                                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                            <span>Archivo seleccionado</span>
                                                        @else
                                                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32a.75.75 0 0 1-1.06-1.06L16.5 8.25" />
                                                            </svg>
                                                            <span>Seleccionar archivo</span>
                                                        @endif
                                                        <input type="file" wire:model="justificanteArchivos.{{ $alumnoId }}" accept=".pdf,.jpg,.png" class="hidden" />
                                                    </label>
                                                    <p class="text-xs text-zinc-400">PDF, JPG o PNG — opcional</p>
                                                    @error("justificanteArchivos.{$alumnoId}")
                                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                                    @enderror
                                                </div>
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
    </flux:main>
</div>
