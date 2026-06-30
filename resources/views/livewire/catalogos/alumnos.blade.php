<div>
    <flux:main>
        <x-page-header title="Alumnos" />

        <div class="flex items-center justify-between mb-6">
            <flux:button wire:click="crear" variant="primary">
                Nuevo Alumno
            </flux:button>
        </div>

        {{-- Filtros y búsqueda --}}
        <div class="mb-4 flex gap-4 flex-wrap">
            <div class="max-w-xs flex-1">
                <flux:select wire:model.live="filtro_grado" placeholder="Todos los grados">
                    @foreach($grados as $grado)
                        <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="max-w-xs flex-1">
                <flux:select wire:model.live="filtro_grupo" placeholder="Todos los grupos">
                    @foreach($grupos as $grupo)
                        <option value="{{ $grupo->id }}">{{ $grupo->grado?->nombre }} - {{ $grupo->nombre }}</option>
                    @endforeach
                </flux:select>
            </div>
            <div class="max-w-xs flex-1">
                <flux:select wire:model.live="filtro_estatus" placeholder="Todos los estatus">
                    <option value="activo">Activo</option>
                    <option value="baja">Baja</option>
                    <option value="egresado">Egresado</option>
                </flux:select>
            </div>
            <div class="max-w-sm flex-1">
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por matrícula, nombre o apellido..." icon="magnifying-glass" />
            </div>
        </div>

        <div class="overflow-x-auto rounded-lg border border-borde">
            <table class="min-w-full divide-y divide-borde">
                <thead class="bg-tabla-encabezado">
                    <tr>
                        <th wire:click="sortBy('matricula')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                            <div class="flex items-center gap-1">
                                Matrícula
                                <x-sort-indicator :field="'matricula'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                            </div>
                        </th>
                        <th wire:click="sortBy('nombre_completo')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                Nombre completo
                                <x-sort-indicator :field="'nombre_completo'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                            </div>
                        </th>
                        <th wire:click="sortBy('curp')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                            <div class="flex items-center gap-1">
                                CURP
                                <x-sort-indicator :field="'curp'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                            </div>
                        </th>
                        <th wire:click="sortBy('grado_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                Grado
                                <x-sort-indicator :field="'grado_id'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                            </div>
                        </th>
                        <th wire:click="sortBy('grupo_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                            <div class="flex items-center gap-1">
                                Grupo
                                <x-sort-indicator :field="'grupo_id'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                            </div>
                        </th>
                        <th wire:click="sortBy('estatus')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                Estatus
                                <x-sort-indicator :field="'estatus'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                            </div>
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde bg-white">
                    @forelse($alumnos as $alumno)
                        <tr class="hover:bg-hover">
                            <td class="px-4 py-3 text-sm font-mono hidden sm:table-cell">{{ $alumno->matricula }}</td>
                            <td class="px-4 py-3 text-sm font-medium">
                                {{ $alumno->persona?->apellido_paterno }} {{ $alumno->persona?->apellido_materno }} {{ $alumno->persona?->nombre }}
                            </td>
                            <td class="px-4 py-3 text-sm font-mono uppercase hidden sm:table-cell">{{ $alumno->persona?->curp }}</td>
                            <td class="px-4 py-3 text-sm">{{ $alumno->grado?->nombre }}</td>
                            <td class="px-4 py-3 text-sm hidden sm:table-cell">
                                @if($alumno->grupo)
                                    <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-texto">
                                        {{ $alumno->grupo->nombre }}
                                    </span>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $estatusClasses = [
                                        'activo' =>         'bg-green-100 text-green-800',
                                        'baja' => 'bg-red-100 text-red-800',
                                        'egresado' => 'bg-zinc-100 text-zinc-600',
                                    ];
                                @endphp
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estatusClasses[$alumno->estatus] ?? 'bg-zinc-100 text-zinc-800' }}">
                                    {{ ucfirst($alumno->estatus) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <flux:button href="{{ route('alumnos.show', $alumno) }}" size="sm" inset="top bottom">Ver</flux:button>
                                <flux:button wire:click="editar({{ $alumno->id }})" size="sm" inset="top bottom">Editar</flux:button>

                                @if($alumno->estatus === 'activo')
                                    <flux:button wire:click="darBaja({{ $alumno->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Dar de baja a este alumno?">Dar de baja</flux:button>
                                    @if($alumno->grado?->nombre === '6°')
                                        <flux:button wire:click="darEgreso({{ $alumno->id }})" size="sm" variant="primary" inset="top bottom" wire:confirm="¿Marcar como egresado?">Egresar</flux:button>
                                    @endif
                                @elseif($alumno->estatus === 'baja' || $alumno->estatus === 'egresado')
                                    <flux:button wire:click="reactivar({{ $alumno->id }})" size="sm" variant="primary" inset="top bottom" wire:confirm="¿Reactivar este alumno?">Reactivar</flux:button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-zinc-500">
                                No hay alumnos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $alumnos->links() }}
        </div>

        {{-- Modal reutilizado --}}
        @include('livewire.catalogos.alumnos-form')
    </flux:main>
</div>
