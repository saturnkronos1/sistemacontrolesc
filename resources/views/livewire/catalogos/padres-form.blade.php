<flux:modal wire:model="showModal" wire:key="modal-{{ $modalKey }}" :dismissible="false" class="w-full max-w-2xl">
    <div class="space-y-4">
        <div>
            <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Padre de Familia</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:input wire:model="nombre" label="Nombre *" placeholder="NOMBRE(S)" oninput="this.value = this.value.toUpperCase()" />
            <flux:input wire:model="apellido_paterno" label="Apellido Paterno *" placeholder="APELLIDO PATERNO" oninput="this.value = this.value.toUpperCase()" />
            <flux:input wire:model="apellido_materno" label="Apellido Materno" placeholder="APELLIDO MATERNO" oninput="this.value = this.value.toUpperCase()" />
            <flux:input wire:model="curp" label="CURP" placeholder="18 caracteres" maxlength="18" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 18)" />
            <flux:input wire:model="telefono" label="Teléfono" type="tel" placeholder="10 dígitos" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
            <flux:input wire:model="telefono_2" label="Teléfono 2" type="tel" placeholder="Teléfono adicional" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
            <flux:input wire:model="email" label="Email" type="email" placeholder="correo@ejemplo.com" />
            <flux:input wire:model="fecha_nacimiento" label="Fecha de Nacimiento" type="date" />
        </div>

        <flux:textarea wire:model="domicilio" label="Domicilio" placeholder="DIRECCIÓN COMPLETA" oninput="this.value = this.value.toUpperCase()" />

        <flux:separator text="Vínculo" />

            <flux:select wire:model="parentesco" label="Parentesco">
                <option value="Padre">Padre</option>
                <option value="Madre">Madre</option>
                <option value="Tutor">Tutor legal</option>
                <option value="Hermano/a">Hermano/a</option>
                <option value="Abuelo/a">Abuelo/a</option>
            </flux:select>

        @if(! $editId)
            {{-- En creación: grupo → alumno --}}
            <flux:select wire:model.live="grupo_id" label="Grupo">
                <option value="">-- Seleccionar grupo --</option>
                @foreach($this->gruposLista as $grupo)
                    <option value="{{ $grupo->id }}">{{ $grupo->grado->nombre }} {{ $grupo->nombre }}</option>
                @endforeach
            </flux:select>

            @if($this->grupo_id)
                <flux:select wire:model="alumno_id" label="Vincular a alumno">
                    <option value="">-- Seleccionar alumno --</option>
                    @foreach($this->alumnosPorGrupo as $alumno)
                        <option value="{{ $alumno->id }}">{{ $alumno->persona->nombreCompleto() }} ({{ $alumno->matricula }})</option>
                    @endforeach
                </flux:select>
                <p class="text-xs text-zinc-500">
                    También puedes vincular padres adicionales desde el módulo de Alumnos, o editar este registro después.
                </p>
            @else
                <p class="text-xs text-zinc-400">Selecciona un grupo para ver los alumnos disponibles.</p>
            @endif
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

                <div class="space-y-3">
                    <flux:select wire:model.live="grupo_id" label="Grupo">
                        <option value="">-- Seleccionar grupo --</option>
                        @foreach($this->gruposLista as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->grado->nombre }} {{ $grupo->nombre }}</option>
                        @endforeach
                    </flux:select>

                    @if($this->grupo_id)
                        <div class="flex gap-2 items-end">
                            <div class="flex-1">
                                <flux:select wire:model="alumno_id" label="Agregar alumno">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($this->alumnosPorGrupo as $alumno)
                                        <option value="{{ $alumno->id }}">{{ $alumno->persona->nombreCompleto() }} ({{ $alumno->matricula }})</option>
                                    @endforeach
                                </flux:select>
                            </div>
                            <flux:button wire:click="agregarVinculo" size="sm" inset="top bottom">Agregar</flux:button>
                        </div>
                    @else
                        <p class="text-xs text-zinc-400">Selecciona un grupo para ver los alumnos disponibles.</p>
                    @endif
                </div>

                @if(count($vinculos) > 0 && collect($vinculos)->some(fn($v) => ! isset($v['id'])))
                    <div class="flex justify-end">
                        <flux:button wire:click="guardarVinculos" size="sm" variant="primary">Guardar vínculos nuevos</flux:button>
                    </div>
                @endif
            </div>
        @endif

        <div class="flex justify-end gap-3 pt-2">
            <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
        </div>
    </div>
</flux:modal>
