<div>
    <flux:main class="max-w-3xl mx-auto py-8">
        {{-- Header con acciones --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <flux:heading size="xl">{{ $padre->nombre }} {{ $padre->apellido_paterno }} {{ $padre->apellido_materno }}</flux:heading>
            </div>
            <div class="flex gap-2">
                <flux:button wire:click="irAEditar" variant="primary">
                    Editar
                </flux:button>
                <flux:button variant="ghost" href="{{ route('padres-familia.index') }}">
                    ← Volver
                </flux:button>
            </div>
        </div>

        {{-- Sección: Datos del Tutor --}}
        <flux:card class="mb-6">
            <flux:heading size="lg" class="mb-4">Datos del Tutor</flux:heading>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <flux:label>Nombre(s)</flux:label>
                    <flux:text class="text-zinc-900 font-medium">{{ $padre->nombre }}</flux:text>
                </div>
                <div>
                    <flux:label>Apellido Paterno</flux:label>
                    <flux:text class="text-zinc-900">{{ $padre->apellido_paterno }}</flux:text>
                </div>
                <div>
                    <flux:label>Apellido Materno</flux:label>
                    <flux:text class="text-zinc-900">{{ $padre->apellido_materno ?? '—' }}</flux:text>
                </div>
                <div>
                    <flux:label>CURP</flux:label>
                    <flux:text class="text-zinc-900 font-mono">{{ $padre->curp ?? '—' }}</flux:text>
                </div>
                <div>
                    <flux:label>Email</flux:label>
                    <flux:text class="text-zinc-900">{{ $padre->email ?? '—' }}</flux:text>
                </div>
                <div>
                    <flux:label>Teléfono</flux:label>
                    <flux:text class="text-zinc-900">{{ $padre->telefono ?? '—' }}</flux:text>
                </div>
                <div>
                    <flux:label>Teléfono 2</flux:label>
                    <flux:text class="text-zinc-900">{{ $padre->telefono_2 ?? '—' }}</flux:text>
                </div>
                <div>
                    <flux:label>Fecha de Nacimiento</flux:label>
                    <flux:text class="text-zinc-900">{{ $padre->fecha_nacimiento ? $padre->fecha_nacimiento->format('d/m/Y') : '—' }}</flux:text>
                </div>
            </div>

            @if ($padre->domicilio)
                <flux:separator class="my-4" />
                <div>
                    <flux:label>Domicilio</flux:label>
                    <flux:text class="text-zinc-900">{{ $padre->domicilio }}</flux:text>
                </div>
            @endif

            @if ($padre->user)
                <flux:separator class="my-4" />
                <div>
                    <flux:label>Cuenta de usuario</flux:label>
                    <flux:badge color="emerald">Activa ({{ $padre->user->email }})</flux:badge>
                </div>
            @endif
        </flux:card>

        {{-- Sección: Alumnos Vinculados --}}
        <flux:card>
            <flux:heading size="lg" class="mb-4">Alumnos Vinculados</flux:heading>

            @if ($padre->familiares->isNotEmpty())
                <div class="overflow-x-auto rounded-lg border border-borde">
                    <table class="min-w-full divide-y divide-borde">
                        <thead class="bg-tabla-encabezado">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Matrícula</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Nombre</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Grado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Grupo</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 uppercase">Parentesco</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-borde bg-white">
                            @foreach($padre->familiares as $vinculo)
                                <tr class="hover:bg-hover">
                                    <td class="px-4 py-3 text-sm font-mono">{{ $vinculo->alumno->matricula }}</td>
                                    <td class="px-4 py-3 text-sm font-medium">{{ $vinculo->alumno->persona->nombreCompleto() }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $vinculo->alumno->grado?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm">{{ $vinculo->alumno->grupo?->nombre ?? '—' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <flux:badge>{{ $vinculo->parentesco }}</flux:badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-zinc-400">Este padre de familia no tiene alumnos vinculados.</p>
            @endif
        </flux:card>
    </flux:main>

    {{-- Modal de edición reutilizado --}}
    @include('livewire.catalogos.padres-form')
</div>
