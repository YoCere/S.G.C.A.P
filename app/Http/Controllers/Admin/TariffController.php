<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TariffController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.tariffs.index')->only('index');
        $this->middleware('can:admin.tariffs.create')->only(['create', 'store']);
        $this->middleware('can:admin.tariffs.edit')->only(['edit', 'update', 'activate', 'deactivate']);
        $this->middleware('can:admin.tariffs.show')->only('show');
        $this->middleware('can:admin.tariffs.activate')->only('activate');
        $this->middleware('can:admin.tariffs.deactivate')->only('deactivate');
    }
    public function index()
    {
        // Por defecto, Eloquent excluye las soft-deleted
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
            'nombre' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9ÁÉÍÓÚÑ ]+$/',
                Rule::unique('tarifas','nombre')->whereNull('deleted_at'),
            ],
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
            'nombre' => [
                'required',
                'string',
                'max:255',
                'regex:/^[A-Z0-9ÁÉÍÓÚÑ ]+$/',
                Rule::unique('tarifas','nombre')
                    ->ignore($tariff->id)
                    ->whereNull('deleted_at'),
            ],

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
        // Soft delete (no rompe FKs a deudas/propiedades)
        $tariff->delete();

        return redirect()
            ->route('admin.tariffs.index')
            ->with('info', 'Tarifa archivada con éxito');
    }
    public function deactivate(Tariff $tariff)
    {
        $tariff->update(['activo' => false]);
        
        return redirect()->route('admin.tariffs.index')
            ->with('info', "Tarifa '{$tariff->nombre}' desactivada correctamente");
    }

    public function activate(Tariff $tariff)
    {
        $tariff->update(['activo' => true]);
        
        return redirect()->route('admin.tariffs.index')
            ->with('info', "Tarifa '{$tariff->nombre}' activada correctamente");
    }
}
