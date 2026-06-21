<div>
   {{--  <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Periodos de Evaluación" />
            <div class="flex items-center justify-between mb-6">
                <flux:button wire:click="crear" variant="primary">
                    Nuevo Periodo
                </flux:button>
            </div>

            {{-- Filtro por ciclo escolar y búsqueda --}}
            <div class="mb-4 flex gap-4 flex-wrap">
                <div class="max-w-xs flex-1">
                    <flux:select wire:model.live="filtro_ciclo" placeholder="Todos los ciclos">
                        @foreach($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                        @endforeach
                    </flux:select>
                </div>
                <div class="max-w-sm flex-1">
                    <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre u orden..." icon="magnifying-glass" />
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-borde">
                <table class="min-w-full divide-y divide-borde">
                    <thead class="bg-tabla-encabezado">
                        <tr>
                            <th wire:click="sortBy('ciclo_escolar_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center gap-1">
                                    Ciclo
                                    <x-sort-indicator :field="'ciclo_escolar_id'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('nombre')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Nombre
                                    <x-sort-indicator :field="'nombre'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('orden')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center gap-1">
                                    Orden
                                    <x-sort-indicator :field="'orden'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('fecha_inicio')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center gap-1">
                                    Fecha Inicio
                                    <x-sort-indicator :field="'fecha_inicio'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('fecha_fin')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center gap-1">
                                    Fecha Fin
                                    <x-sort-indicator :field="'fecha_fin'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde bg-white">
                        @forelse($periodos as $periodo)
                            <tr class="hover:bg-hover">
                                <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $periodo->cicloEscolar?->nombre }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $periodo->nombre }}</td>
                                <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $periodo->orden }}</td>
                                <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $periodo->fecha_inicio->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $periodo->fecha_fin->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <flux:button wire:click="editar({{ $periodo->id }})" size="sm" inset="top bottom">Editar</flux:button>
                                    <flux:button wire:click="eliminar({{ $periodo->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Eliminar este periodo de evaluación?">Eliminar</flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
                                    No hay periodos de evaluación registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $periodos->links() }}
            </div>

            {{-- Modal --}}
            <flux:modal wire:model="showModal" wire:key="modal-{{ $modalKey }}" class="w-full max-w-lg">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Periodo de Evaluación</h2>
                    </div>

                    <flux:select wire:model="ciclo_escolar_id" label="Ciclo Escolar">
                        @foreach($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="nombre" label="Nombre" placeholder="Ej: Primer Periodo" />

                    <flux:input wire:model="orden" label="Orden" type="number" min="1" max="10" placeholder="1" />

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="fecha_inicio" label="Fecha de inicio" type="date" />
                        <flux:input wire:model="fecha_fin" label="Fecha de fin" type="date" />
                    </div>

                    <div class="flex justify-end gap-3 pt-2">
                        <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
                        <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
                    </div>
                </div>
            </flux:modal>
        </flux:main>
    {{-- </x-layouts::app.sidebar> --}}
</div>
