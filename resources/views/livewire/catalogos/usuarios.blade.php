<div>
    <x-layouts::app.sidebar>
        <flux:main>
            <x-page-header title="Usuarios" />
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Usuarios</h1>
                <flux:button wire:click="crear" variant="primary">
                    Nuevo Usuario
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Foto</th>
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
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">Rol</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                        @forelse($usuarios as $usuario)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3">
                                    @php $photoUrl = $usuario->profilePhotoUrl(); @endphp
                                    @if($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="{{ $usuario->name }}" class="h-8 w-8 rounded-full object-cover">
                                    @else
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-zinc-200 dark:bg-zinc-600 text-xs font-semibold">
                                            {{ $usuario->initials() }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-medium">{{ $usuario->name }}</td>
                                <td class="px-4 py-3 text-sm">{{ $usuario->email }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($usuario->roles->isNotEmpty())
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
                                            {{ $usuario->roles->first()->name }}
                                        </span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <flux:button wire:click="editar({{ $usuario->id }})" size="sm" inset="top bottom">Editar</flux:button>

                                    @if((int) $usuario->id !== auth()->id())
                                        <flux:button wire:click="eliminar({{ $usuario->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Eliminar este usuario?">Eliminar</flux:button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-zinc-500">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $usuarios->links() }}
            </div>

            {{-- Modal --}}
            <flux:modal wire:model="showModal" class="w-full max-w-lg">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Usuario</h2>
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

                    <flux:select wire:model="rol" label="Rol">
                        <option value="">Seleccionar rol...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role }}">{{ $role }}</option>
                        @endforeach
                    </flux:select>

                    <flux:field>
                        <flux:label>Foto de perfil</flux:label>
                        <input type="file" wire:model="foto_perfil" accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-sm text-zinc-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-zinc-50 file:text-zinc-700 hover:file:bg-zinc-100 dark:file:bg-zinc-800 dark:file:text-zinc-300 dark:hover:file:bg-zinc-700">
                        <flux:error name="foto_perfil" />
                    </flux:field>

                    @if($foto_perfil)
                        <div class="flex justify-center">
                            <img src="{{ $foto_perfil->temporaryUrl() }}" class="h-20 w-20 rounded-full object-cover">
                        </div>
                    @endif

                    <div class="flex justify-end gap-3 pt-2">
                        <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
                        <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
                    </div>
                </div>
            </flux:modal>
        </flux:main>
    </x-layouts::app.sidebar>
</div>
