<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyRequest;
use App\Models\Property;
use App\Models\Client;
use App\Models\Tariff;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request; // ← AGREGAR ESTO
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    public function index(Request $request) // ← CAMBIAR a Request $request
    {
        $query = Property::with(['client','tariff']);

        // ✅ NUEVO: BÚSQUEDA por referencia o cliente
       // En el método index() del PropertyController, actualiza la búsqueda:
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                ->orWhere('barrio', 'like', "%{$search}%") // ✅ NUEVO: Buscar por barrio
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

    // ... (el resto de los métodos se mantienen igual) ...
    public function create()
    {
        $clients = Client::orderBy('nombre')->get(['id','nombre','ci']);
        $tariffs = \App\Models\Tariff::whereNull('deleted_at')->orderBy('nombre')->get();
        return view('admin.properties.create', compact('clients','tariffs'));
    }

    public function store(PropertyRequest $request)
    {
        Property::create($request->validated());
        return redirect()->route('admin.properties.index')->with('info','Propiedad creada con éxito');
    }

    public function edit(Property $property)
    {
        $clients = Client::orderBy('nombre')->get(['id','nombre','ci']);
        $tariffs = \App\Models\Tariff::whereNull('deleted_at')->orderBy('nombre')->get();
        return view('admin.properties.edit', compact('property','clients','tariffs'));
    }

    public function update(PropertyRequest $request, Property $property)
    {   
        $property->update($request->validated());
        return redirect()->route('admin.properties.index', $property)->with('info','Propiedad actualizada con éxito');
    }

    public function destroy(Property $property)
    {
        try {
            $property->delete();
            return redirect()->route('admin.properties.index')->with('info','Propiedad eliminada con éxito');
        } catch (QueryException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1451) {
                return back()->with('info','No se puede eliminar: tiene registros asociados.');
            }
            throw $e;
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
}