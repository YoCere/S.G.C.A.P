<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.clients.index')->only('index');
        $this->middleware('can:admin.clients.create')->only(['create', 'store']);
        $this->middleware('can:admin.clients.show')->only('show');
        $this->middleware('can:admin.clients.edit')->only(['edit', 'update']);
        $this->middleware('can:admin.clients.destroy')->only('destroy');
    }

    public function index(Request $request)
    {
        // ✅ CORREGIDO: Cargar la relación properties
        $query = Client::with(['properties']);

        // ✅ FILTRO POR ESTADO
        if ($request->filled('estado')) {
            if ($request->estado === 'activos') {
                $query->activos();
            } elseif ($request->estado === 'inactivos') {
                $query->inactivos();
            }
            // Si es 'todos' no aplicamos filtro
        } else {
            // Por defecto mostrar solo activos
            $query->activos();
        }

        // ✅ ACTUALIZADO: BÚSQUEDA INCLUYE CÓDIGO CLIENTE
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('ci', 'like', "%{$search}%")
                  ->orWhere('telefono', 'like', "%{$search}%")
                  ->orWhere('codigo_cliente', 'like', "%{$search}%");
            });
        }

        // ✅ NUEVO: FILTRO ESPECÍFICO POR CÓDIGO CLIENTE
        if ($request->filled('codigo_cliente')) {
            $query->where('codigo_cliente', 'like', "%{$request->codigo_cliente}%");
        }

        $clients = $query->orderBy('nombre')->paginate(15);

        return view('admin.clients.index', compact('clients'));
    }

    public function create()
    {
        return view('admin.clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20|unique:clientes,ci',
            'telefono' => 'nullable|string|max:20',
        ]);
        
        // ✅ NUEVO: Generar código de cliente automáticamente
        $clientData = $request->all();
        $clientData['codigo_cliente'] = Client::generarCodigoAleatorioUnico();
        
        $client = Client::create($clientData);
        return redirect()->route('admin.clients.edit', $client)->with('info', 'Cliente creado con éxito');
    }

    public function show(Client $client)
    {
        // ✅ CARGAR relaciones correctamente
        $client->load([
            'properties.client', 
            'properties.tariff',
            'properties.debts' => function($query) {
                $query->where('estado', 'pendiente'); // Solo deudas pendientes
            }
        ]);
        
        return view('admin.clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'ci' => 'required|string|max:20|unique:clientes,ci,'.$client->id,
            'telefono' => 'nullable|string|max:20',
            // ❌ NO validar codigo_cliente
        ]);
        
        // ❌ NO actualizar el código, excluirlo del update
        $client->update($request->only(['nombre', 'ci', 'telefono']));
        
        return redirect()->route('admin.clients.edit', $client)->with('info', 'Cliente actualizado con éxito');
    }

    public function destroy(Client $client)
    {
        // ✅ CAMBIADO: En lugar de eliminar, cambiar estado a inactivo
        $propertiesCount = $client->properties()->count();
        
        // Cambiar estado a inactivo
        $client->update(['estado_cuenta' => Client::ESTADO_INACTIVO]);

        if ($propertiesCount > 0) {
            return redirect()->route('admin.clients.index')
                ->with('warning', "Cliente marcado como inactivo. Tiene {$propertiesCount} propiedad(es) asociadas que se mantienen en el sistema.");
        }

        return redirect()->route('admin.clients.index')
            ->with('info', 'Cliente marcado como inactivo correctamente.');
    }

    // ✅ NUEVO MÉTODO PARA ACTIVAR CLIENTES
    public function activate(Client $client)
    {
        $client->update(['estado_cuenta' => Client::ESTADO_ACTIVO]);

        return redirect()->route('admin.clients.index')
            ->with('info', 'Cliente activado correctamente.');
    }
}