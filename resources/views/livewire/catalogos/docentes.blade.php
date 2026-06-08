<div>
    {{-- <x-layouts::app.sidebar> --}}
        <flux:main>
            <x-page-header title="Docentes" />
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold lg:hidden">Docentes</h1>
                <flux:button wire:click="crear" variant="primary">
                    Nuevo Docente
                </flux:button>
            </div>

            {{-- Búsqueda --}}
            <div class="mb-4 max-w-sm">
                <flux:input wire:model.live="search" placeholder="Buscar por nombre o email..." icon="magnifying-glass" />
            </div>

            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th wire:click="sortBy('name')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-zinc-700 dark:hover:text-zinc-300 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Nombre
                                    <x-sort-indicator :field="'name'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('email')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-zinc-700 dark:hover:text-zinc-300 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Email
                                    <x-sort-indicator :field="'email'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                        @forelse($docentes as $docente)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 text-sm font-medium">{{ $docente->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $docente->email }}</td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <flux:button wire:click="editar({{ $docente->id }})" size="sm" inset="top bottom">Editar</flux:button>

                                    @if((int) $docente->id !== auth()->id())
                                        <flux:button wire:click="eliminar({{ $docente->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Eliminar este docente?">Eliminar</flux:button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-12 text-center text-zinc-500">
                                    No hay docentes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $docentes->links() }}
            </div>

            {{-- Modal --}}
            <flux:modal wire:model="showModal" class="w-full max-w-lg">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Docente</h2>
                    </div>

                    <flux:input wire:model="name" label="Nombre" placeholder="Nombre completo" />

                    <flux:input wire:model="email" label="Email" type="email" placeholder="correo@ejemplo.com" />

                    @if($editId)
                        <flux:input wire:model="password" label="Nueva contraseña (dejar vacío para mantener)" type="password" />
                        <flux:input wire:model="password_confirmation" label="Confirmar contraseña" type="password" />
                    @else
                        <flux:input wire:model="password" label="Contraseña" type="password" />
                        <flux:input wire:model="password_confirmation" label="Confirmar contraseña" type="password" />
                    @endif

                    <div class="flex justify-end gap-3 pt-2">
                        <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
                        <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
                    </div>
                </div>
            </flux:modal>
        </flux:main>
    {{-- </x-layouts::app.sidebar> --}}
</div>
