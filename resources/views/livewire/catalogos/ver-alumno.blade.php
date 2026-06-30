<div>
    <flux:main class="max-w-3xl mx-auto py-8">
        {{-- Header con acciones --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl">{{ $alumno->persona->nombre }} {{ $alumno->persona->apellido_paterno }} {{ $alumno->persona->apellido_materno }}</flux:heading>
                <flux:text class="text-zinc-500">Matrícula: {{ $alumno->matricula }}</flux:text>
            </div>
            <div class="flex gap-2">
                <flux:button wire:click="irAEditar" variant="primary">
                    Editar
                </flux:button>
                <flux:button variant="ghost" href="{{ route('alumnos.index') }}">
                    ← Volver
                </flux:button>
            </div>
        </div>

        {{-- Sección: Datos del Alumno --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Datos del Alumno</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:label>Nombre(s)</flux:label>
                    <flux:text class="text-zinc-900 font-medium">{{ $alumno->persona->nombre }}</flux:text>
                </div>
                <div>
                    <flux:label>Apellido Paterno</flux:label>
                    <flux:text class="text-zinc-900">{{ $alumno->persona->apellido_paterno }}</flux:text>
                </div>
                <div>
                    <flux:label>Apellido Materno</flux:label>
                    <flux:text class="text-zinc-900">{{ $alumno->persona->apellido_materno ?? '—' }}</flux:text>
                </div>
                <div>
                    <flux:label>CURP</flux:label>
                    <flux:text class="text-zinc-900 font-mono">{{ $alumno->persona->curp ?? '—' }}</flux:text>
                </div>
                <div>
                    <flux:label>Teléfono</flux:label>
                    <flux:text class="text-zinc-900">{{ $alumno->persona->telefono ?? '—' }}</flux:text>
                </div>
                <div>
                    <flux:label>Matrícula</flux:label>
                    <flux:text class="text-zinc-900 font-mono">{{ $alumno->matricula }}</flux:text>
                </div>
            </div>

            <flux:separator class="my-4" />

            <div class="flex gap-4 flex-wrap">
                <div>
                    <flux:label>Grado</flux:label>
                    <flux:badge>{{ $alumno->grado?->nombre }}</flux:badge>
                </div>
                <div>
                    <flux:label>Grupo</flux:label>
                    <flux:badge>{{ $alumno->grupo?->nombre ?? 'Sin asignar' }}</flux:badge>
                </div>
                <div>
                    <flux:label>Estatus</flux:label>
                    <flux:badge color="{{ $alumno->estatus === 'activo' ? 'emerald' : ($alumno->estatus === 'baja' ? 'red' : 'amber') }}">
                        {{ ucfirst($alumno->estatus) }}
                    </flux:badge>
                </div>
            </div>
        </flux:card>

        {{-- Sección: Datos del Tutor --}}
        @if ($familiar = $alumno->familiares->first())
            <flux:card>
                <flux:heading size="lg" class="mb-4">Tutor</flux:heading>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <flux:label>Parentesco</flux:label>
                        <flux:badge>{{ $familiar->parentesco }}</flux:badge>
                    </div>
                    <div>
                        <flux:label>Nombre completo</flux:label>
                        <flux:text class="text-zinc-900">
                            {{ $familiar->persona->nombre }} {{ $familiar->persona->apellido_paterno }} {{ $familiar->persona->apellido_materno ?? '' }}
                        </flux:text>
                    </div>
                    <div>
                        <flux:label>Teléfono</flux:label>
                        <flux:text class="text-zinc-900">{{ $familiar->persona->telefono ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:label>Teléfono 2</flux:label>
                        <flux:text class="text-zinc-900">{{ $familiar->persona->telefono_2 ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:label>Email</flux:label>
                        <flux:text class="text-zinc-900">{{ $familiar->persona->email ?? '—' }}</flux:text>
                    </div>
                    <div>
                        <flux:label>Fecha de nacimiento</flux:label>
                        <flux:text class="text-zinc-900">{{ $familiar->persona->fecha_nacimiento ? $familiar->persona->fecha_nacimiento->format('d/m/Y') : '—' }}</flux:text>
                    </div>
                </div>

                @if ($familiar->persona->domicilio)
                    <flux:separator class="my-4" />
                    <div>
                        <flux:label>Domicilio</flux:label>
                        <flux:text class="text-zinc-900">{{ $familiar->persona->domicilio }}</flux:text>
                    </div>
                @endif
            </flux:card>
        @endif
    </flux:main>

    {{-- Modal de edición reutilizado --}}
    @include('livewire.catalogos.alumnos-form')
</div>
