<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles']);

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
        ]);

        // ✅ CORREGIDO: Usar sync con IDs de roles
        $user->roles()->sync($request->roles);

        return redirect()->route('admin.users.index', $user)->with('info', 'Usuario creado con éxito');
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

        // Actualizar password solo si se proporciona
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        // ✅ CORREGIDO: Mantener sync con IDs
        $user->roles()->sync($request->roles);

        return redirect()->route('admin.users.index', $user)->with('info', 'Usuario actualizado con éxito');
    }

    public function destroy(User $user)
    {
        // Validar que no sea el usuario actual
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Validar que no tenga registros asociados en pagos
        if ($user->pagos()->exists()) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar el usuario porque tiene pagos registrados.');
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('info', 'Usuario eliminado con éxito');
    }
}