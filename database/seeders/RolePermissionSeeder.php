<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()->make(PermissionRegistrar::class)->forgetCachedPermissions();

        // ─── Permisos agrupados por módulo ───

        $permissionsByModule = [
            'Catalogos' => ['listar', 'crear', 'editar', 'eliminar'],
            'Docentes' => ['listar', 'crear', 'editar', 'eliminar'],
            'Grupos' => ['listar', 'crear', 'editar', 'asignar-docente'],
            'Alumnos' => ['listar', 'inscribir', 'editar', 'dar-baja', 'dar-egreso'],
            'Padres' => ['listar', 'crear', 'editar', 'eliminar'],
            'Calificaciones' => ['capturar', 'ver-reporte'],
            'Asistencia' => ['pasar-lista', 'ver-reporte', 'subir-justificante', 'validar-justificante'],
            'Boleta' => ['generar', 'descargar'],
            'Reinscripciones' => ['reinscribir', 'listar'],
            'Reportes' => ['concentrado', 'kardex', 'inasistencias', 'alumnos-por-tutor'],
            'Tutor' => ['dashboard', 'ver-calificaciones', 'ver-asistencia', 'descargar-boleta'],
            'Usuarios' => ['listar', 'crear', 'editar', 'eliminar'],
        ];

        $allPermissions = [];

        foreach ($permissionsByModule as $module => $actions) {
            foreach ($actions as $action) {
                $permissionName = strtolower($module).".$action";
                $allPermissions[] = Permission::firstOrCreate([
                    'name' => $permissionName,
                    'guard_name' => 'web',
                ]);
            }
        }

        // Permiso extra para dashboard general
        $dashboardPermission = Permission::firstOrCreate([
            'name' => 'dashboard',
            'guard_name' => 'web',
        ]);

        // ─── Roles y asignación de permisos ───

        $superadmin = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        $superadmin->givePermissionTo($allPermissions);
        $superadmin->givePermissionTo($dashboardPermission);

        $director = Role::firstOrCreate(['name' => 'Director', 'guard_name' => 'web']);
        $director->givePermissionTo([
            // Docentes
            'docentes.listar', 'docentes.crear', 'docentes.editar', 'docentes.eliminar',
            // Grupos
            'grupos.listar', 'grupos.crear', 'grupos.editar', 'grupos.asignar-docente',
            // Alumnos
            'alumnos.listar', 'alumnos.inscribir', 'alumnos.editar', 'alumnos.dar-baja', 'alumnos.dar-egreso',
            // Calificaciones
            'calificaciones.capturar', 'calificaciones.ver-reporte',
            // Asistencia
            'asistencia.pasar-lista', 'asistencia.ver-reporte', 'asistencia.subir-justificante', 'asistencia.validar-justificante',
            // Boleta
            'boleta.generar', 'boleta.descargar',
            // Padres
            'padres.listar', 'padres.crear', 'padres.editar', 'padres.eliminar',
            // Reinscripciones
            'reinscripciones.reinscribir', 'reinscripciones.listar',
            // Reportes
            'reportes.concentrado', 'reportes.kardex', 'reportes.inasistencias', 'reportes.alumnos-por-tutor',
            // Dashboard
            'dashboard',
        ]);

        $subdirector = Role::firstOrCreate(['name' => 'Subdirector', 'guard_name' => 'web']);
        $subdirector->givePermissionTo([
            // Calificaciones
            'calificaciones.capturar', 'calificaciones.ver-reporte',
            // Asistencia
            'asistencia.pasar-lista', 'asistencia.ver-reporte', 'asistencia.subir-justificante', 'asistencia.validar-justificante',
            // Boleta
            'boleta.generar', 'boleta.descargar',
            // Reportes
            'reportes.concentrado', 'reportes.kardex', 'reportes.inasistencias', 'reportes.alumnos-por-tutor',
            // Dashboard
            'dashboard',
        ]);

        $docente = Role::firstOrCreate(['name' => 'Docente', 'guard_name' => 'web']);
        $docente->givePermissionTo([
            // Calificaciones
            'calificaciones.capturar', 'calificaciones.ver-reporte',
            // Asistencia
            'asistencia.pasar-lista', 'asistencia.ver-reporte', 'asistencia.subir-justificante', 'asistencia.validar-justificante',
            // Boleta
            'boleta.generar', 'boleta.descargar',
            // Dashboard
            'dashboard',
        ]);

        $tutor = Role::firstOrCreate(['name' => 'Tutor', 'guard_name' => 'web']);
        $tutor->givePermissionTo([
            // Boleta
            'boleta.generar', 'boleta.descargar',
            // Tutor
            'tutor.dashboard', 'tutor.ver-calificaciones', 'tutor.ver-asistencia', 'tutor.descargar-boleta',
            // Dashboard
            'dashboard',
        ]);
    }
}
