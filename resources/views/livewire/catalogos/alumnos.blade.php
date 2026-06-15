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
        <flux:modal wire:model="showModal" class="w-full max-w-2xl">
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

                {{-- Sección de familia (toggle) --}}
                <div class="border-t border-borde pt-4">
                    <button type="button" wire:click="$toggle('showFamilia')" class="flex items-center gap-2 text-sm font-medium text-zinc-600  hover:text-zinc-900 ">
                        <svg class="w-4 h-4 transition-transform {{ $showFamilia ? 'rotate-90' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                        </svg>
                        {{ $showFamilia ? 'Ocultar' : 'Agregar' }} datos de padres / tutores
                    </button>

                    @if($showFamilia)
                        <div class="mt-4 space-y-4">
                            {{-- Tipo de registro --}}
                            <div class="flex gap-6">
                                <label class="flex items-center gap-2 text-sm">
                                    <flux:radio wire:model="tipo_registro" value="padres" />
                                    Registrar padres
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <flux:radio wire:model="tipo_registro" value="tutor_legal" />
                                    Registrar tutor legal
                                </label>
                            </div>

                            @if($tipo_registro === 'padres')
                                {{-- Padre/Madre 1 --}}
                                <div class="rounded-lg border border-borde p-4 space-y-3">
                                    <h3 class="text-sm font-medium text-texto">Padre / Madre 1</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <flux:input wire:model="p1_apellido_paterno" label="Apellido paterno" placeholder="García" />
                                        <flux:input wire:model="p1_apellido_materno" label="Apellido materno" placeholder="López" />
                                    </div>
                                    <flux:input wire:model="p1_nombre" label="Nombre(s)" placeholder="José" />
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <flux:select wire:model="p1_parentesco" label="Parentesco">
                                            <option value="Padre">Padre</option>
                                            <option value="Madre">Madre</option>
                                        </flux:select>
                                        <flux:input wire:model="p1_telefono" label="Teléfono" type="tel" placeholder="5512345678" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <flux:input wire:model="p1_telefono_2" label="Teléfono 2 (opcional)" type="tel" placeholder="5512345679" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                                        <flux:input wire:model="p1_email" label="Email (opcional)" placeholder="correo@ejemplo.com" type="email" />
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <flux:input wire:model="p1_fecha_nacimiento" label="Fecha de nacimiento" type="date" />
                                        <flux:input wire:model="p1_domicilio" label="Domicilio" placeholder="Calle y número" />
                                    </div>
                                </div>

                                {{-- Padre/Madre 2 (opcional) --}}
                                @if($p2_activo)
                                    <div class="rounded-lg border border-borde p-4 space-y-3 relative">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-sm font-medium text-texto">Padre / Madre 2</h3>
                                            <button type="button" wire:click="quitarPadre2" class="text-xs text-red-500 hover:text-red-700">Eliminar</button>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <flux:input wire:model="p2_apellido_paterno" label="Apellido paterno" placeholder="García" />
                                            <flux:input wire:model="p2_apellido_materno" label="Apellido materno" placeholder="López" />
                                        </div>
                                        <flux:input wire:model="p2_nombre" label="Nombre(s)" placeholder="María" />
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <flux:select wire:model="p2_parentesco" label="Parentesco">
                                                <option value="Madre">Madre</option>
                                                <option value="Padre">Padre</option>
                                            </flux:select>
                                            <flux:input wire:model="p2_telefono" label="Teléfono" type="tel" placeholder="5512345678" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <flux:input wire:model="p2_telefono_2" label="Teléfono 2 (opcional)" type="tel" placeholder="5512345679" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                                            <flux:input wire:model="p2_email" label="Email (opcional)" placeholder="correo@ejemplo.com" type="email" />
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <flux:input wire:model="p2_fecha_nacimiento" label="Fecha de nacimiento" type="date" />
                                            <flux:input wire:model="p2_domicilio" label="Domicilio" placeholder="Calle y número" />
                                        </div>
                                    </div>
                                @else
                                    <flux:button wire:click="agregarPadre2" size="sm" variant="ghost">
                                        + Agregar padre / madre 2
                                    </flux:button>
                                @endif

                                {{-- Tutor designado --}}
                                <div class="rounded-lg bg-fondo-secundario/50 p-4 space-y-2">
                                    <p class="text-sm font-medium text-texto">Tutor designado (se creará su cuenta de usuario)</p>
                                    <div class="flex gap-6">
                                        <label class="flex items-center gap-2 text-sm">
                                            <flux:radio wire:model="tutor_designado" value="padre1" />
                                            Padre / Madre 1
                                        </label>
                                        @if($p2_activo)
                                            <label class="flex items-center gap-2 text-sm">
                                                <flux:radio wire:model="tutor_designado" value="padre2" />
                                                Padre / Madre 2
                                            </label>
                                        @endif
                                    </div>
                                </div>

                            @else
                                {{-- Tutor legal --}}
                                <div class="rounded-lg border border-borde p-4 space-y-3">
                                    <h3 class="text-sm font-medium text-texto">Tutor legal</h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <flux:input wire:model="tl_apellido_paterno" label="Apellido paterno" placeholder="García" />
                                        <flux:input wire:model="tl_apellido_materno" label="Apellido materno" placeholder="López" />
                                    </div>
                                    <flux:input wire:model="tl_nombre" label="Nombre(s)" placeholder="Roberto" />
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <flux:input wire:model="tl_telefono" label="Teléfono" type="tel" placeholder="5512345678" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                                        <flux:input wire:model="tl_telefono_2" label="Teléfono 2 (opcional)" type="tel" placeholder="5512345679" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <flux:input wire:model="tl_email" label="Email (opcional)" placeholder="correo@ejemplo.com" type="email" />
                                        <flux:input wire:model="tl_fecha_nacimiento" label="Fecha de nacimiento" type="date" />
                                    </div>
                                    <flux:input wire:model="tl_domicilio" label="Domicilio" placeholder="Calle y número" />
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Credenciales generadas --}}
                @if($credenciales)
                    <div class="rounded-lg     bg-yellow-50 border border-yellow-200 p-4 space-y-1">
                        <p class="text-sm font-semibold text-yellow-800">⚠ Cuenta de tutor creada</p>
                        <p class="text-sm text-yellow-700">Usuario: <span class="font-mono font-medium">{{ $credenciales['email'] }}</span></p>
                        <p class="text-sm text-yellow-700">Contraseña: <span class="font-mono font-medium">{{ $credenciales['password'] }}</span></p>
                        <p class="text-xs text-yellow-600 mt-1">Anote estas credenciales. No podrá volver a ver la contraseña.</p>
                    </div>
                @endif

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
                    <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
                </div>
            </div>
        </flux:modal>
    </flux:main>
</div>
