<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyRequest;
use App\Models\Property;
use App\Models\Client;
use App\Models\Tariff;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
    public function index(Request $request)
{
    $query = Property::with(['client', 'tariff']);

    // Aplicar filtros
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('referencia', 'like', "%{$search}%")
              ->orWhereHas('client', function($q) use ($search) {
                  $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('ci', 'like', "%{$search}%");
              });
        });
    }

    if ($request->filled('estado')) {
        $query->where('estado', $request->estado);
    }

    if ($request->filled('barrio')) {
        $query->where('barrio', $request->barrio);
    }

    if ($request->filled('tarifa_id')) {
        $query->where('tarifa_id', $request->tarifa_id);
    }

    if ($request->filled('cliente_id')) {
        $query->where('cliente_id', $request->cliente_id);
    }

    if ($request->filled('estado_cuenta')) {
        $query->whereHas('client', function($q) use ($request) {
            $q->where('estado_cuenta', $request->estado_cuenta);
        });
    }

    // Ordenamiento
    switch ($request->orden) {
        case 'antiguo':
            $query->orderBy('created_at', 'asc');
            break;
        case 'referencia':
            $query->orderBy('referencia', 'asc');
            break;
        case 'cliente':
            $query->join('clientes', 'propiedades.cliente_id', '=', 'clientes.id')
                  ->orderBy('clientes.nombre', 'asc')
                  ->select('propiedades.*');
            break;
        case 'barrio':
            $query->orderBy('barrio', 'asc');
            break;
        default: // reciente
            $query->orderBy('created_at', 'desc');
            break;
    }

    $properties = $query->paginate(20);

    // Estadísticas para las tarjetas
    $estadisticas = [
        'activas' => Property::where('estado', 'activo')->count(),
        'corte_pendiente' => Property::where('estado', 'corte_pendiente')->count(),
        'cortadas' => Property::where('estado', 'cortado')->count(),
        'con_ubicacion' => Property::whereNotNull('latitud')->whereNotNull('longitud')->count(),
        'clientes_activos' => Client::where('estado_cuenta', 'activo')->count(),
    ];

    $clients = Client::orderBy('nombre')->get();
    $tariffs = Tariff::orderBy('nombre')->get();

    return view('admin.properties.index', compact(
        'properties', 
        'clients', 
        'tariffs',
        'estadisticas'
    ))->with('totalPropiedades', Property::count());
}

    public function create()
    {
        $clients = Client::orderBy('nombre')->get(['id', 'nombre', 'ci']);
        $tariffs = Tariff::activas()->orderBy('nombre')->get(); // ← SOLO ACTIVAS para crear
        
        return view('admin.properties.create', compact('clients', 'tariffs'));
    }

    public function store(PropertyRequest $request)
    {
        try {
            Property::create($request->validated());
            return redirect()->route('admin.properties.index')
                ->with('info', 'Propiedad creada con éxito');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la propiedad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Property $property)
    {
        $property->load(['client', 'tariff', 'debts']);
        return view('admin.properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        $clients = Client::orderBy('nombre')->get(['id', 'nombre', 'ci']);
        
        // ✅ MOSTRAR TODAS las tarifas en edición, pero marcar inactivas
        $tariffs = Tariff::orderBy('activo', 'desc')->orderBy('nombre')->get();
        
        return view('admin.properties.edit', compact('property', 'clients', 'tariffs'));
    }

    public function update(PropertyRequest $request, Property $property)
    {
        try {
            $property->update($request->validated());
            
            return redirect()->route('admin.properties.index')
                ->with('info', 'Propiedad actualizada correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la propiedad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Property $property)
    {
        try {
            $property->delete();
            return redirect()->route('admin.properties.index')
                ->with('info', 'Propiedad eliminada con éxito');
                
        } catch (QueryException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1451) {
                return back()->with('error', 'No se puede eliminar: tiene registros asociados (deudas, pagos, etc.).');
            }
            return back()->with('error', 'Error al eliminar la propiedad: ' . $e->getMessage());
        }
    }

    public function cutService(Property $property)
{
    // Cambiar estado a "corte_pendiente" en lugar de "cortado"
    $property->update(['estado' => 'corte_pendiente']);
    
    // También actualizar las deudas a estado "corte_pendiente"
    $property->debts()
        ->where('estado', 'pendiente')
        ->update(['estado' => 'corte_pendiente']);

    return redirect()->route('admin.properties.index')
        ->with('success', 'Propiedad marcada para corte pendiente. El equipo físico procederá con el corte.');
}

// Agregar este método para cancelar corte pendiente
public function cancelCutService(Property $property)
{
    // Solo permitir si está en corte_pendiente
    if ($property->estado !== 'corte_pendiente') {
        return redirect()->back()
            ->with('error', 'Solo se puede cancelar cortes pendientes');
    }

    $property->update(['estado' => 'activo']);
    
    // Revertir deudas a estado pendiente
    $property->debts()
        ->where('estado', 'corte_pendiente')
        ->update(['estado' => 'pendiente']);

    return redirect()->route('admin.properties.index')
        ->with('success', 'Corte pendiente cancelado. Propiedad reactivada.');
}

    public function restoreService(Property $property)
    {
        try {
            $property->update(['estado' => 'activo']);
            
            return redirect()->back()
                ->with('info', 'Servicio restaurado para: ' . $property->referencia);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al restaurar servicio: ' . $e->getMessage());
        }
    }
    public function search(Request $request)
{
    $query = $request->get('q');
    
    $propiedades = Property::with(['client', 'tariff'])
        ->where('estado', 'activo')
        ->where(function($q) use ($query) {
            $q->where('referencia', 'like', "%{$query}%")
              ->orWhere('barrio', 'like', "%{$query}%")
              ->orWhereHas('client', function($q) use ($query) {
                  $q->where('nombre', 'like', "%{$query}%")
                    ->orWhere('ci', 'like', "%{$query}%");
              });
        })
        ->limit(10)
        ->get()
        ->map(function($propiedad) {
            return [
                'id' => $propiedad->id,
                'referencia' => $propiedad->referencia,
                'barrio' => $propiedad->barrio,
                'cliente_nombre' => $propiedad->client->nombre,
                'cliente_ci' => $propiedad->client->ci,
                'tarifa_precio' => $propiedad->tariff->precio_mensual,
                'tarifa_nombre' => $propiedad->tariff->nombre
            ];
        });
    
    return response()->json($propiedades);
}
}