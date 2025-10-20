<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.users.index')->only('index');
        $this->middleware('can:admin.users.create')->only(['create', 'store']);
        $this->middleware('can:admin.users.edit')->only(['edit', 'update']);
        $this->middleware('can:admin.users.show')->only('show');
        $this->middleware('can:admin.users.destroy')->only('destroy');
    }

    public function index(Request $request)
    {
        $query = User::with(['roles']);

        // ✅ FILTRO POR ESTADO
        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->where('activo', true);
            } elseif ($request->estado === 'inactivos') {
                $query->where('activo', false);
            }
            // Si es 'todos' no aplicamos filtro
        } else {
            // Por defecto mostrar solo activos
            $query->where('activo', true);
        }

        // Búsqueda por nombre o email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'activo' => true,
        ]);

        $user->roles()->sync($request->roles);

        return redirect()->route('admin.users.index')->with('info', 'Usuario creado con éxito');
    }

    public function show(User $user)
    {
        $user->load(['roles']);
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $user->load(['roles']);
        
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);
        $user->roles()->sync($request->roles);

        return redirect()->route('admin.users.index')->with('info', 'Usuario actualizado con éxito');
    }

    public function destroy(User $user)
    {
        // ✅ CAMBIADO: En lugar de eliminar, cambiar estado
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'No puedes desactivar tu propio usuario.');
        }

        // ✅ MODIFICADO: Permitir desactivar aunque tenga pagos, con mensaje apropiado
        $pagosCount = $user->pagos()->count();
        
        // Cambiar estado a inactivo
        $user->update(['activo' => false]);

        if ($pagosCount > 0) {
            return redirect()->route('admin.users.index')
                ->with('warning', "Usuario desactivado. Tiene {$pagosCount} pago(s) registrados que se mantienen en el sistema para auditoría.");
        }

        return redirect()->route('admin.users.index')
            ->with('info', 'Usuario desactivado correctamente.');
    }

    // ✅ NUEVO MÉTODO PARA ACTIVAR USUARIOS
    public function activate(User $user)
    {
        $user->update(['activo' => true]);

        return redirect()->route('admin.users.index')
            ->with('info', 'Usuario activado correctamente.');
    }
}