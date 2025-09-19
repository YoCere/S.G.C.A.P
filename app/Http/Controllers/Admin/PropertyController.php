<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyRequest;
use App\Models\Property;
use App\Models\Client;
use App\Models\Tariff;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::with(['client','tariff'])
            ->orderByDesc('id')
            ->paginate(12);

        return view('admin.properties.index', compact('properties'));
    }

    public function create()
    {
        $clientes = Client::orderBy('nombre')->get(['id','nombre','ci']);
        $tarifas  = Tariff::orderBy('nombre')->get(['id','nombre','precio_mensual']);

        return view('admin.properties.create', compact('clientes','tarifas'));
    }

    public function store(PropertyRequest $request)
    {
        Property::create($request->validated());
        return redirect()
            ->route('admin.properties.index')
            ->with('info', 'Propiedad creada con éxito');
    }

    // OJO: usamos parámetro singular 'propiedad' (ver rutas)
    public function edit(Property $propiedad)
    {
        $clientes = Client::orderBy('nombre')->get(['id','nombre','ci']);
        $tarifas  = Tariff::orderBy('nombre')->get(['id','nombre','precio_mensual']);

        return view('admin.properties.edit', compact('propiedad','clientes','tarifas'));
    }

    public function update(PropertyRequest $request, Property $propiedad)
    {
        $propiedad->update($request->validated());

        return redirect()
            ->route('admin.properties.edit', $propiedad)
            ->with('info', 'Propiedad actualizada con éxito');
    }

    public function destroy(Property $propiedad)
    {
        try {
            $propiedad->delete();
            return redirect()
                ->route('admin.properties.index')
                ->with('info', 'Propiedad eliminada con éxito');
        } catch (QueryException $e) {
            // 1451 = Cannot delete or update a parent row: a foreign key constraint fails
            if ((int) $e->errorInfo[1] === 1451) {
                return back()->with('info', 'No se puede eliminar: tiene registros asociados (deudas/recibos).');
            }
            throw $e;
        }
    }
}
