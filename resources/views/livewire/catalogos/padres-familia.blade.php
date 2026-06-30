<div>
    <flux:main>
        <x-page-header title="Padres de Familia" />

        <div class="flex items-center justify-between mb-6">
            <flux:button wire:click="crear" variant="primary">
                Nuevo Padre
            </flux:button>
        </div>

        {{-- Búsqueda --}}
        <div class="mb-4 max-w-sm">
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre, email o CURP..." icon="magnifying-glass" />
        </div>

        <div class="overflow-x-auto rounded-lg border border-borde">
            <table class="min-w-full divide-y divide-borde">
                <thead class="bg-tabla-encabezado">
                    <tr>
                        <th wire:click="sortBy('apellido_paterno')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                Nombre Completo
                                <x-sort-indicator :field="'apellido_paterno'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                            </div>
                        </th>
                        <th wire:click="sortBy('email')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
                            <div class="flex items-center gap-1">
                                Email
                                <x-sort-indicator :field="'email'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap hidden sm:table-cell">
                            Teléfono
                        </th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 uppercase">ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde bg-white">
                    @forelse($padres as $padre)
                        <tr class="hover:bg-hover">
                            <td class="px-4 py-3 text-sm font-medium">
                                {{ $padre->nombreCompleto() }}
                            </td>
                            <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $padre->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $padre->telefono ?? '—' }}</td>

                            <td class="px-4 py-3 text-center whitespace-nowrap">
                                <flux:button href="{{ route('padres-familia.show', $padre) }}" size="sm" inset="top bottom">Ver</flux:button>
                                <flux:button wire:click="editar({{ $padre->id }})" size="sm" inset="top bottom">Editar</flux:button>
                                <flux:button wire:click="eliminar({{ $padre->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Desvincular este padre de familia? Se eliminarán sus vínculos con alumnos pero se conservará su registro.">Desvincular</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center text-zinc-500">
                                No hay padres de familia registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $padres->links() }}
        </div>

        {{-- Modal reutilizado --}}
        @include('livewire.catalogos.padres-form')
    </flux:main>
</div>
