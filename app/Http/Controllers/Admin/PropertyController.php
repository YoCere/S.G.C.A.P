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

        // ✅ BÚSQUEDA por referencia, barrio o cliente
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhere('barrio', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%");
                  });
            });
        }

        // Filtro por estado si se necesita
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $properties = $query->orderByDesc('id')->paginate(12);

        return view('admin.properties.index', compact('properties'));
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
        try {
            $property->update(['estado' => 'cortado']);
            
            return redirect()->back()
                ->with('info', 'Servicio cortado para: ' . $property->referencia);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al cortar servicio: ' . $e->getMessage());
        }
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