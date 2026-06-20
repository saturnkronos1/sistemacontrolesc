<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Materias" />
            <div class="flex items-center justify-between mb-6">
                <flux:button wire:click="crear" variant="primary">
                    Nueva Materia
                </flux:button>
            </div>

            {{-- Filtro por grado --}}
            <div class="mb-4 max-w-xs">
                <flux:select wire:model.live="filtro_grado" placeholder="Todos los grados">
                    @foreach($grados as $grado)
                        <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                    @endforeach
                </flux:select>
            </div>

            <div class="overflow-x-auto rounded-lg border border-borde">
                <table class="min-w-full divide-y divide-borde">
                    <thead class="bg-tabla-encabezado">
                        <tr>
                            <th wire:click="sortBy('clave_materia')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center gap-1">
                                    Clave
                                    <x-sort-indicator :field="'clave_materia'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('nombre')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Nombre
                                    <x-sort-indicator :field="'nombre'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('grado_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                                <div class="flex items-center gap-1">
                                    Grado
                                    <x-sort-indicator :field="'grado_id'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-borde bg-white">
                        @forelse($materias as $materia)
                            <tr class="hover:bg-hover">
                                <td class="px-4 py-3 text-sm font-mono hidden sm:table-cell">{{ $materia->clave_materia }}</td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $materia->nombre }}</td>
                                <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $materia->grado?->nombre }}</td>
                                <td class="px-4 py-3 text-right">
                                    <flux:button wire:click="editar({{ $materia->id }})" size="sm" inset="top bottom">Editar</flux:button>
                                    <flux:button wire:click="eliminar({{ $materia->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Eliminar esta materia?">Eliminar</flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center text-zinc-500">
                                    No hay materias registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $materias->links() }}
            </div>

            {{-- Modal --}}
            <flux:modal wire:model="showModal" wire:key="modal-{{ $modalKey }}" class="w-full max-w-lg">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nueva' }} Materia</h2>
                    </div>

                    <flux:select wire:model="grado_id" label="Grado">
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="nombre" label="Nombre" placeholder="Ej: Matemáticas I" />

                    <flux:input wire:model="clave_materia" label="Clave de materia" placeholder="Ej: MAT-101" />

                    <div class="flex justify-end gap-3 pt-2">
                        <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
                        <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
                    </div>
                </div>
            </flux:modal>
        </flux:main>
    {{-- </x-layouts::app.sidebar> --}}
</div>
