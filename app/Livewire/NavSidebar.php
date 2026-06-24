<?php

namespace App\Livewire;

use Livewire\Component;

class NavSidebar extends Component
{
    public array $menuGroups = [];

    public function mount(): void
    {
        $user = auth()->user();

        $groups = [
            'General' => [
                [
                    'label' => 'Dashboard',
                    'route' => 'dashboard',
                    'route_prefix' => 'dashboard',
                    'params' => [],
                    'icon' => 'home',
                    'visible' => $user?->can('dashboard'),
                ],
            ],
            'Catálogos' => [
                [
                    'label' => 'Ciclos Escolares',
                    'route' => 'ciclos-escolares.index',
                    'route_prefix' => 'ciclos-escolares',
                    'params' => [],
                    'icon' => 'academic-cap',
                    'visible' => $user?->can('catalogos.listar'),
                ],
                [
                    'label' => 'Periodos Evaluación',
                    'route' => 'periodos-evaluacion.index',
                    'route_prefix' => 'periodos-evaluacion',
                    'params' => [],
                    'icon' => 'calendar-days',
                    'visible' => $user?->can('catalogos.listar'),
                ],
                [
                    'label' => 'Campos Formativos',
                    'route' => 'materias.index',
                    'route_prefix' => 'materias',
                    'params' => [],
                    'icon' => 'book-open',
                    'visible' => $user?->can('catalogos.listar'),
                ],
                [
                    'label' => 'Usuarios',
                    'route' => 'usuarios.index',
                    'route_prefix' => 'usuarios',
                    'params' => [],
                    'icon' => 'users',
                    'visible' => $user?->can('usuarios.listar'),
                ],
            ],
            'Académico' => [
                [
                    'label' => 'Docentes',
                    'route' => 'docentes.index',
                    'route_prefix' => 'docentes',
                    'params' => [],
                    'icon' => 'user-group',
                    'visible' => $user?->can('docentes.listar'),
                ],
                [
                    'label' => 'Grupos',
                    'route' => 'grupos.index',
                    'route_prefix' => 'grupos',
                    'params' => [],
                    'icon' => 'users',
                    'visible' => $user?->can('grupos.listar'),
                ],
                [
                    'label' => 'Alumnos',
                    'route' => 'alumnos.index',
                    'route_prefix' => 'alumnos',
                    'params' => [],
                    'icon' => 'user',
                    'visible' => $user?->can('alumnos.listar'),
                ],
                [
                    'label' => 'Padres de Familia',
                    'route' => 'padres-familia.index',
                    'route_prefix' => 'padres-familia',
                    'params' => [],
                    'icon' => 'users',
                    'visible' => $user?->can('padres.listar'),
                ],
                [
                    'label' => 'Calificaciones',
                    'route' => 'calificaciones.index',
                    'route_prefix' => 'calificaciones',
                    'params' => [],
                    'icon' => 'clipboard-document-list',
                    'visible' => $user?->can('calificaciones.capturar'),
                ],
                [
                    'label' => 'Asistencia',
                    'route' => 'asistencia.index',
                    'route_prefix' => 'asistencia',
                    'params' => [],
                    'icon' => 'calendar-days',
                    'visible' => $user?->can('asistencia.ver-reporte'),
                ],
                [
                    'label' => 'Pasar lista',
                    'route' => 'pasar-lista.index',
                    'route_prefix' => 'pasar-lista',
                    'params' => [],
                    'icon' => 'check-circle',
                    'visible' => $user?->can('asistencia.pasar-lista'),
                ],
                [
                    'label' => 'Reinscripciones',
                    'route' => 'reinscripciones.index',
                    'route_prefix' => 'reinscripciones',
                    'params' => [],
                    'icon' => 'arrow-path',
                    'visible' => $user?->can('reinscripciones.reinscribir'),
                ],
            ],
            'Reportes' => [
                [
                    'label' => 'Boleta',
                    'route' => 'boleta.index',
                    'route_prefix' => 'boleta',
                    'params' => [],
                    'icon' => 'document-text',
                    'visible' => $user?->can('boleta.generar'),
                ],
                [
                    'label' => 'Reportes',
                    'route' => 'reportes.index',
                    'route_prefix' => 'reportes',
                    'params' => [],
                    'icon' => 'chart-bar',
                    'visible' => $user?->can('reportes.concentrado'),
                ],
            ],
            'Tutor' => [
                [
                    'label' => 'Tutor Dashboard',
                    'route' => 'tutor.dashboard',
                    'route_prefix' => 'tutor',
                    'params' => [],
                    'icon' => 'academic-cap',
                    'visible' => $user?->can('tutor.dashboard'),
                ],
            ],
        ];

        // Pre-filter: remove non-visible items so they don't leak into the Livewire snapshot
        $this->menuGroups = array_map(function ($items) {
            return array_values(array_filter($items, fn ($item) => $item['visible']));
        }, $groups);
    }

    public function render()
    {
        return view('livewire.nav-sidebar');
    }
}
