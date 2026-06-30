<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Grupos" />
            <div class="flex items-center justify-between mb-6">
                <flux:button wire:click="crear" variant="primary">
                    Nuevo Grupo
                </flux:button>
            </div>

            {{-- Filtros y búsqueda --}}
            <div class="mb-4 flex gap-4 flex-wrap">
                <div class="max-w-xs flex-1">
                    <flux:select wire:model.live="filtro_ciclo" placeholder="Todos los ciclos">
                        @foreach($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="max-w-xs flex-1">
                    <flux:select wire:model.live="filtro_grado">
                        <option value="">Todos los grados</option>
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="max-w-sm flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o docente..." icon="magnifying-glass" />
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-borde">
                <table class="min-w-full divide-y divide-borde">
                    <thead class="bg-tabla-encabezado">
                        <tr>
                            <th wire:click="sortBy('nombre')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Nombre
                                    <x-sort-indicator :field="'nombre'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('grado_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap max-sm:hidden">
                                <div class="flex items-center gap-1">
                                    Grado
                                    <x-sort-indicator :field="'grado_id'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('ciclo_escolar_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap max-sm:hidden">
                                <div class="flex items-center gap-1">
                                    Ciclo
                                    <x-sort-indicator :field="'ciclo_escolar_id'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('docente_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Docente
                                    <x-sort-indicator :field="'docente_id'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde bg-white">
                        @forelse($grupos as $grupo)
                            <tr class="hover:bg-hover">
                                <td class="px-4 py-3 text-sm font-medium">{{ $grupo->nombre }}</td>
                                <td class="px-4 py-3 text-sm max-sm:hidden">{{ $grupo->grado?->nombre }}</td>
                                <td class="px-4 py-3 text-sm max-sm:hidden">{{ $grupo->cicloEscolar?->nombre }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($grupo->docente)
                                        <span class="inline-flex items-center rounded-full bg-violet-100 px-2.5 py-0.5 text-xs font-medium text-violet-800">
                                            {{ $grupo->docente->name }}
                                        </span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <flux:button wire:click="editar({{ $grupo->id }})" size="sm" inset="top bottom">Editar</flux:button>
                                    <flux:button wire:click="eliminar({{ $grupo->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Eliminar este grupo?">Eliminar</flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-zinc-500">
                                    No hay grupos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $grupos->links() }}
            </div>

            {{-- Modal --}}
            <flux:modal wire:model="showModal" wire:key="modal-{{ $modalKey }}" :dismissible="false" class="w-full max-w-lg">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Grupo</h2>
                    </div>

                    <flux:select wire:model="ciclo_escolar_id" label="Ciclo Escolar">
                        @foreach($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="grado_id" label="Grado">
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="nombre" label="Nombre del grupo" placeholder="Ej: A" maxlength="50" />

                    <flux:select wire:model="docente_id" placeholder="Sin asignar">
                        <option value="">Sin docente asignado</option>
                        @foreach($docentes as $docente)
                            <option value="{{ $docente->id }}">{{ $docente->name }}</option>
                        @endforeach
                    </flux:select>

                    <div class="flex justify-end gap-3 pt-2">
                        <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
                        <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
                    </div>
                </div>
            </flux:modal>
        </flux:main>
    {{-- </x-layouts::app.sidebar> --}}
</div>
