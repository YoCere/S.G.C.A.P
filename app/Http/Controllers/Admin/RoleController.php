<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // ✅ AGREGAR ESTE IMPORT

class RoleController extends Controller
{
    // Roles críticos que NO se pueden modificar/eliminar
    const CORE_ROLES = ['Admin', 'Secretaria', 'Operador'];

    public function __construct()
    {
        $this->middleware('can:admin.users.index')->only('index');
        $this->middleware('can:admin.users.create')->only('create', 'store');
        $this->middleware('can:admin.users.show')->only('show');
        $this->middleware('can:admin.users.edit')->only('edit', 'update');
        $this->middleware('can:admin.users.destroy')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::with('permissions')->get();
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
   /**
 * Show the form for creating a new resource.
 */
    public function create()
    {
        $permissions = Permission::all();
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
 * Store a newly created resource in storage.
 */
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:roles,name',
        'permissions' => 'required|array',
        'permissions.*' => 'exists:permissions,id'
    ]);

    try {
        DB::beginTransaction();

        // Crear rol
        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web',
            'activo' => true
        ]);

        // ✅ CORRECCIÓN: Obtener nombres de permisos desde IDs
        $permissionNames = Permission::whereIn('id', $request->permissions)
            ->pluck('name')
            ->toArray();

        // Asignar permisos usando los NOMBRES
        $role->syncPermissions($permissionNames);

        DB::commit();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol creado exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        
        return redirect()->back()
            ->with('error', 'Error al crear el rol: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        $role->load('permissions', 'users');
        
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    /**
     * Show the form for editing the specified resource.
     */
    /**
 * Show the form for editing the specified resource.
 */
public function edit(Role $role)
{
    // ✅ ELIMINADO: Ya no hay restricción para roles del sistema
    // El admin puede editar TODOS los roles

    // Asegurar que permissions esté agrupado por módulo
    $permissions = Permission::all()->groupBy(function ($permission) {
        $parts = explode('.', $permission->name);
        return count($parts) >= 2 ? $parts[1] : 'general';
    });

    $rolePermissions = $role->permissions->pluck('id')->toArray();
    
    return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
}

/**
 * Update the specified resource in storage.
 */
public function update(Request $request, Role $role)
{
    // ✅ ELIMINADO: Ya no hay restricción para roles del sistema
    // El admin puede actualizar TODOS los roles

    $request->validate([
        'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
        'permissions' => 'required|array',
        'permissions.*' => 'exists:permissions,id'
    ]);

    try {
        DB::beginTransaction();

        // Actualizar nombre del rol
        $role->update($request->only('name'));

        // Obtener los nombres de los permisos desde los IDs
        $permissionNames = Permission::whereIn('id', $request->permissions)
            ->pluck('name')
            ->toArray();

        // Sincronizar permisos usando los NOMBRES
        $role->syncPermissions($permissionNames);

        DB::commit();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol actualizado exitosamente.');

    } catch (\Exception $e) {
        DB::rollBack();
        return redirect()->back()
            ->with('error', 'Error al actualizar el rol: ' . $e->getMessage())
            ->withInput();
    }
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // 1. Prevenir eliminación de roles del sistema
        if (in_array($role->name, self::CORE_ROLES)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'No se puede eliminar un rol del sistema.');
        }

        // 2. Verificar si hay usuarios con este rol
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'No se puede eliminar el rol. Existen usuarios asignados a este rol.');
        }

        try {
            DB::beginTransaction();

            // Remover todos los permisos antes de eliminar
            $role->syncPermissions([]);
            $role->delete();

            DB::commit();

            return redirect()->route('admin.roles.index')
                ->with('success', 'Rol eliminado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('admin.roles.index')
                ->with('error', 'Error al eliminar el rol: ' . $e->getMessage());
        }
    }

    /**
     * Verificar si un rol es del sistema
     */
    public static function isCoreRole($roleName)
    {
        return in_array($roleName, self::CORE_ROLES);
    }
    /**
     * Desactivar un rol
     */
    public function desactivate(Role $role)
    {
        // Prevenir desactivación de roles del sistema
        if (in_array($role->name, self::CORE_ROLES)) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'No se puede desactivar un rol del sistema.');
        }

        $role->update(['activo' => false]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol desactivado exitosamente.');
    }

    /**
     * Activar un rol
     */
    public function activate(Role $role)
    {
        $role->update(['activo' => true]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rol activado exitosamente.');
    }
}