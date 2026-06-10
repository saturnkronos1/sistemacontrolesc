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
                <flux:input wire:model.live="search" placeholder="Buscar por nombre, CURP o email..." icon="magnifying-glass" />
            </div>

            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th wire:click="sortBy('apellido_paterno')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-zinc-700 dark:hover:text-zinc-300 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Nombre
                                    <x-sort-indicator :field="'apellido_paterno'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('curp')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-zinc-700 dark:hover:text-zinc-300 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    CURP
                                    <x-sort-indicator :field="'curp'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">
                                Teléfono
                            </th>
                            <th wire:click="sortBy('estatus')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-zinc-700 dark:hover:text-zinc-300 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Estatus
                                    <x-sort-indicator :field="'estatus'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-900">
                        @forelse($docentes as $docente)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                                    @if($docente->persona)
                                        {{ $docente->persona->apellido_paterno }} {{ $docente->persona->apellido_materno }}, {{ $docente->persona->nombre }}
                                    @else
                                        {{ $docente->name }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-mono">{{ $docente->persona?->curp ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">{{ $docente->persona?->telefono ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $estatus = $docente->persona?->estatus ?? 'activo';
                                        $badge = $estatus === 'activo'
                                            ? ['color' => 'green', 'label' => 'Activo']
                                            : ['color' => 'red', 'label' => 'Inactivo'];
                                    @endphp
                                    <span @class([
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' => $badge['color'] === 'green',
                                        'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' => $badge['color'] === 'red',
                                    ])>
                                        {{ $badge['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <flux:button wire:click="editar({{ $docente->id }})" size="sm" inset="top bottom">Editar</flux:button>

                                    @if((int) $docente->id !== auth()->id())
                                        <flux:button wire:click="eliminar({{ $docente->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Eliminar este docente?">Eliminar</flux:button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center text-zinc-500">
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
            <flux:modal wire:model="showModal" class="w-full max-w-2xl">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Docente</h2>
                    </div>

                    {{-- CURP y Cédula --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input wire:model="curp" label="CURP" placeholder="18 caracteres" maxlength="18" class="font-mono uppercase" />
                        <flux:input wire:model="cedula" label="Cédula profesional" placeholder="Cédula" maxlength="50" />
                    </div>

                    {{-- Nombres y apellidos --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <flux:input wire:model="nombres" label="Nombres *" placeholder="Nombre(s)" />
                        <flux:input wire:model="apellido_paterno" label="Apellido paterno *" placeholder="Apellido paterno" />
                        <flux:input wire:model="apellido_materno" label="Apellido materno" placeholder="Apellido materno" />
                    </div>

                    {{-- Teléfono y correo --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input wire:model="telefono" label="Teléfono" type="tel" placeholder="10 dígitos" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                        <flux:input wire:model="correo" label="Correo" type="email" placeholder="correo@ejemplo.com" />
                    </div>

                    {{-- Fecha de nacimiento y estatus --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input wire:model="fecha_nacimiento" label="Fecha de nacimiento" type="date" />
                        <flux:select wire:model="estatus" label="Estatus">
                            <option value="activo">Activo</option>
                            <option value="inactivo">Inactivo</option>
                        </flux:select>
                    </div>

                    {{-- Dirección --}}
                    <flux:textarea wire:model="direccion" label="Dirección" placeholder="Dirección completa" />

                    <flux:separator text="Datos de la cuenta" />

                    {{-- Email de login --}}
                    <flux:input wire:model="email" label="Email de inicio de sesión *" type="email" placeholder="correo@ejemplo.com" />

                    {{-- Password --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input wire:model="password" label="{{ $editId ? 'Nueva contraseña (dejar vacío para mantener)' : 'Contraseña *' }}" type="password" viewable />
                        <flux:input wire:model="password_confirmation" label="Confirmar contraseña" type="password" viewable />
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
