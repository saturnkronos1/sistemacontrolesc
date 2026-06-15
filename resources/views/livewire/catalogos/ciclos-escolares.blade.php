<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Ciclos Escolares" />
            <div class="flex items-center justify-between mb-6">
                <flux:button wire:click="crear" variant="primary">
                    Nuevo Ciclo
                </flux:button>
            </div>

            {{-- Búsqueda --}}
            <div class="mb-4 max-w-sm">
                <flux:input wire:model.live="search" placeholder="Buscar por nombre o fecha..." icon="magnifying-glass" />
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
                            <th wire:click="sortBy('fecha_inicio')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center gap-1">
                                    Inicio
                                    <x-sort-indicator :field="'fecha_inicio'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('fecha_fin')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center gap-1">
                                    Fin
                                    <x-sort-indicator :field="'fecha_fin'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('estatus')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Estatus
                                    <x-sort-indicator :field="'estatus'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde bg-white">
                        @forelse($ciclos as $ciclo)
                            <tr class="hover:bg-hover">
                                <td class="px-4 py-3 text-sm font-medium">{{ $ciclo->nombre }}</td>
                                <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $ciclo->fecha_inicio->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $ciclo->fecha_fin->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $badge = match($ciclo->estatus) {
                                            'activo' => ['color' => 'green', 'label' => 'Activo'],
                                            'pendiente' => ['color' => 'yellow', 'label' => 'Pendiente'],
                                            'finalizado' => ['color' => 'zinc', 'label' => 'Finalizado'],
                                            default => ['color' => 'zinc', 'label' => $ciclo->estatus],
                                        };
                                    @endphp
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-green-100 text-green-800' => $badge['color'] === 'green',
                                        'bg-yellow-100 text-yellow-800' => $badge['color'] === 'yellow',
                                        'bg-zinc-100 text-zinc-600' => $badge['color'] === 'zinc',
                                    ])>
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right flex items-center justify-end gap-2">
                                    @if($ciclo->estatus === 'pendiente' && $ciclo->autocreado)
                                        <flux:button wire:click="confirmar({{ $ciclo->id }})" size="sm" variant="primary" inset="top bottom" wire:confirm="¿Activar este ciclo escolar?">
                                            Confirmar
                                        </flux:button>
                                    @endif
                                    <flux:button wire:click="editar({{ $ciclo->id }})" size="sm" inset="top bottom">Editar</flux:button>
                                    <flux:button wire:click="eliminar({{ $ciclo->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Eliminar este ciclo escolar?">Eliminar</flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-zinc-500">
                                    No hay ciclos escolares registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $ciclos->links() }}
            </div>

            {{-- Modal --}}
            <flux:modal wire:model="showModal" class="w-full max-w-lg">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Ciclo Escolar</h2>
                    </div>

                    <flux:input wire:model="nombre" label="Nombre" placeholder="Ej: 2025-2026" />

                    <div class="grid grid-cols-2 gap-4">
                        <flux:input wire:model="fecha_inicio" label="Fecha de inicio" type="date" />
                        <flux:input wire:model="fecha_fin" label="Fecha de fin" type="date" />
                    </div>

                    <flux:select wire:model="estatus" label="Estatus">
                        <option value="pendiente">Pendiente</option>
                        <option value="activo">Activo</option>
                        <option value="finalizado">Finalizado</option>
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
