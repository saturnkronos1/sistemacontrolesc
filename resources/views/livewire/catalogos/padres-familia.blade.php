<div>
    <flux:main>
        <x-page-header title="Padres de Familia" />

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold lg:hidden">Padres de Familia</h1>
            <flux:button wire:click="crear" variant="primary">
                Nuevo Padre
            </flux:button>
        </div>

        {{-- Búsqueda --}}
        <div class="mb-4 max-w-sm">
            <flux:input wire:model.live="search" placeholder="Buscar por nombre, email o CURP..." icon="magnifying-glass" />
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
                        <th wire:click="sortBy('email')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase cursor-pointer select-none hover:text-texto whitespace-nowrap">
                            <div class="flex items-center gap-1">
                                Email
                                <x-sort-indicator :field="'email'" :sort-field="$sortField" :sort-direction="$sortDirection" />
                            </div>
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">
                            Teléfono
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">
                            Parentesco
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">
                            Hijos
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase whitespace-nowrap">
                            Cuenta
                        </th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-borde bg-white">
                    @forelse($padres as $padre)
                        <tr class="hover:bg-hover">
                            <td class="px-4 py-3 text-sm font-medium">
                                {{ $padre->nombreCompleto() }}
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $padre->email ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">{{ $padre->telefono ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $parentescos = $padre->familiares->pluck('parentesco')->unique()->implode(', ');
                                @endphp
                                {{ $parentescos ?: '—' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $padre->familiares->count() }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($padre->user)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 ">
                                        <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg>
                                        Sí
                                    </span>
                                @else
                                    <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <flux:button wire:click="editar({{ $padre->id }})" size="sm" inset="top bottom">Editar</flux:button>
                                <flux:button wire:click="eliminar({{ $padre->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Desvincular este padre de familia? Se eliminarán sus vínculos con alumnos pero se conservará su registro.">Desvincular</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-zinc-500">
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

        {{-- Modal --}}
        <flux:modal wire:model="showModal" class="w-full max-w-2xl">
            <div class="space-y-4">
                <div>
                    <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Padre de Familia</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <flux:input wire:model="nombre" label="Nombre *" placeholder="Nombre(s)" />
                    <flux:input wire:model="apellido_paterno" label="Apellido Paterno *" placeholder="Apellido paterno" />
                    <flux:input wire:model="apellido_materno" label="Apellido Materno" placeholder="Apellido materno" />
                    <flux:input wire:model="curp" label="CURP" placeholder="18 caracteres" maxlength="18" />
                    <flux:input wire:model="telefono" label="Teléfono" type="tel" placeholder="10 dígitos" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                    <flux:input wire:model="telefono_2" label="Teléfono 2" type="tel" placeholder="Teléfono adicional" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
                    <flux:input wire:model="email" label="Email" type="email" placeholder="correo@ejemplo.com" />
                    <flux:input wire:model="fecha_nacimiento" label="Fecha de Nacimiento" type="date" />
                </div>

                <flux:textarea wire:model="domicilio" label="Domicilio" placeholder="Dirección completa" />

                <flux:separator text="Vínculo" />

                <flux:select wire:model="parentesco" label="Parentesco">
                    <option value="Padre">Padre</option>
                    <option value="Madre">Madre</option>
                    <option value="Tutor">Tutor legal</option>
                </flux:select>

                @if(! $editId)
                    {{-- En creación: selector simple de alumno --}}
                    <flux:select wire:model="alumno_id" label="Vincular a alumno (opcional)">
                        <option value="">-- Seleccionar alumno --</option>
                        @foreach($alumnos as $alumno)
                            <option value="{{ $alumno->id }}">{{ $alumno->persona->nombreCompleto() }} ({{ $alumno->matricula }})</option>
                        @endforeach
                    </flux:select>

                    <p class="text-xs text-zinc-500">
                        También puedes vincular padres a alumnos desde el módulo de Alumnos, o editar este registro después para agregar más vínculos.
                    </p>
                @else
                    {{-- En edición: lista de vínculos actuales --}}
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-texto">Alumnos vinculados</label>

                        @if(count($vinculos) > 0)
                            <div class="divide-y divide-borde border rounded-lg border-borde">
                                @foreach($vinculos as $index => $vinculo)
                                    <div class="flex items-center justify-between px-3 py-2 text-sm">
                                        <span>{{ $vinculo['alumno_nombre'] }} ({{ $vinculo['parentesco'] }})</span>
                                        <flux:button wire:click="quitarVinculo({{ $index }})" size="xs" variant="danger" wire:confirm="¿Desvincular este alumno?">Quitar</flux:button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-zinc-400">Sin alumnos vinculados.</p>
                        @endif

                        <div class="flex gap-2 items-end">
                            <div class="flex-1">
                                <flux:select wire:model="alumno_id" label="Agregar alumno">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($alumnos as $alumno)
                                        <option value="{{ $alumno->id }}">{{ $alumno->persona->nombreCompleto() }} ({{ $alumno->matricula }})</option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <flux:button wire:click="agregarVinculo" size="sm" inset="top bottom">Agregar</flux:button>
                        </div>

                        @if(count($vinculos) > 0 && collect($vinculos)->some(fn($v) => ! isset($v['id'])))
                            <div class="flex justify-end">
                                <flux:button wire:click="guardarVinculos" size="sm" variant="primary">Guardar vínculos nuevos</flux:button>
                            </div>
                        @endif
                    </div>
                @endif

                <flux:separator text="Cuenta de usuario (tutor)" />

                <flux:checkbox wire:model="crear_cuenta" label="Crear cuenta de tutor" />

                @if($crear_cuenta)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <flux:input wire:model="password" label="{{ $editId ? 'Nueva contraseña (dejar vacío para mantener)' : 'Contraseña *' }}" type="password" viewable />
                        <flux:input wire:model="password_confirmation" label="Confirmar contraseña" type="password" viewable />
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
