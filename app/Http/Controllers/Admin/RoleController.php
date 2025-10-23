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
        $roles = Role::all();
        
        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::all()->groupBy(function ($permission) {
            // Extraer el módulo del nombre del permiso (ej: admin.users.index -> users)
            $parts = explode('.', $permission->name);
            return count($parts) >= 2 ? $parts[1] : 'general';
        });
        
        // DEBUG: Verificar permisos
        \Log::info('Total permisos encontrados: ' . $permissions->flatten()->count());
        \Log::info('Módulos de permisos: ' . $permissions->keys()->implode(', '));
        
        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('=== INICIANDO CREACIÓN DE ROL ===');
        \Log::info('Datos del request:', $request->all());

        // Validación manual para ver errores específicos
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:roles,name',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        if ($validator->fails()) {
            \Log::error('Errores de validación:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        \Log::info('Validación pasada correctamente');

        try {
            DB::beginTransaction();
            
            \Log::info('Intentando crear rol con nombre: ' . $request->name);
            
            // Crear el rol paso a paso
            $role = new Role();
            $role->name = $request->name;
            $role->guard_name = 'web'; // ✅ ESTE CAMPO ES OBLIGATORIO
            $role->save();
            
            \Log::info('Rol creado exitosamente. ID: ' . $role->id);

            \Log::info('Permisos a sincronizar:', $request->permissions);
            
            // Verificar que los permisos existen
            $permissionsCount = Permission::whereIn('id', $request->permissions)->count();
            \Log::info('Permisos encontrados en BD: ' . $permissionsCount . ' de ' . count($request->permissions));
            
            if ($permissionsCount !== count($request->permissions)) {
                throw new \Exception('Algunos permisos no existen en la base de datos');
            }
            
            $role->syncPermissions($request->permissions);
            \Log::info('Permisos sincronizados correctamente');

            DB::commit();
            \Log::info('=== ROL CREADO EXITOSAMENTE ===');

            return redirect()->route('admin.roles.index')
                ->with('success', 'Rol creado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('ERROR CRÍTICO: ' . $e->getMessage());
            \Log::error('Archivo: ' . $e->getFile());
            \Log::error('Línea: ' . $e->getLine());
            \Log::error('Trace completo: ' . $e->getTraceAsString());
            
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
    public function edit(Role $role)
    {
        // Prevenir edición de roles del sistema
        if (in_array($role->name, self::CORE_ROLES)) {
            return redirect()->route('admin.roles.index')
                ->with('warning', 'No se puede editar un rol del sistema.');
        }

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
        // Prevenir actualización de roles del sistema
        if (in_array($role->name, self::CORE_ROLES)) {
            return redirect()->route('admin.roles.index')
                ->with('warning', 'No se puede modificar un rol del sistema.');
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        try {
            DB::beginTransaction();

            $role->update([
                'name' => $request->name,
                // guard_name no se actualiza normalmente
            ]);

            $role->syncPermissions($request->permissions);

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
}