<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        $admin = Role::create(['name' => 'Admin']);
        $secretaria = Role::create(['name' => 'Secretaria']);
        $operador = Role::create(['name' => 'Operador']);

        // ==================== PERMISOS GENERALES ====================
        Permission::create([
            'name' => 'admin.home',
            'description' => 'Acceder al dashboard principal'
        ])->syncRoles([$admin, $secretaria, $operador]);

        // ==================== PERMISOS DE USUARIOS ====================
        Permission::create([
            'name' => 'admin.users.index',
            'description' => 'Ver listado de usuarios'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.users.create',
            'description' => 'Crear nuevos usuarios'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.users.show',
            'description' => 'Ver detalles de usuario'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.users.edit',
            'description' => 'Editar información de usuarios'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.users.update',
            'description' => 'Actualizar datos de usuarios'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.users.destroy',
            'description' => 'Eliminar usuarios del sistema'
        ])->syncRoles([$admin]);

        // ==================== PERMISOS DE CLIENTES ====================
        Permission::create([
            'name' => 'admin.clients.index',
            'description' => 'Ver listado de clientes'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.clients.create',
            'description' => 'Registrar nuevos clientes'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.clients.show',
            'description' => 'Ver información detallada de cliente'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.clients.edit',
            'description' => 'Editar datos de clientes'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.clients.update',
            'description' => 'Actualizar información de clientes'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.clients.destroy',
            'description' => 'Eliminar clientes del sistema'
        ])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE TARIFAS ====================
        Permission::create([
            'name' => 'admin.tariffs.index',
            'description' => 'Ver listado de tarifas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.tariffs.create',
            'description' => 'Crear nuevas tarifas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.tariffs.show',
            'description' => 'Ver detalles de tarifa'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.tariffs.edit',
            'description' => 'Editar tarifas existentes'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.tariffs.update',
            'description' => 'Actualizar información de tarifas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.tariffs.destroy',
            'description' => 'Eliminar tarifas del sistema'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.tariffs.deactivate',
            'description' => 'Desactivar tarifas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.tariffs.activate',
            'description' => 'Activar tarifas'
        ])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE PROPIEDADES ====================
        Permission::create([
            'name' => 'admin.properties.index',
            'description' => 'Ver listado de propiedades'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.properties.create',
            'description' => 'Registrar nuevas propiedades'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.properties.show',
            'description' => 'Ver detalles de propiedad'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.properties.edit',
            'description' => 'Editar información de propiedades'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.properties.update',
            'description' => 'Actualizar datos de propiedades'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.properties.destroy',
            'description' => 'Eliminar propiedades del sistema'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.properties.cut',
            'description' => 'Solicitar corte de servicio'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.properties.restore',
            'description' => 'Restaurar servicio cortado'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.properties.cancel-cut',
            'description' => 'Cancelar solicitud de corte'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.properties.request-reconnection',
            'description' => 'Solicitar reconexión de servicio'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.propiedades.search',
            'description' => 'Buscar propiedades en el sistema'
        ])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE PAGOS ====================
        Permission::create([
            'name' => 'admin.pagos.index',
            'description' => 'Ver listado de pagos'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.pagos.create',
            'description' => 'Registrar nuevos pagos'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.pagos.show',
            'description' => 'Ver detalles de pago'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.pagos.edit',
            'description' => 'Editar información de pagos'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.pagos.update',
            'description' => 'Actualizar datos de pagos'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.pagos.destroy',
            'description' => 'Eliminar registros de pago'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.pagos.print',
            'description' => 'Imprimir comprobantes de pago'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.pagos.anular',
            'description' => 'Anular pagos registrados'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.pagos.obtenerMesesPendientes',
            'description' => 'Consultar meses pendientes de pago'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.pagos.validar-meses',
            'description' => 'Validar meses a pagar'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.propiedades.deudaspendientes',
            'description' => 'Consultar deudas pendientes por propiedad'
        ])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE DEUDAS ====================
        Permission::create([
            'name' => 'admin.debts.index',
            'description' => 'Ver listado de deudas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.debts.create',
            'description' => 'Generar nuevas deudas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.debts.show',
            'description' => 'Ver detalles de deuda'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.debts.store',
            'description' => 'Almacenar registros de deuda'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.debts.destroy',
            'description' => 'Eliminar registros de deuda'
        ])->syncRoles([$admin]);
        Permission::create([
            'name' => 'admin.debts.annul',
            'description' => 'Anular deudas registradas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.debts.mark-as-paid',
            'description' => 'Marcar deudas como pagadas'
        ])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE MULTAS ====================
        Permission::create([
            'name' => 'admin.multas.index',
            'description' => 'Ver listado de multas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.multas.create',
            'description' => 'Registrar nuevas multas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.multas.show',
            'description' => 'Ver detalles de multa'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.multas.edit',
            'description' => 'Editar información de multas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.multas.update',
            'description' => 'Actualizar datos de multas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.multas.marcar-pagada',
            'description' => 'Marcar multas como pagadas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.multas.anular',
            'description' => 'Anular multas registradas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.multas.restaurar',
            'description' => 'Restaurar multas anuladas'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.multas.obtener-monto-base',
            'description' => 'Consultar montos base de multas'
        ])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE CORTES ====================
        Permission::create([
            'name' => 'admin.cortes.pendientes',
            'description' => 'Ver trabajos de corte pendientes'
        ])->syncRoles([$admin, $operador, $secretaria]);
        Permission::create([
            'name' => 'admin.cortes.cortadas',
            'description' => 'Ver propiedades con servicio cortado'
        ])->syncRoles([$admin, $operador, $secretaria]);
        Permission::create([
            'name' => 'admin.cortes.marcar-cortado',
            'description' => 'Ejecutar corte físico de servicio'
        ])->syncRoles([$admin, $operador]);
        Permission::create([
            'name' => 'admin.cortes.aplicar-multa',
            'description' => 'Aplicar multas por reconexión'
        ])->syncRoles([$admin, $operador]);

        // ==================== PERMISOS DE REPORTES ====================
        Permission::create([
            'name' => 'admin.reportes.index',
            'description' => 'Acceder a módulo de reportes'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.reportes.morosidad',
            'description' => 'Generar reportes de morosidad'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.reportes.ingresos',
            'description' => 'Generar reportes de ingresos'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.reportes.cortes',
            'description' => 'Generar reportes de cortes'
        ])->syncRoles([$admin, $secretaria]);
        Permission::create([
            'name' => 'admin.reportes.propiedades',
            'description' => 'Generar reportes de propiedades'
        ])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE UTILIDADES ====================
        Permission::create([
            'name' => 'admin.sincronizar-deudas',
            'description' => 'Sincronizar deudas con pagos'
        ])->syncRoles([$admin]);
    }
}