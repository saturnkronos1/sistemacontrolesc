<flux:modal wire:model="showModal" wire:key="modal-{{ $modalKey }}" :dismissible="false" class="w-full max-w-2xl">
    <div class="space-y-4 max-h-[80vh] overflow-y-auto px-0.5">
        <div>
            <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Alumno</h2>
        </div>

        {{-- Datos del alumno --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:input wire:model="apellido_paterno" label="Apellido paterno" placeholder="GARCÍA" oninput="this.value = this.value.toUpperCase()" />
            <flux:input wire:model="apellido_materno" label="Apellido materno" placeholder="LÓPEZ" oninput="this.value = this.value.toUpperCase()" />
        </div>

        <flux:input wire:model="nombre" label="Nombre(s)" placeholder="JUAN CARLOS" oninput="this.value = this.value.toUpperCase()" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:input wire:model="curp" label="CURP" placeholder="GARC123456HDFRRN08" maxlength="18" class="font-mono uppercase" oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 18)" />
            <flux:input wire:model="telefono" label="Teléfono" type="tel" placeholder="5512345678" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:select wire:model="grado_id" label="Grado">
                <option value="">Seleccionar grado...</option>
                @foreach($this->grados as $grado)
                    <option value="{{ $grado->id }}">{{ $grado->nombre }}</option>
                @endforeach
            </flux:select>
            <flux:select wire:model="grupo_id" label="Grupo" placeholder="Sin grupo">
                <option value="">Sin grupo</option>
                @foreach($this->grupos as $grupo)
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
                            <flux:input wire:model="tutor_apellido_paterno" label="Apellido paterno" placeholder="GARCÍA" oninput="this.value = this.value.toUpperCase()" />
                            <flux:input wire:model="tutor_apellido_materno" label="Apellido materno" placeholder="LÓPEZ" oninput="this.value = this.value.toUpperCase()" />
                        </div>

                        <flux:input wire:model="tutor_nombre" label="Nombre(s)" placeholder="JOSÉ" oninput="this.value = this.value.toUpperCase()" />

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

                        <flux:input wire:model="tutor_domicilio" label="Domicilio" placeholder="CALLE Y NÚMERO" oninput="this.value = this.value.toUpperCase()" />
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
