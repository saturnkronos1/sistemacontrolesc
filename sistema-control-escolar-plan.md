# Plan de Implementación — Sistema de Control Escolar

**Versión:** 2.0 (actualización post-implementación Fases 0–4)
**Stack:** Laravel 13.7 + Livewire 4.1 + Flux UI 2.13 + Fortify 1.37 + Spatie Permission 7.4 + Tailwind CSS v4 + Alpine.js + MySQL + DomPDF 3.1 + Pest 4
**Generator:** `composer create-project laravel/laravel:^13.0` (Livewire Starter Kit con Flux + Fortify)

---

## TABLA DE CONTENIDOS
1. [Stack real y decisiones de frontend](#1-stack-real)
2. [Arquitectura del layout](#2-arquitectura-del-layout)
3. [Sistema de toasts](#3-sistema-de-toasts)
4. [Patrón de componentes CRUD Livewire](#4-patron-crud-livewire)
5. [Estructura de directorios actual](#5-estructura-de-directorios)
6. [Modelos y relaciones](#6-modelos-y-relaciones)
7. [Permisos (Spatie)](#7-permisos)
8. [Menú lateral (NavSidebar)](#8-menu-lateral-navsidebar)
9. [Seeders y orden](#9-seeders)
10. [Tests](#10-tests)
11. [Fases de implementación con estado real](#11-fases-de-implementacion)
12. [Decisiones de diseño registradas](#12-decisiones-de-diseno)
13. [Flujo de replicación del proyecto](#13-flujo-de-replicacion)

---

## 1. Stack real

> **IMPORTANTE:** El plan original planteaba "0 Flux, 0 Bootstrap — layout 100% artesanal con Tailwind". En la implementación real se optó por **Flux UI 2** para los componentes de contenido (tablas, modales, botones, inputs, selects) manteniendo el layout general (sidebar, topbar, toast) con Alpine.js + Tailwind puro.

| Componente | Decisión real | Justificación |
|---|---|---|
| **CSS** | Tailwind CSS v4 | Renderiza clases utilitarias rápidas |
| **Framework UI (contenido)** | Flux UI 2 (`livewire/flux:^2.13`) | Modales, botones, inputs, selects listos. Ahorra cientos de líneas de HTML+Alpine por componente |
| **Layout (sidebar/topbar)** | Tailwind + Alpine.js puro | El sidebar, topbar mobile, y user dropdown son 100% artesanales con Alpine. Sin Flux. |
| **Toast** | Alpine.js + window events | Sistema inline en `sidebar.blade.php`. Escucha `window.addEventListener('toast', ...)` |
| **Iconos** | SVG Heroicons outline inline | En NavSidebar y layout, inline en los blades. Sin npm packages extra. |
| **Auth** | Fortify (starter kit) | Login, 2FA, passkeys, email verification incluidos |
| **Teams** | Jetstream-style teams | Starter Kit trae teams. El sistema escolar usa rutas FUERA del wrapper de teams. |
| **Dark mode** | Sí (Tailwind `dark:`) | El layout tiene `class="dark"` en `<html>`. Soporte nativo. |

### Packages clave (composer.json)

```json
{
    "php": "^8.3",
    "laravel/framework": "^13.7",
    "livewire/livewire": "^4.1",
    "livewire/flux": "^2.13.1",
    "laravel/fortify": "^1.37.2",
    "spatie/laravel-permission": "^7.4",
    "barryvdh/laravel-dompdf": "^3.1"
}
```

---

## 2. Arquitectura del layout

### Vista base: `resources/views/layouts/app/sidebar.blade.php`

NO usa `flux:sidebar` — es un layout **100% artesanal con Alpine.js** que incluye:

```
┌─────────────────────────────────────────────────────────┐
│  DESKTOP (lg+)                                          │
│  ┌──────────┐  ┌──────────────────────────────────────┐ │
│  │ SIDEBAR  │  │  MAIN CONTENT                         │ │
│  │ (w-64)   │  │  <main class="p-6 max-w-7xl mx-auto"> │ │
│  │ fixed    │  │    {{ $slot }}                         │ │
│  │          │  │    (usa Flux components acá)           │ │
│  │ [Logo]   │  │                                        │ │
│  │ [Rol]    │  │                                        │ │
│  │ <nav>    │  │                                        │ │
│  │  sidebar │  │                                        │ │
│  │ [User▼]  │  │                                        │ │
│  └──────────┘  └──────────────────────────────────────┘ │
│                                                          │
│  MOBILE (<lg)                                            │
│  ┌──────────────────────────────────────────────────┐   │
│  │ [☰] [Logo]                    [Avatar ▼]         │   │ ← sticky
│  ├──────────────────────────────────────────────────┤   │
│  │              {{ $slot }}                          │   │
│  └──────────────────────────────────────────────────┘   │
│                                                          │
│  ☰ → sidebar como overlay con backdrop oscuro            │
│  └── Alpine: x-data="layout()", @click toggle            │
└──────────────────────────────────────────────────────────┘
```

### Componentes Alpine.js en el layout

```blade
{{-- Layout controller --}}
<div x-data="layout()" x-init="init()">
    {{-- sidebarOpen toggle, cierra en navegación --}}
</div>

{{-- User dropdown (desktop & mobile) --}}
<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" @click.away="open = false">...</button>
</div>

{{-- Toast system --}}
<div x-data="toast()" x-init="init()">
    <template x-for="(t, i) in toasts" :key="i">
        <div x-show="t.visible" x-transition ...>
            <span x-text="t.message"></span>
        </div>
    </template>
</div>
```

### JS functions inline (sidebar.blade.php)

```javascript
function layout() {
    return {
        sidebarOpen: false,
        init() {
            Livewire?.on('$navigate', () => { this.sidebarOpen = false; });
        }
    };
}

function toast() {
    return {
        toasts: [],
        init() {
            window.addEventListener('toast', (e) => {
                this.toasts.push({ message: e.detail.message, type: e.detail.type, visible: true });
                setTimeout(() => { /* auto-hide + remove */ }, e.detail.duration || 4000);
            });
        },
        remove(index) { /* fade + remove */ }
    };
}
```

### Cómo se usa en las vistas

TODOS los componentes Livewire (catalogos, docentes, grupos, etc.) usan este wrapper:

```blade
<div>
    <x-layouts::app.sidebar>
        <flux:main>
            {{-- contenido del componente con Flux UI --}}
        </flux:main>
    </x-layouts::app.sidebar>
</div>
```

> `<flux:main>` es el wrapper de Flux que da padding y max-width al contenido. El sidebar layout externo es Alpine puro; el contenido interno usa Flux.

---

## 3. Sistema de toasts

**Pattern:** Livewire dispatch → Alpine escucha vía window event.

```php
// En cualquier componente Livewire:
$this->dispatch('toast', message: 'Texto del mensaje', type: 'success');
// type: 'success' (verde) | 'error' (rojo) | 'info' (azul)
```

El Alpine component en `sidebar.blade.php` escucha `window.addEventListener('toast', ...)` y muestra el toast con animación slide-in desde la derecha, auto-hide a los 4 segundos.

---

## 4. Patrón de componentes CRUD Livewire

Todos los CRUD de catálogos siguen EXACTAMENTE este patrón:

### PHP Class (`app/Livewire/Catalogos/<Nombre>.php`)

```php
<?php

namespace App\Livewire\Catalogos;

use App\Models\<Modelo>;
use Livewire\Component;
use Livewire\WithPagination;

class <Nombre> extends Component
{
    use WithPagination;

    public $showModal = false;     // controla visibilidad del modal
    public $editId = null;         // null = crear, int = editar
    // ... campos del formulario, inicializados como '' ...

    public string $sortField = 'nombre';       // columna por defecto
    public string $sortDirection = 'asc';
    public string $search = '';                 // búsqueda wire:model.live

    protected function rules() { /* reglas de validación */ }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $query = <Modelo>::query();
        // Filtros + búsqueda
        return view('livewire.catalogos.<nombre>', [
            'data' => $query->orderBy($this->sortField, $this->sortDirection)->paginate(15),
            'relaciones' => ...,
        ]);
    }

    public function crear()   { $this->resetModal(); $this->showModal = true; }
    public function editar($id) { /* carga datos en propiedades */ }
    public function guardar() { $this->validate(); /* updateOrCreate */ $this->dispatch('toast', ...); $this->resetModal(); }
    public function eliminar($id) { /* findOrFail + delete */ $this->dispatch('toast', ...); }
    public function resetModal() { /* limpia TODAS las propiedades */ }
}
```

### Blade View (`resources/views/livewire/catalogos/<nombre>.blade.php`)

```blade
<div>
    <x-layouts::app.sidebar>
        <flux:main>
            {{-- Header: título + botón Nuevo --}}
            {{-- Filtros y búsqueda --}}
            {{-- Tabla responsive con overflow-x-auto --}}
            {{-- Sort en encabezados (wire:click="sortBy('campo')") --}}
            {{-- Forelse + empty state --}}
            {{-- Paginación --}}
            {{-- Modal Flux UI para crear/editar --}}
        </flux:main>
    </x-layouts::app.sidebar>
</div>
```

### Patrón de tabla responsiva

```blade
<div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
```

### Patrón de búsqueda

```blade
<flux:input wire:model.live="search" placeholder="Buscar..." icon="magnifying-glass" />
```

Sin debounce (`wire:model.live` a secas) para respuesta inmediata. Excepción: Materias no tiene búsqueda por decisión del usuario.

### Patrón de filtros

```blade
<flux:select wire:model.live="filtro_ciclo" placeholder="Todos...">
    @foreach($items as $item)
        <option value="{{ $item->id }}">{{ $item->nombre }}</option>
    @endforeach
</flux:select>
```

### Patrón de sort en encabezados

```blade
<th wire:click="sortBy('nombre')" class="... cursor-pointer select-none ...">
    <div class="flex items-center gap-1">
        Nombre
        @if($sortField === 'nombre')
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                @if($sortDirection === 'asc')
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5"/>
                @else
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                @endif
            </svg>
        @endif
    </div>
</th>
```

### Patrón de acciones por fila

```blade
<td class="px-4 py-3 text-right whitespace-nowrap">
    <flux:button wire:click="editar({{ $item->id }})" size="sm" inset="top bottom">Editar</flux:button>
    <flux:button wire:click="eliminar({{ $item->id }})" size="sm" variant="danger" inset="top bottom" wire:confirm="¿Eliminar?">Eliminar</flux:button>
</td>
```

### Patrón de modal Flux UI

```blade
<flux:modal wire:model="showModal" class="w-full max-w-lg">
    <div class="space-y-4">
        <h2 class="text-lg font-semibold">{{ $editId ? 'Editar' : 'Nuevo' }} Título</h2>
        {{-- flux:input, flux:select --}}
        <div class="flex justify-end gap-3 pt-2">
            <flux:button wire:click="resetModal" variant="ghost">Cancelar</flux:button>
            <flux:button wire:click="guardar" variant="primary">Guardar</flux:button>
        </div>
    </div>
</flux:modal>
```

---

## 5. Estructura de directorios

```
app/
├── Actions/                          (no implementado aún)
├── Livewire/
│   ├── NavSidebar.php                ← menú lateral dinámico
│   ├── Catalogos/
│   │   ├── CiclosEscolares.php       ✅
│   │   ├── PeriodosEvaluacion.php    ✅
│   │   ├── Materias.php              ✅ (sin búsqueda)
│   │   ├── Usuarios.php              ✅ (con foto perfil + roles)
│   │   ├── Grupos.php                ✅ (con filtros + docente FK)
│   │   ├── Docentes.php              ✅ (siempre rol Docente)
│   │   ├── Alumnos.php               ✅ (CRUD con Persona, estatus, grupo)
│   │   ├── Calificaciones.php        ✅ (captura por grupo/materia/periodo)
│   │   ├── Asistencia.php            ✅ (pase de lista diario)
│   │   ├── Boleta.php                ✅ (vista previa + PDF DomPDF)
│   │   ├── Reinscripciones.php       ✅ (cambio de ciclo escolar)
│   │   ├── TutorDashboard.php        ✅ (dashboard tutor con 3 vistas)
│   │   └── Reportes.php              ✅ (4 reportes: concentrado, kardex, inasistencias, alumnos-por-tutor)
│   └── ...
├── Models/
│   ├── Grado.php                     ✅ 13 modelos de dominio
│   ├── CicloEscolar.php              ✅
│   ├── PeriodoEvaluacion.php         ✅
│   ├── Materia.php                   ✅
│   ├── Grupo.php                     ✅
│   ├── Alumno.php                    ✅ (con relaciones completas)
│   ├── Persona.php                   ✅
│   ├── AlumnoFamilia.php             ✅
│   ├── Calificacion.php              ✅
│   ├── CalificacionLog.php           ✅
│   ├── Asistencia.php                ✅
│   ├── Justificante.php              ✅
│   ├── BoletaObservacion.php         ✅
│   └── (Team, TeamInvitation, Membership — del starter kit)
├── Observers/                        🔲 (CalificacionObserver pendiente)
├── Services/                         🔲 (pendiente)
└── Http/Middleware/                  🔲 (CicloActivoMiddleware pendiente)

resources/
├── views/
│   ├── layouts/
│   │   ├── app/
│   │   │   └── sidebar.blade.php     ← layout ppal (Alpine+Tailwind puro)
│   │   ├── app.blade.php             ← usa sidebar.blade.php
│   │   └── guest.blade.php
│   ├── livewire/
│   │   ├── nav-sidebar.blade.php     ← items del menú
│   │   ├── catalogos/
│   │   │   ├── ciclos-escolares.blade.php   ✅
│   │   │   ├── periodos-evaluacion.blade.php ✅
│   │   │   ├── materias.blade.php           ✅
│   │   │   ├── usuarios.blade.php           ✅
│   │   │   ├── grupos.blade.php             ✅
│   │   │   ├── docentes.blade.php           ✅
│   │   │   ├── alumnos.blade.php            ✅
│   │   │   ├── calificaciones.blade.php     ✅
│   │   │   ├── asistencia.blade.php         ✅
│   │   │   ├── boleta.blade.php             ✅
│   │   │   └── reinscripciones.blade.php    ✅
│   │   ├── tutor-dashboard.blade.php       ✅
│   │   └── reportes.blade.php             ✅
│   └── pdf/
│       ├── boleta.blade.php          ✅ (template PDF DomPDF)
│       ├── concentrado.blade.php     ✅
│       ├── kardex.blade.php          ✅
│       ├── inasistencias.blade.php   ✅
│       └── alumnos-por-tutor.blade.php ✅

database/
├── migrations/
│   ├── 0001_01_01_* (users, cache, jobs)  ← Laravel base
│   ├── 2024_01_01_000000_create_passkeys_table.php
│   ├── 2025_08_14_170933_add_two_factor_columns_to_users_table.php
│   ├── 2026_01_27_* (teams)               ← Starter Kit
│   ├── 2026_05_30_153125_create_permission_tables.php  ← Spatie
│   ├── 2026_05_30_191311_add_foto_perfil_to_users_table.php
│   ├── 2026_05_30_192244_create_grados_table.php
│   ├── 2026_05_30_192244_create_ciclos_escolares_table.php
│   ├── 2026_05_30_192250_create_materias_table.php
│   ├── 2026_05_30_192250_create_periodos_evaluacion_table.php
│   ├── 2026_05_30_192301_create_alumnos_table.php
│   ├── 2026_05_30_192302_create_alumno_familia_table.php
│   ├── 2026_05_30_192305_create_grupos_table.php
│   ├── 2026_05_30_192316_create_calificaciones_table.php
│   ├── 2026_05_30_192316_create_asistencias_table.php
│   ├── 2026_05_30_192329_create_calificacion_logs_table.php
│   ├── 2026_05_30_192329_create_justificantes_table.php
│   └── 2026_05_30_192331_create_boleta_observaciones_table.php
├── seeders/
│   ├── DatabaseSeeder.php            ← llama a todos en orden
│   ├── RolePermissionSeeder.php      ← roles + permisos
│   ├── GradoSeeder.php               ← 1° a 6°
│   ├── AdminSeeder.php               ← admin@admin.com / password
│   ├── CicloEscolarSeeder.php        ← 2024-2025 y 2025-2026
│   ├── MateriasSeeder.php            ← materias por grado
│   ├── PeriodoEvaluacionSeeder.php   ← 3 periodos
│   └── GrupoSeeder.php               ← docentes + grupos

routes/
├── modules.php                       ← todas las rutas del sistema (fuera de teams)
├── web.php                           ← rutas base + teams
├── fortify.php                       ← Fortify actions
├── ...

tests/
├── Feature/
│   ├── Auth/
│   │   ├── AuthenticationTest.php
│   │   ├── RoleAccessTest.php        ← 11 tests: permisos por rol
│   │   └── ...
│   ├── Catalogos/
│   │   └── CatalogosTest.php         ← 31 tests: CRUDs + permisos de catálogos
│   └── ... (Teams, Settings, etc.)
└── Pest.php                          ← RefreshDatabase global para Feature tests
```

---

## 6. Modelos y relaciones

```
User (HasRoles, foto_perfil, teams)
├── hasMany Grupo (docente_id)        ← docente a cargo del grupo
├── hasMany Asistencia (created_by)
├── hasMany CalificacionLog
├── hasMany BoletaObservacion
└── hasOne Persona (tutor)

Grado                                  ← 1°, 2°, ... 6°
├── hasMany Materia
└── hasMany Grupo

CicloEscolar                           ← 2024-2025, 2025-2026
├── hasMany PeriodoEvaluacion
├── hasMany Grupo
├── hasMany Alumno
└── hasMany BoletaObservacion

Materia
└── belongsTo Grado

Grupo                                  ← 1°A, 1°B, etc.
├── belongsTo Grado
├── belongsTo CicloEscolar
├── belongsTo User (docente, nullable)
├── hasMany Alumno                     ← alumnos inscritos
├── hasMany Calificacion
├── hasMany Asistencia
└── hasMany BoletaObservacion

Alumno
├── belongsTo Grupo
├── belongsTo CicloEscolar
├── hasMany Calificacion
├── hasMany Asistencia
├── hasMany BoletaObservacion
└── belongsToMany Persona (via AlumnoFamilia)

Persona                                ← padres/tutores
├── belongsTo User (tutor, nullable)
└── belongsToMany Alumno (via AlumnoFamilia)

AlumnoFamilia                          ← pivot alumno ↔ persona
├── belongsTo Alumno
├── belongsTo Persona
└── parentesco                         ← string: padre, madre, tutor

Calificacion
├── belongsTo Alumno
├── belongsTo Grupo                    ← para consistencia
├── belongsTo Materia
├── belongsTo PeriodoEvaluacion
└── hasMany CalificacionLog

CalificacionLog                        ← auditoría
├── belongsTo Calificacion
└── belongsTo User (quién modificó)

Asistencia
├── belongsTo Alumno
├── belongsTo Grupo
├── belongsTo User (created_by)
├── estado                             ← enum: presente, falta, justificado
└── hasOne Justificante

Justificante
├── belongsTo Asistencia
├── archivo                            ← string: ruta al archivo
└── belongsTo User (validado_por, nullable)

BoletaObservacion
├── belongsTo Alumno
├── belongsTo Grupo
├── belongsTo CicloEscolar
├── belongsTo PeriodoEvaluacion
└── belongsTo User (created_by)
```

---

## 7. Permisos

Definidos en `database/seeders/RolePermissionSeeder.php`. Estructura:

```php
$permissionsByModule = [
    'Catalogos' => ['listar', 'crear', 'editar', 'eliminar'],
    'Docentes' => ['listar', 'crear', 'editar', 'eliminar'],       ← NUEVO
    'Grupos' => ['listar', 'crear', 'editar', 'asignar-docente'],
    'Alumnos' => ['listar', 'inscribir', 'editar', 'dar-baja', 'dar-egreso'],
    'Calificaciones' => ['capturar', 'ver-reporte'],
    'Asistencia' => ['pasar-lista', 'ver-reporte', 'subir-justificante', 'validar-justificante'],
    'Boleta' => ['generar', 'descargar'],
    'Reinscripciones' => ['reinscribir', 'listar'],
    'Reportes' => ['concentrado', 'kardex', 'inasistencias', 'alumnos-por-tutor'],
    'Tutor' => ['dashboard', 'ver-calificaciones', 'ver-asistencia', 'descargar-boleta'],
    'Usuarios' => ['listar', 'crear', 'editar', 'eliminar'],
];
```

El nombre del permiso se genera como `strtolower($module).".".$action`:
- `catalogos.listar`, `docentes.listar`, `grupos.asignar-docente`, etc.

### Asignación por rol

| Módulo | Superadmin | Director | Subdirector | Docente | Tutor |
|--------|:----------:|:--------:|:-----------:|:-------:|:-----:|
| catalogos.* | ✅ | — | — | — | — |
| docentes.* | ✅ | ✅ | — | — | — |
| grupos.* | ✅ | ✅ | — | — | — |
| alumnos.* | ✅ | ✅ | — | — | — |
| calificaciones.* | ✅ | ✅ | ✅ | ✅ | — |
| asistencia.* | ✅ | ✅ | ✅ | ✅ | — |
| boleta.* | ✅ | ✅ | ✅ | ✅ | ✅ |
| reinscripciones.* | ✅ | ✅ | — | — | — |
| reportes.* | ✅ | ✅ | ✅ | — | — |
| tutor.* | ✅ | — | — | — | ✅ |
| usuarios.* | ✅ | — | — | — | — |
| dashboard | ✅ | ✅ | ✅ | ✅ | ✅ |

> **Importante:** Superadmin recibe `$allPermissions` (todos los permisos existentes) automáticamente. No necesita entrada explícita por módulo.

---

## 8. Menú lateral (NavSidebar)

### Componente PHP: `app/Livewire/NavSidebar.php`

Estructura de menú con grupos y items. Cada item tiene:
- `label`, `route`, `route_prefix`, `params`, `svg` (heroicon inline), `visible` (permiso)

```php
'General' => ['Dashboard' → dashboard]
'Catálogos' => ['Ciclos Escolares', 'Periodos Evaluación', 'Materias', 'Usuarios']
'Académico' => ['Docentes', 'Grupos', 'Alumnos', 'Calificaciones', 'Asistencia', 'Reinscripciones']
'Reportes' => ['Boleta', 'Reportes']
'Tutor' => ['Tutor Dashboard']
```

### Filtrado de visibilidad

El NavSidebar filtra items por permiso en `mount()`:
```php
$this->menuGroups = array_map(function ($items) {
    return array_values(array_filter($items, fn ($item) => $item['visible']));
}, $groups);
```

Si un grupo se queda sin items visibles, no se renderiza (control en Blade: `@if(count($items))`).

### Active state

Se determina por `route_prefix`: si la ruta actual empieza con el prefijo, el item se marca como activo.
```blade
$isActive = $currentRoute && str_starts_with($currentRoute, $item['route_prefix']);
```

---

## 9. Seeders

Orden de ejecución en `DatabaseSeeder::run()`:

```php
$this->call([
    RolePermissionSeeder::class,         // roles + permisos (Spatie)
    GradoSeeder::class,                  // 1° a 6° de primaria
    AdminSeeder::class,                  // admin@admin.com / password
    CicloEscolarSeeder::class,           // 2024-2025 (activo) y 2025-2026
    MateriasSeeder::class,               // materias por grado
    PeriodoEvaluacionSeeder::class,      // 3 periodos por ciclo
    GrupoSeeder::class,                  // docentes de prueba + 6 grupos
]);
```

### Detalle de seeders implementados

**RolePermissionSeeder**: Crea 5 roles (Superadmin, Director, Subdirector, Docente, Tutor) y todos los permisos por módulo. Asigna permisos a cada rol.

**GradoSeeder**: `1°`, `2°`, `3°`, `4°`, `5°`, `6°` — grados de primaria.

**AdminSeeder**: `admin@admin.com` / `password` con rol Superadmin. Crea un personal team automáticamente (el Starter Kit lo requiere).

**CicloEscolarSeeder**: Dos ciclos: `2024-2025` (activo=true), `2025-2026` (activo=false). Verifica que solo UNO tenga activo=true.

**MateriasSeeder**: 4 materias por grado (Español, Matemáticas, Conocimiento del Medio, Formación Cívica y Ética para 1°-2°; Español, Matemáticas, Ciencias Naturales, Geografía/Historia/FCyE para 3°-6°). Total ~24 materias.

**PeriodoEvaluacionSeeder**: 3 periodos (1er Trimestre, 2do Trimestre, 3er Trimestre) por ciclo escolar activo.

**GrupoSeeder**: Crea 3 usuarios con rol Docente (María García, Juan Pérez, Laura Martínez), luego crea 6 grupos (1°A-6°A) asignados al ciclo activo, cada uno con un docente diferente.

---

## 10. Tests

### Configuración global (`tests/Pest.php`)

```php
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
```

TODOS los tests en `tests/Feature/` tienen RefreshDatabase automático.

### Tests del sistema escolar

| Archivo | Tests | Estado |
|---------|-------|--------|
| `tests/Feature/Auth/RoleAccessTest.php` | 11 tests: permisos por rol y acceso a rutas | ✅ Todos verdes |
| `tests/Feature/Catalogos/CatalogosTest.php` | 46 tests: CRUD + grupos + docentes + alumnos + calificaciones + asistencia + boleta + reinscripciones | ✅ Todos verdes |
| `tests/Feature/Tutor/TutorTest.php` | 7 tests: acceso, hijos, calificaciones, asistencias, volver | ✅ Todos verdes |
| `tests/Feature/Catalogos/ReportesTest.php` | 15 tests: 4 reportes con filtros + PDF | ✅ Todos verdes |

**Tests de catálogos (46 tests):**

- **Catálogos base (6):** Guest redirect (5) + page loads + data display + CRUD
- **Grupos + Docentes (6):** Guest redirect, page load, list display, filtro por rol
- **Alumnos (4):** Guest redirect, page load, list display, filtro estatus, superadmin access
- **Calificaciones (7):** Guest redirect, page load, grupos en select, filtro por docente, carga materias, carga notas existentes, guardar + log
- **Asistencia (7):** Guest redirect, page load, filtro por docente, carga alumnos, guardar estatus, justificante con motivo, superadmin access
- **Boleta (5):** Guest redirect, page load, grupos en select, carga alumnos, carga calificaciones
- **Reinscripciones (4):** Guest redirect, page load, carga alumnos, reinscribir a nuevo grupo

**Tests pendientes para fases futuras:**
- Ninguno

---

## 11. Fases de implementación

### Fase 0: Setup del proyecto ✅ COMPLETADA

| # | Tarea | Estado | Notas |
|---|---|---|---|
| 0.1 | Crear proyecto | ✅ | `composer create-project laravel/laravel:^13.0` (Livewire Starter Kit) |
| 0.2 | Auth + Fortify + Livewire | ✅ | Incluido en Starter Kit |
| 0.3 | Alpine.js | ✅ | Incluido con Livewire |
| 0.4 | Instalar Spatie | ✅ | `composer require spatie/laravel-permission` |
| 0.5 | Migraciones Spatie | ✅ | `vendor:publish` |
| 0.6 | HasRoles en User | ✅ | `use Spatie\Permission\Traits\HasRoles;` |
| 0.7 | Compilar assets | ✅ | `npm install && npm run build` |
| 0.8 | Instalar DomPDF | ✅ | `composer require barryvdh/laravel-dompdf` |
| 0.9 | Migración foto_perfil | ✅ | `$table->string('foto_perfil')->nullable()->after('email');` |
| 0.10 | 13 migraciones de dominio | ✅ | Ver listado en estructura de directorios |
| 0.11 | Todos los Eloquent Models | ✅ | 13 modelos con relaciones, casts, fillable |
| 0.12 | Seeders iniciales | ✅ | RolePermission, Grado, Admin |

### Fase 1: Auth + Roles + Layout ✅ COMPLETADA (con variaciones)

| # | Tarea | Estado | Detalle real |
|---|---|---|---|
| 1.1 | Seed: roles + permisos | ✅ | RolePermissionSeeder con todos los módulos |
| 1.2 | Seed: grados (1-6) | ✅ | GradoSeeder |
| 1.3 | Seed: usuario admin | ✅ | AdminSeeder |
| 1.4 | Actualizar DatabaseSeeder | ✅ | Orden: RolePermission → Grado → Admin → CicloEscolar → Materias → Periodos → Grupo |
| 1.5 | Livewire NavSidebar (PHP) | ✅ | app/Livewire/NavSidebar.php con grupos e items |
| 1.6 | Livewire NavSidebar (Blade) | ✅ | resources/views/livewire/nav-sidebar.blade.php |
| 1.7 | Reescribir sidebar.blade.php | ✅ | Layout Alpine+Tailwind, NO Flux en el layout |
| 1.8 | Sistema Toast Alpine | ✅ | window events + Alpine component |
| 1.9 | Rutas con nombre en modules.php | ✅ | 12 rutas fuera del wrapper Teams |
| 1.10 | CicloActivoMiddleware | ✅ | `app/Http/Middleware/CicloActivoMiddleware.php` — registrado en `bootstrap/app.php`. Comparte ciclo activo en vistas + warning si no hay |
| 1.11 | Profile con foto | ✅ | Implementado en SFC `⚡profile.blade.php` — upload, preview, remove, validación 1MB.
| 1.12 | migrate --seed verificado | ✅ | Funcional |
| 1.13 | Tests básicos auth | ✅ | RoleAccessTest (11 tests) |

### Fase 2: Catálogos base ✅ COMPLETADA

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 2.1 | Ciclos escolares | `Catalogos/CiclosEscolares` | ✅ | CRUD con validación 1 activo |
| 2.2 | Materias por grado | `Catalogos/Materias` | ✅ | Sin búsqueda (por decisión del usuario) |
| 2.3 | Periodos evaluación | `Catalogos/PeriodosEvaluacion` | ✅ | Vinculado a ciclo escolar |
| 2.4 | Usuarios | `Catalogos/Usuarios` | ✅ | CRUD + foto perfil + roles |
| 2.5 | Tests de catálogos | — | ✅ | CatalogosTest (tests iniciales + expansión) |

### Fase 3: Gestión de grupos ✅ COMPLETADA

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 3.1 | CRUD Grupos | `Catalogos/Grupos` | ✅ | Con filtros por ciclo y grado, búsqueda por nombre/docente |
| 3.2 | Asignación docente | Select en modal | ✅ | Filtra solo usuarios con rol Docente |
| 3.3 | Validaciones | Reglas en component | ✅ | required, exists, FK |
| 3.4 | Tests | CatalogosTest | ✅ | Guest redirect, page load, list display |

### Fase 3b (NUEVA): Docentes ✅ COMPLETADA

> Fase agregada durante implementación — no estaba en el plan original.

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 3b.1 | CRUD Docentes | `Catalogos/Docentes` | ✅ | Siempre asigna rol Docente |
| 3b.2 | Permisos | `docentes.*` | ✅ | listar, crear, editar, eliminar |
| 3b.3 | Ruta | `/docentes` | ✅ | docentes.index |
| 3b.4 | Menú lateral | Académico → Docentes | ✅ | Arriba de Grupos |
| 3b.5 | Tests | CatalogosTest | ✅ | Guest redirect, page load, filtrar solo docentes |

### Fase 4: Alumnos ✅ COMPLETADA

> Schema fix: se agregó `grupo_id` + `ciclo_escolar_id` a la tabla `alumnos` vía migración independiente.

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 4.1 | Migración: grupo_id + ciclo_escolar_id en alumnos | `2026_06_01_*` | ✅ | FKs nullable a grupos y ciclos_escolares |
| 4.2 | Alumno model actualizado | `app/Models/Alumno.php` | ✅ | Relaciones grupo(), cicloEscolar(). Fillable extendido |
| 4.3 | AlumnoFactory + AlumnoSeeder | `database/factories+seeders` | ✅ | 18 alumnos (3 por grado) con grupos asignados |
| 4.4 | CRUD Alumnos | `Catalogos/Alumnos` | ✅ | Transacción Persona+Alumno, búsqueda nombre/matrícula, filtros grado/grupo/estatus |
| 4.5 | Estatus management | Métodos darBaja/darEgreso/reactivar | ✅ | Badges de colores (verde/rojo/gris) |
| 4.6 | Matrícula auto-generada | Formato `ALU{AÑO}{SEQ:04d}` | ✅ | Editable al editar |
| 4.7 | Tests | CatalogosTest | ✅ | 4 tests: acceso, listado, filtros, superadmin |

### Fase 5: Calificaciones ✅ COMPLETADA

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 5.1 | Livewire Calificaciones | `Catalogos/Calificaciones` | ✅ | Selección grupo→materia→periodo, tabla alumnos con inputs de nota |
| 5.2 | Upsert + auditoría | `CalificacionLog` | ✅ | Crea log en cada creación/cambio de nota |
| 5.3 | Fix: $table en Calificacion | `app/Models/Calificacion.php` | ✅ | Eloquent infiere `calificacions`, se fijó `calificaciones` |
| 5.4 | Docente solo ve sus grupos | Filtro en render() | ✅ | Superadmin/Director ve todos |
| 5.5 | Tests | CatalogosTest | ✅ | 7 tests: acceso, grupos, filtro, materias, carga notas, guardar+log |

### Fase 6: Asistencia ✅ COMPLETADA

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 6.1 | Livewire Asistencia | `Catalogos/Asistencia` | ✅ | Selección grupo + fecha, tabla alumnos con select de estatus |
| 6.2 | Estatus disponibles | asistio/falta/retardo/justificado | ✅ | Select con emojis indicadores |
| 6.3 | Justificante con motivo | `Justificante` model | ✅ | Textarea visible solo cuando estatus=justificado. Se crea/actualiza/elimina automáticamente |
| 6.4 | Docente solo ve sus grupos | Filtro por rol en render() | ✅ | |
| 6.5 | Tests | CatalogosTest | ✅ | 7 tests: acceso, grupos, filtro docente, carga alumnos, guardar estatus, justificante |

### Fase 7: Boleta ✅ COMPLETADA

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 7.1 | Livewire Boleta | `Catalogos/Boleta` | ✅ | Selección grupo→alumno→periodo, vista previa con tabla cruzada |
| 7.2 | Tabla calificaciones | Materias × Periodos | ✅ | Con colores (verde ≥6, rojo <6), promedios por materia/periodo/general |
| 7.3 | Observaciones | `BoletaObservacion` model | ✅ | Agrupadas por periodo |
| 7.4 | PDF DomPDF | `resources/views/pdf/boleta.blade.php` | ✅ | CSS plano, header profesional, tabla cruzada, footer con fecha |
| 7.5 | Descarga PDF | `response()->streamDownload()` | ✅ | Nombre: `boleta-{matricula}.pdf` |
| 7.6 | Tests | CatalogosTest | ✅ | 5 tests: acceso, grupos en select, carga alumnos, carga calificaciones |

### Fase 8: Reinscripciones ✅ COMPLETADA

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 8.1 | Livewire Reinscripciones | `Catalogos/Reinscripciones` | ✅ | Selección grupo origen → tabla con checkboxes → grupo destino |
| 8.2 | Selección múltiple | ToggleAll + checkboxes individuales | ✅ | `wire:model="selected.*"` |
| 8.3 | Transacción DB | `DB::transaction()` | ✅ | Actualiza grado_id, grupo_id, ciclo_escolar_id |
| 8.4 | Confirmación | `wire:confirm` | ✅ | Muestra conteo de alumnos a reinscribir |
| 8.5 | Tests | CatalogosTest | ✅ | 4 tests: acceso, carga alumnos, reinscripción exitosa |

### Fase 9: Tutor ✅ COMPLETADA

Dashboard para padres/tutores donde pueden consultar la información académica de sus hijos. Dependencias: Fases 4, 5, 6, 7 completadas.

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 9.1 | Vincular Persona a User | Migración + Modelos | ✅ | `persona_id` nullable FK en `users`. Relaciones `User->persona()`, `Persona->user()` |
| 9.2 | Dashboard tutor | `TutorDashboard` | ✅ | Lista de hijos con nombre, matrícula, grado, grupo, estatus |
| 9.3 | Ver calificaciones | Vista `calificaciones` | ✅ | Tabla materias×periodos con colores, promedios, observaciones |
| 9.4 | Ver asistencias | Vista `asistencias` | ✅ | Historial con badges de estatus + resumen numérico |
| 9.5 | Descargar boleta | método `descargarBoleta()` | ✅ | Reusa template `pdf.boleta` de DomPDF |
| 9.6 | Permisos ya existentes | RolePermissionSeeder | ✅ | Ya estaban creados desde Fase 0. Ruta actualizada a componente real |
| 9.7 | Tests | `tests/Feature/Tutor/TutorTest.php` | ✅ | 7 tests: guest redirect, page load, sin hijos, con hijos, calificaciones, asistencias, volver |

### Fase 10: Reportes ✅ COMPLETADA

Módulo de reportes generales para administración escolar (Superadmin, Director, Subdirector).

| # | Tarea | Componente | Estado | Notas |
|---|---|---|---|---|
| 10.1 | Concentrado de calificaciones | Vista `concentrado` en Reportes | ✅ | Tabla alumnos×materias con promedios. Filtro grupo + periodo opcional. PDF con DomPDF |
| 10.2 | Kardex del alumno | Vista `kardex` en Reportes | ✅ | Historial completo del alumno a través de todos los ciclos. PDF agrupado por ciclo |
| 10.3 | Inasistencias por alumno/grupo | Vista `inasistencias` en Reportes | ✅ | Conteo por estatus (asistió/falta/retardo/justificado) con filtro de fechas. PDF con totales |
| 10.4 | Alumnos por tutor | Vista `alumnos-por-tutor` en Reportes | ✅ | Lista de tutores con teléfono, cantidad de hijos y nombres. Búsqueda por nombre |
| 10.5 | Permisos ya existentes | RolePermissionSeeder | ✅ | Ya estaban creados. Ruta actualizada a componente real |
| 10.6 | Tests | `tests/Feature/Catalogos/ReportesTest.php` | ✅ | 15 tests: acceso, carga de datos, 4 reportes, PDF |

### Fase 11: CI/CD ✅ COMPLETADA

Infraestructura de integración continua y despliegue. Sin dependencias de las fases anteriores.

| # | Tarea | Estado | Detalle |
|---|---|---|
| 11.1 | GitHub Actions: tests | ✅ | `tests.yml` — PHP 8.3/8.4/8.5, Node 22, Pest |
| 11.2 | GitHub Actions: lint | ✅ | `lint.yml` — Pint en cada push/PR |
| 11.3 | Configurar Laravel Cloud | ✅ | `config/cloud.php` + sección `extra.cloud` en composer.json con build/deploy commands |
| 11.4 | Base de datos en producción | ✅ | Migraciones listas + seeders (`php artisan migrate --force` en deploy) |
| 11.5 | Variables de entorno | ✅ | `.env.example` actualizado con secciones comentadas para producción, Flux, Cloud |
| 11.6 | Asset compilation | ✅ | En `composer.json` script `setup` (`npm install && npm run build`)

---

## 12. Decisiones de diseño registradas

### 12.1 Flux UI para contenido, Tailwind+Alpine para layout

El plan original decía "0 Flux, 0 Bootstrap". Durante implementación se decidió usar Flux UI 2 para el contenido (tablas, modales, botones, inputs, selects) porque acelera enormemente la creación de CRUDs. El layout general (sidebar, topbar, user dropdown, toast) sigue siendo 100% Tailwind + Alpine.

### 12.2 Docentes como módulo separado de Usuarios

Decisión del usuario: los docentes merecen su propio módulo CRUD, separado de la gestión general de usuarios. El componente `Docentes` es más simple que `Usuarios` (sin foto de perfil, sin selector de rol, sin creación de teams) y filtra siempre `User::role('Docente')`.

### 12.3 Materias sin búsqueda

Decisión del usuario: son pocas materias (24 aprox), no necesita búsqueda. Solo tiene sort y overflow-x-auto.

### 12.4 Búsqueda sin debounce

Todos los campos de búsqueda usan `wire:model.live` sin debounce, para respuesta inmediata. Con 15 items por página no hay impacto de performance.

### 12.5 Docente nullable en Grupo

`docente_id` en grupos es nullable. Un grupo puede no tener docente asignado. El select en el modal de Grupo tiene opción "Sin docente asignado" con valor vacío.

### 12.6 Ciclo activo por defecto

Al crear un grupo, el modal preselecciona el ciclo escolar activo. Esto evita errores de asignación.

### 12.7 Sin team creation en Docentes

A diferencia de Usuarios (que crea un personal team automáticamente), Docentes NO crea teams al registrarse. Los docentes se usan exclusivamente para asignación a grupos y autenticación.

### 12.8 NavSidebar con route_prefix para active state

En lugar de comparar rutas exactas, se usa `route_prefix` para determinar qué item está activo. Esto permite que rutas hijas (ej: `grupos.index`, `grupos.edit`) mantengan activo el item "Grupos".

### 12.9 `@fluxScripts` en el layout

El layout `sidebar.blade.php` incluye `@fluxScripts` al final del body, necesario para que los componentes Flux (modales, selects, etc.) funcionen correctamente.

### 12.10 RefreshDatabase global en tests

`tests/Pest.php` aplica `RefreshDatabase` a TODOS los tests en `tests/Feature/`. No es necesario agregar el trait en cada test case.

### 12.11 Tests consolidados en CatalogosTest

En lugar de un archivo de test por cada módulo de catálogo, todos los tests de CRUD de catálogos viven en `tests/Feature/Catalogos/CatalogosTest.php`. Esto es más fácil de mantener mientras el número de catálogos sea manejable.

---

## 13. Flujo de replicación del proyecto

Para replicar este proyecto desde cero:

```bash
# 1. Crear proyecto con Livewire Starter Kit
composer create-project laravel/laravel:^13.0 sistema-control-escolar
cd sistema-control-escolar

# 2. Configurar .env (MySQL database)
# DB_DATABASE=sistema_control_escolar

# 3. Instalar dependencias
composer require spatie/laravel-permission
composer require barryvdh/laravel-dompdf

# 4. Publicar Spatie migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# 5. Compilar assets
npm install && npm run build

# 6. Copiar archivos del proyecto:
#    - app/Models/*.php (13 modelos de dominio + HasRoles en User)
#    - app/Livewire/NavSidebar.php
#    - app/Livewire/Catalogos/*.php (6 componentes)
#    - database/migrations/*.php (13 migraciones de dominio + foto_perfil)
#    - database/seeders/*.php (8 seeders)
#    - resources/views/layouts/app/sidebar.blade.php
#    - resources/views/livewire/nav-sidebar.blade.php
#    - resources/views/livewire/catalogos/*.blade.php (6 vistas)
#    - routes/modules.php
#    - tests/Feature/Auth/RoleAccessTest.php
#    - tests/Feature/Catalogos/CatalogosTest.php
#    - tests/Pest.php

# 7. Migrar y seedear
php artisan migrate --seed

# 8. Verificar tests
php artisan test --compact --filter="Catalogos|RoleAccess"

# 9. Login: admin@admin.com / password
```

### Puntos críticos de configuración

1. **User model**: debe tener `HasRoles` trait y métodos `initials()`, `profilePhotoUrl()` (de Starter Kit).
2. **`tests/Pest.php`**: debe tener `->use(RefreshDatabase::class)->in('Feature')`.
3. **Layout**: `sidebar.blade.php` debe incluir `@fluxScripts` antes de `</body>`.
4. **Flux install**: el Starter Kit ya trae Flux configurado. Si se replica sin Starter Kit, seguir [docs de Flux](https://fluxui.dev).
5. **Tailwind v4**: configurado vía `vendor/livewire/flux` CSS import. No hay `tailwind.config.js`.
6. **Dark mode**: el `<html>` tag tiene `class="dark"` — forzado, sin toggle.

---

## Apéndice: Migraciones de dominio — detalle de columnas

### grados
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| nombre | string(10) | Ej: "1°", "2°" |
| orden | tinyInteger | Para ordenar 1-6 |

### ciclos_escolares
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| nombre | string(20) | Ej: "2024-2025" |
| fecha_inicio | date | |
| fecha_fin | date | |
| activo | boolean | Solo 1 puede ser activo |

### periodos_evaluacion
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| ciclo_escolar_id | FK→ciclos_escolares | |
| nombre | string(50) | Ej: "1er Trimestre" |
| orden | tinyInteger | 1, 2, 3 |
| fecha_inicio | date | |
| fecha_fin | date | |

### materias
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| grado_id | FK→grados | |
| clave_materia | string(10) | unique |
| nombre | string(100) | |

### grupos
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| grado_id | FK→grados | |
| ciclo_escolar_id | FK→ciclos_escolares | |
| docente_id | FK→users, nullable | |
| nombre | string(50) | Ej: "A", "B" |

### alumnos
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| grupo_id | FK→grupos | |
| ciclo_escolar_id | FK→ciclos_escolares | |
| nombre | string(100) | |
| curp | string(18) | unique por ciclo |
| fecha_nacimiento | date | |
| estado | string(20) | activo, baja, egresado |

### personas
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| user_id | FK→users, nullable | Cuando es tutor |
| nombre | string(100) | |
| telefono | string(15) | nullable |
| email | string(100) | nullable |

### alumno_familia
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| alumno_id | FK→alumnos | |
| persona_id | FK→personas | |
| parentesco | enum: padre, madre, tutor | |

### calificaciones
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| alumno_id | FK→alumnos | |
| grupo_id | FK→grupos | |
| materia_id | FK→materias | |
| periodo_evaluacion_id | FK→periodos_evaluacion | |
| calificacion | decimal(4,2), nullable | |

### calificacion_logs
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| calificacion_id | FK→calificaciones | |
| user_id | FK→users | |
| old_value | decimal(4,2), nullable | |
| new_value | decimal(4,2), nullable | |
| created_at | timestamp | |

### asistencias
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| alumno_id | FK→alumnos | |
| grupo_id | FK→grupos | |
| fecha | date | |
| estado | string(20) | presente, falta, justificado |
| created_by | FK→users | |

### justificantes
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| asistencia_id | FK→asistencias | |
| archivo | string(255) | Ruta storage |
| motivo | text | nullable |
| validado_por | FK→users, nullable | |
| created_at | timestamp | |

### boleta_observaciones
| Columna | Tipo | Notas |
|---------|------|-------|
| id | bigIncrements | |
| alumno_id | FK→alumnos | |
| grupo_id | FK→grupos | |
| ciclo_escolar_id | FK→ciclos_escolares | |
| periodo_evaluacion_id | FK→periodos_evaluacion | nullable |
| observacion | text | |
| created_by | FK→users | |
| created_at | timestamp | |
