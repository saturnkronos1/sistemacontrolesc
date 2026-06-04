<div>
    <x-layouts::app.sidebar>
        <flux:main>
            <x-page-header title="Alumnos" />
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Alumnos</h1>
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
                    <flux:input wire:model.live="search" placeholder="Buscar por matrícula, nombre o apellido..." icon="magnifying-glass" />
                </div>
            </div>

            <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th wire:click="sortBy('matricula')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-zinc-700 dark:hover:text-zinc-300 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Matrícula
                                    <x-sort-indicator :field="'matricula'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('nombre_completo')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-zinc-700 dark:hover:text-zinc-300 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Nombre completo
                                    <x-sort-indicator :field="'nombre_completo'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('grado_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-zinc-700 dark:hover:text-zinc-300 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Grado
                                    <x-sort-indicator :field="'grado_id'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
                            </th>
                            <th wire:click="sortBy('grupo_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-zinc-700 dark:hover:text-zinc-300 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    Grupo
                                    <x-sort-indicator :field="'grupo_id'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                                </div>
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
                        @forelse($alumnos as $alumno)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-4 py-3 text-sm font-mono">{{ $alumno->matricula }}</td>
                                <td class="px-4 py-3 text-sm font-medium">
                                    {{ $alumno->persona?->apellido_paterno }} {{ $alumno->persona?->apellido_materno }}, {{ $alumno->persona?->nombre }}
                                </td>
                                <td class="px-4 py-3 text-sm">{{ $alumno->grado?->nombre }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if($alumno->grupo)
                                        <span class="inline-flex items-center rounded-full bg-zinc-100 px-2.5 py-0.5 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                            {{ $alumno->grupo->nombre }}
                                        </span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @php
                                        $estatusClasses = [
                                            'activo' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                            'baja' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                            'egresado' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-900/30 dark:text-zinc-400',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $estatusClasses[$alumno->estatus] ?? 'bg-zinc-100 text-zinc-800' }}">
                                        {{ ucfirst($alumno->estatus) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <flux:button wire:click="editar({{ $alumno->id }})" size="sm" inset="top bottom">Editar</flux:button>

                                    @if($alumno->estatus === 'activo')
                                        <flux:button wire:click="darBaja({{ $alumno->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Dar de baja a este alumno?">Dar de baja</flux:button>
                                        <flux:button wire:click="darEgreso({{ $alumno->id }})" size="sm" variant="primary" inset="top bottom" wire:confirm="¿Marcar como egresado?">Egresar</flux:button>
                                    @elseif($alumno->estatus === 'baja' || $alumno->estatus === 'egresado')
                                        <flux:button wire:click="reactivar({{ $alumno->id }})" size="sm" variant="primary" inset="top bottom" wire:confirm="¿Reactivar este alumno?">Reactivar</flux:button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-zinc-500">
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

            {{-- Modal --}}
            <flux:modal wire:model="showModal" class="w-full max-w-lg">
                <div class="space-y-4">
                    <div>
                        <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Alumno</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input wire:model="apellido_paterno" label="Apellido paterno" placeholder="García" />
                        <flux:input wire:model="apellido_materno" label="Apellido materno" placeholder="López" />
                    </div>

                    <flux:input wire:model="nombre" label="Nombre(s)" placeholder="Juan Carlos" />

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <flux:input wire:model="curp" label="CURP" placeholder="GARC123456HDFRRN08" maxlength="18" class="font-mono uppercase" />
                        <flux:input wire:model="telefono" label="Teléfono" placeholder="5512345678" maxlength="20" />
                    </div>

                    <flux:select wire:model="grado_id" label="Grado">
                        <option value="">Seleccionar grado...</option>
                        @foreach($grados as $grado)
                            <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                        @endforeach
                    </flux:select>

                    <flux:select wire:model="grupo_id" label="Grupo" placeholder="Sin grupo">
                        <option value="">Sin grupo</option>
                        @foreach($grupos as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->grado?->nombre }} - {{ $grupo->nombre }}</option>
                        @endforeach
                    </flux:select>

                    <flux:input wire:model="matricula" label="Matrícula" placeholder="ALU260001" class="font-mono" />

                    <div class="flex justify-end gap-3 pt-2">
                        <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
                        <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
                    </div>
                </div>
            </flux:modal>
        </flux:main>
    </x-layouts::app.sidebar>
</div>
