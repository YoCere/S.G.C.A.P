<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    public function index()
    {
        $tariffs = Tariff::orderBy('nombre')->paginate(15);
        return view('admin.tariffs.index', compact('tariffs'));
    }

    public function create()
    {
        return view('admin.tariffs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'         => 'required|string|max:255|unique:tarifas,nombre',
            'precio_mensual' => 'required|numeric|min:0|max:999999.99',
            'descripcion'    => 'nullable|string|max:1000',
        ]);

        $tariff = Tariff::create($data);

        return redirect()
            ->route('admin.tariffs.edit', $tariff)
            ->with('info', 'Tarifa creada con éxito');
    }

    public function show(Tariff $tariff)
    {
        return view('admin.tariffs.show', compact('tariff'));
    }

    public function edit(Tariff $tariff)
    {
        return view('admin.tariffs.edit', compact('tariff'));
    }

    public function update(Request $request, Tariff $tariff)
    {
        $data = $request->validate([
            'nombre'         => 'required|string|max:255|unique:tarifas,nombre,' . $tariff->id,
            'precio_mensual' => 'required|numeric|min:0|max:999999.99',
            'descripcion'    => 'nullable|string|max:1000',
        ]);

        $tariff->update($data);

        return redirect()
            ->route('admin.tariffs.edit', $tariff)
            ->with('info', 'Tarifa actualizada con éxito');
    }

    public function destroy(Tariff $tariff)
    {
        $tariff->delete();

        return redirect()
            ->route('admin.tariffs.index')
            ->with('info', 'Tarifa eliminada con éxito');
    }
}
