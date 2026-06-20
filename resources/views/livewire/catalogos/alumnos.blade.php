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
                <flux:input wire:model.live="search" placeholder="Buscar por matrícula, nombre o apellido..." icon="magnifying-glass" />
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
                        <th wire:click="sortBy('grado_id')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap hidden sm:table-cell">
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
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde bg-white">
                    @forelse($alumnos as $alumno)
                        <tr class="hover:bg-hover">
                            <td class="px-4 py-3 text-sm font-mono hidden sm:table-cell">{{ $alumno->matricula }}</td>
                            <td class="px-4 py-3 text-sm font-medium">
                                {{ $alumno->persona?->apellido_paterno }} {{ $alumno->persona?->apellido_materno }}, {{ $alumno->persona?->nombre }}
                            </td>
                            <td class="px-4 py-3 text-sm font-mono uppercase hidden sm:table-cell">{{ $alumno->persona?->curp }}</td>
                            <td class="px-4 py-3 text-sm hidden sm:table-cell">{{ $alumno->grado?->nombre }}</td>
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
                            <td class="px-4 py-3 text-right whitespace-nowrap">
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

        {{-- Modal --}}
        <flux:modal wire:model="showModal" wire:key="modal-{{ $modalKey }}" class="w-full max-w-2xl">
            <div class="space-y-4 max-h-[80vh] overflow-y-auto px-0.5">
                <div>
                    <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Alumno</h2>
                </div>

                {{-- Datos del alumno --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="apellido_paterno" label="Apellido paterno" placeholder="García" />
                    <flux:input wire:model="apellido_materno" label="Apellido materno" placeholder="López" />
                </div>

                <flux:input wire:model="nombre" label="Nombre(s)" placeholder="Juan Carlos" />

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input wire:model="curp" label="CURP" placeholder="GARC123456HDFRRN08" maxlength="18" class="font-mono uppercase" />
                    <flux:input wire:model="telefono" label="Teléfono" type="tel" placeholder="5512345678" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                </div>

                <flux:input wire:model="matricula" label="Matrícula" placeholder="ALU260001" class="font-mono" />

                {{-- Sección de tutor (toggle) --}}
                <div class="border-t border-borde pt-4">
                    <button type="button" wire:click="$toggle('showFamilia')" class="flex items-center gap-2 text-sm font-medium text-zinc-600 hover:text-zinc-900">
                        <svg class="w-4 h-4 transition-transform {{ $showFamilia ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                        {{ $showFamilia ? 'Ocultar' : 'Agregar' }} tutor del alumno
                    </button>

                    @if($showFamilia)
                        <div class="mt-4 space-y-4">
                            <div class="rounded-lg border border-borde p-4 space-y-3">
                                <h3 class="text-sm font-medium text-texto">Tutor designado</h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <flux:input wire:model="tutor_apellido_paterno" label="Apellido paterno" placeholder="García" />
                                    <flux:input wire:model="tutor_apellido_materno" label="Apellido materno" placeholder="López" />
                                </div>

                                <flux:input wire:model="tutor_nombre" label="Nombre(s)" placeholder="José" />

                                <flux:select wire:model="tutor_parentesco" label="Parentesco">
                                    <option value="Padre">Padre</option>
                                    <option value="Madre">Madre</option>
                                    <option value="Abuelo/a">Abuelo/a</option>
                                    <option value="Hermana/o">Hermana/o</option>
                                    <option value="Tutor Legal">Tutor Legal</option>
                                </flux:select>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <flux:input wire:model="tutor_telefono" label="Teléfono" type="tel" placeholder="5512345678" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                                    <flux:input wire:model="tutor_telefono_2" label="Teléfono 2 (opcional)" type="tel" placeholder="5512345679" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <flux:input wire:model="tutor_email" label="Email (opcional)" placeholder="correo@ejemplo.com" type="email" />
                                    <flux:input wire:model="tutor_fecha_nacimiento" label="Fecha de nacimiento" type="date" />
                                </div>

                                <flux:input wire:model="tutor_domicilio" label="Domicilio" placeholder="Calle y número" />

                                <flux:separator text="Cuenta de usuario del tutor" />

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <flux:input wire:model="tutor_user_email" label="Email de usuario" placeholder="tutor@correo.com" type="email" />
                                    <flux:input wire:model="tutor_user_password" label="Contraseña" type="password" placeholder="{{ $editId ? 'Dejar vacío para mantener actual' : 'Mínimo 8 caracteres' }}" />
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
                    <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
                </div>
            </div>
        </flux:modal>
    </flux:main>
</div>
