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
        $operador = Role::create(['name' => 'Operador']); // 🆕 CAMBIADO: personal_corte → Operador

        // ==================== PERMISOS GENERALES ====================
        Permission::create(['name' => 'admin.home'])->syncRoles([$admin, $secretaria, $operador]);

        // ==================== PERMISOS DE USUARIOS ====================
        Permission::create(['name' => 'admin.users.index'])->syncRoles([$admin]);
        Permission::create(['name' => 'admin.users.create'])->syncRoles([$admin]);
        Permission::create(['name' => 'admin.users.show'])->syncRoles([$admin]);
        Permission::create(['name' => 'admin.users.edit'])->syncRoles([$admin]);
        Permission::create(['name' => 'admin.users.update'])->syncRoles([$admin]);
        Permission::create(['name' => 'admin.users.destroy'])->syncRoles([$admin]);

        // ==================== PERMISOS DE CLIENTES ====================
        Permission::create(['name' => 'admin.clients.index'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.clients.create'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.clients.show'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.clients.edit'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.clients.update'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.clients.destroy'])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE TARIFAS ====================
        Permission::create(['name' => 'admin.tariffs.index'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.tariffs.create'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.tariffs.show'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.tariffs.edit'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.tariffs.update'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.tariffs.destroy'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.tariffs.deactivate'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.tariffs.activate'])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE PROPIEDADES ====================
        Permission::create(['name' => 'admin.properties.index'])->syncRoles([$admin, $secretaria]); // 🆕 CAMBIADO
        Permission::create(['name' => 'admin.properties.create'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.properties.show'])->syncRoles([$admin, $secretaria ]); // 🆕 CAMBIADO
        Permission::create(['name' => 'admin.properties.edit'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.properties.update'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.properties.destroy'])->syncRoles([$admin]);
        Permission::create(['name' => 'admin.properties.cut'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.properties.restore'])->syncRoles([$admin]); // 🆕 SOLO ADMIN puede reconectar directamente
        Permission::create(['name' => 'admin.properties.cancel-cut'])->syncRoles([$admin, $secretaria]);
        // 🆕 NUEVO PERMISO: Solicitar reconexión
        Permission::create(['name' => 'admin.properties.request-reconnection'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.propiedades.search'])->syncRoles([$admin, $secretaria]); // 🆕 CAMBIADO

        // ==================== PERMISOS DE PAGOS ====================
        Permission::create(['name' => 'admin.pagos.index'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.pagos.create'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.pagos.show'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.pagos.edit'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.pagos.update'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.pagos.destroy'])->syncRoles([$admin]);
        Permission::create(['name' => 'admin.pagos.print'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.pagos.anular'])->syncRoles([$admin]);
        Permission::create(['name' => 'admin.pagos.obtenerMesesPendientes'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.pagos.validar-meses'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.propiedades.deudaspendientes'])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE DEUDAS ====================
        Permission::create(['name' => 'admin.debts.index'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.debts.create'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.debts.show'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.debts.store'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.debts.destroy'])->syncRoles([$admin,]);
        Permission::create(['name' => 'admin.debts.annul'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.debts.mark-as-paid'])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE MULTAS ====================
        Permission::create(['name' => 'admin.multas.index'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.multas.create'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.multas.show'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.multas.edit'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.multas.update'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.multas.marcar-pagada'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.multas.anular'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.multas.restaurar'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.multas.obtener-monto-base'])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE CORTES ====================
        Permission::create(['name' => 'admin.cortes.pendientes'])->syncRoles([$admin, $operador, $secretaria]); // 🆕 CAMBIADO
        Permission::create(['name' => 'admin.cortes.cortadas'])->syncRoles([$admin, $operador, $secretaria]); // 🆕 CAMBIADO
        Permission::create(['name' => 'admin.cortes.marcar-cortado'])->syncRoles([$admin, $operador]); // 🆕 CAMBIADO
        Permission::create(['name' => 'admin.cortes.aplicar-multa'])->syncRoles([$admin, $operador]); // 🆕 CAMBIADO

        // ==================== PERMISOS DE REPORTES ====================
        Permission::create(['name' => 'admin.reportes.index'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.reportes.morosidad'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.reportes.ingresos'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.reportes.cortes'])->syncRoles([$admin, $secretaria]);
        Permission::create(['name' => 'admin.reportes.propiedades'])->syncRoles([$admin, $secretaria]);

        // ==================== PERMISOS DE UTILIDADES ====================
        Permission::create(['name' => 'admin.sincronizar-deudas'])->syncRoles([$admin]);
    }
}