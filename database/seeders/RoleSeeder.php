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
        // Crear roles si no existen
        $roles = ['Admin', 'Secretaria', 'Operador'];
        $roleInstances = [];
        
        foreach ($roles as $roleName) {
            $roleInstances[$roleName] = Role::firstOrCreate(['name' => $roleName]);
        }

        $admin = $roleInstances['Admin'];
        $secretaria = $roleInstances['Secretaria'];
        $operador = $roleInstances['Operador'];

        // ==================== PERMISOS GENERALES ====================
        $this->createPermission('admin.home', 'Acceder al dashboard principal', [$admin, $secretaria, $operador]);

        // ==================== PERMISOS DE USUARIOS ====================
        $this->createPermission('admin.users.index', 'Ver listado de usuarios', [$admin]);
        $this->createPermission('admin.users.create', 'Crear nuevos usuarios', [$admin]);
        $this->createPermission('admin.users.show', 'Ver detalles de usuario', [$admin]);
        $this->createPermission('admin.users.edit', 'Editar información de usuarios', [$admin]);
        $this->createPermission('admin.users.update', 'Actualizar datos de usuarios', [$admin]);
        $this->createPermission('admin.users.destroy', 'Eliminar usuarios del sistema', [$admin]);

        // ==================== PERMISOS DE ROLES ====================
        $this->createPermission('admin.roles.index', 'Ver listado de roles', [$admin]);
        $this->createPermission('admin.roles.create', 'Crear nuevos roles', [$admin]);
        $this->createPermission('admin.roles.store', 'Guardar nuevos roles', [$admin]);
        $this->createPermission('admin.roles.show', 'Ver detalles de rol', [$admin]);
        $this->createPermission('admin.roles.edit', 'Editar información de roles', [$admin]);
        $this->createPermission('admin.roles.update', 'Actualizar datos de roles', [$admin]);
        $this->createPermission('admin.roles.destroy', 'Eliminar roles del sistema', [$admin]);
        $this->createPermission('admin.roles.desactivate', 'Desactivar roles', [$admin]);
        $this->createPermission('admin.roles.activate', 'Activar roles', [$admin]);

        // ==================== PERMISOS DE CLIENTES ====================
        $this->createPermission('admin.clients.index', 'Ver listado de clientes', [$admin, $secretaria]);
        $this->createPermission('admin.clients.create', 'Registrar nuevos clientes', [$admin, $secretaria]);
        $this->createPermission('admin.clients.show', 'Ver información detallada de cliente', [$admin, $secretaria]);
        $this->createPermission('admin.clients.edit', 'Editar datos de clientes', [$admin, $secretaria]);
        $this->createPermission('admin.clients.update', 'Actualizar información de clientes', [$admin, $secretaria]);
        $this->createPermission('admin.clients.destroy', 'Eliminar clientes del sistema', [$admin, $secretaria]);

        // ==================== PERMISOS DE TARIFAS ====================
        $this->createPermission('admin.tariffs.index', 'Ver listado de tarifas', [$admin, $secretaria]);
        $this->createPermission('admin.tariffs.create', 'Crear nuevas tarifas', [$admin, $secretaria]);
        $this->createPermission('admin.tariffs.show', 'Ver detalles de tarifa', [$admin, $secretaria]);
        $this->createPermission('admin.tariffs.edit', 'Editar tarifas existentes', [$admin, $secretaria]);
        $this->createPermission('admin.tariffs.update', 'Actualizar información de tarifas', [$admin, $secretaria]);
        $this->createPermission('admin.tariffs.destroy', 'Eliminar tarifas del sistema', [$admin, $secretaria]);
        $this->createPermission('admin.tariffs.deactivate', 'Desactivar tarifas', [$admin, $secretaria]);
        $this->createPermission('admin.tariffs.activate', 'Activar tarifas', [$admin, $secretaria]);

        // ==================== PERMISOS DE PROPIEDADES ====================
        $this->createPermission('admin.properties.index', 'Ver listado de propiedades', [$admin, $secretaria]);
        $this->createPermission('admin.properties.create', 'Registrar nuevas propiedades', [$admin, $secretaria]);
        $this->createPermission('admin.properties.show', 'Ver detalles de propiedad', [$admin, $secretaria]);
        $this->createPermission('admin.properties.edit', 'Editar información de propiedades', [$admin, $secretaria]);
        $this->createPermission('admin.properties.update', 'Actualizar datos de propiedades', [$admin, $secretaria]);
        $this->createPermission('admin.properties.destroy', 'Eliminar propiedades del sistema', [$admin]);
        $this->createPermission('admin.properties.cut', 'Solicitar corte de servicio', [$admin, $secretaria]);
        $this->createPermission('admin.properties.restore', 'Restaurar servicio cortado', [$admin]);
        $this->createPermission('admin.properties.cancel-cut', 'Cancelar solicitud de corte', [$admin, $secretaria]);
        $this->createPermission('admin.properties.request-reconnection', 'Solicitar reconexión de servicio', [$admin, $secretaria]);
        $this->createPermission('admin.propiedades.search', 'Buscar propiedades en el sistema', [$admin, $secretaria]);

        // ==================== PERMISOS DE PAGOS ====================
        $this->createPermission('admin.pagos.index', 'Ver listado de pagos', [$admin, $secretaria]);
        $this->createPermission('admin.pagos.create', 'Registrar nuevos pagos', [$admin, $secretaria]);
        $this->createPermission('admin.pagos.show', 'Ver detalles de pago', [$admin, $secretaria]);
        $this->createPermission('admin.pagos.edit', 'Editar información de pagos', [$admin, $secretaria]);
        $this->createPermission('admin.pagos.update', 'Actualizar datos de pagos', [$admin, $secretaria]);
        $this->createPermission('admin.pagos.destroy', 'Eliminar registros de pago', [$admin]);
        $this->createPermission('admin.pagos.print', 'Imprimir comprobantes de pago', [$admin, $secretaria]);
        $this->createPermission('admin.pagos.anular', 'Anular pagos registrados', [$admin]);
        $this->createPermission('admin.pagos.obtenerMesesPendientes', 'Consultar meses pendientes de pago', [$admin, $secretaria]);
        $this->createPermission('admin.pagos.validar-meses', 'Validar meses a pagar', [$admin, $secretaria]);
        $this->createPermission('admin.propiedades.deudaspendientes', 'Consultar deudas pendientes por propiedad', [$admin, $secretaria]);
        $this->createPermission('admin.pagos.obtenerMultasPendientes', 'Consultar deudas multas por propiedad', [$admin, $secretaria]);

        // ==================== PERMISOS DE DEUDAS ====================
        $this->createPermission('admin.debts.index', 'Ver listado de deudas', [$admin, $secretaria]);
        $this->createPermission('admin.debts.create', 'Generar nuevas deudas', [$admin, $secretaria]);
        $this->createPermission('admin.debts.show', 'Ver detalles de deuda', [$admin, $secretaria]);
        $this->createPermission('admin.debts.store', 'Almacenar registros de deuda', [$admin, $secretaria]);
        $this->createPermission('admin.debts.destroy', 'Eliminar registros de deuda', [$admin]);
        $this->createPermission('admin.debts.annul', 'Anular deudas registradas', [$admin, $secretaria]);
        $this->createPermission('admin.debts.mark-as-paid', 'Marcar deudas como pagadas', [$admin, $secretaria]);

        // ==================== PERMISOS DE MULTAS ====================
        $this->createPermission('admin.multas.index', 'Ver listado de multas', [$admin, $secretaria]);
        $this->createPermission('admin.multas.create', 'Registrar nuevas multas', [$admin, $secretaria]);
        $this->createPermission('admin.multas.show', 'Ver detalles de multa', [$admin, $secretaria]);
        $this->createPermission('admin.multas.edit', 'Editar información de multas', [$admin, $secretaria]);
        $this->createPermission('admin.multas.update', 'Actualizar datos de multas', [$admin, $secretaria]);
        $this->createPermission('admin.multas.marcar-pagada', 'Marcar multas como pagadas', [$admin, $secretaria]);
        $this->createPermission('admin.multas.anular', 'Anular multas registradas', [$admin, $secretaria]);
        $this->createPermission('admin.multas.restaurar', 'Restaurar multas anuladas', [$admin, $secretaria]);
        $this->createPermission('admin.multas.obtener-monto-base', 'Consultar montos base de multas', [$admin, $secretaria]);

        // ==================== PERMISOS DE CORTES ====================
        $this->createPermission('admin.cortes.pendientes', 'Ver trabajos de corte pendientes', [$admin, $operador, $secretaria]);
        $this->createPermission('admin.cortes.cortadas', 'Ver propiedades con servicio cortado', [$admin, $operador, $secretaria]);
        $this->createPermission('admin.cortes.marcar-cortado', 'Ejecutar corte físico de servicio', [$admin, $operador]);
        $this->createPermission('admin.cortes.aplicar-multa', 'Aplicar multas por reconexión', [$admin, $operador]);

        // ==================== PERMISOS DE REPORTES ====================
        $this->createPermission('admin.reportes.index', 'Acceder a módulo de reportes', [$admin, $secretaria]);
        $this->createPermission('admin.reportes.morosidad', 'Generar reporte de morosidad', [$admin, $secretaria]);
        $this->createPermission('admin.reportes.clientes', 'Generar reporte de clientes', [$admin, $secretaria]);
        $this->createPermission('admin.reportes.propiedades', 'Generar reporte de propiedades', [$admin, $secretaria]);
        $this->createPermission('admin.reportes.trabajos', 'Generar reporte de trabajos pendientes', [$admin, $secretaria]);

        // ==================== PERMISOS DE UTILIDADES ====================
        $this->createPermission('admin.sincronizar-deudas', 'Sincronizar deudas con pagos', [$admin]);

        // ==================== PERMISOS DE BACKUPS ====================
        $this->createPermission('admin.backups.index', 'Ver listado de backups del sistema', [$admin]);
        $this->createPermission('admin.backups.run', 'Ejecutar un backup manual', [$admin]);
        $this->createPermission('admin.backups.clean', 'Ejecutar limpieza de copias antiguas', [$admin]);
        $this->createPermission('admin.backups.download', 'Descargar copia de seguridad', [$admin]);
        $this->createPermission('admin.backups.log', 'Ver detalle del log de un backup', [$admin]);
        $this->createPermission('admin.backups.destroy', 'Eliminar una copia de seguridad', [$admin]);
        $this->createPermission('admin.backups.restore', 'Restaurar una copia de seguridad', [$admin]);
        $this->createPermission('admin.backups.restore-log', 'Ver el log de restauración en vivo', [$admin]);
    }
    
    private function createPermission($name, $description, $roles)
    {
        $permission = Permission::firstOrCreate(
            ['name' => $name],
            ['name' => $name, 'description' => $description]
        );
        
        foreach ($roles as $role) {
            if (!$role->hasPermissionTo($name)) {
                $role->givePermissionTo($permission);
            }
        }
        
        return $permission;
    }
}