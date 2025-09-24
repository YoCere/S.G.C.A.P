<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PropertyRequest;
use App\Models\Property;
use App\Models\Client;
use App\Models\Tariff;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    public function index()
    {
        $properties = Property::with(['client','tariff'])->orderByDesc('id')->paginate(12);
        return view('admin.properties.index', compact('properties'));
    }

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
        return redirect()->route('admin.properties.edit', $property)->with('info','Propiedad actualizada con éxito');
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
}
