<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Property;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $query = Debt::with(['propiedad.client', 'tarifa']);

        // ✅ NUEVO: BÚSQUEDA por cliente o propiedad
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('propiedad.client', function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%");
                })->orWhereHas('propiedad', function($q) use ($search) {
                    $q->where('referencia', 'like', "%{$search}%");
                });
            });
        }

        // Filtros existentes
        if ($request->filled('cliente_id')) {
            $query->whereHas('propiedad', function ($q) use ($request) {
                $q->where('cliente_id', $request->cliente_id);
            });
        }

        if ($request->filled('propiedad_id')) {
            $query->where('propiedad_id', $request->propiedad_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $debts = $query->orderBy('fecha_emision', 'desc')->paginate(15);

        return view('admin.debts.index', compact('debts'));
    }

    // ... (el resto de tus métodos se mantienen igual) ...
    public function create()
    {
        $propiedades = Property::with('client')->orderBy('referencia')->get();
        $tarifas = Tariff::orderBy('nombre')->get();

        return view('admin.debts.create', compact('propiedades', 'tarifas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'propiedad_id' => ['required', 'exists:propiedades,id'],
            'tarifa_id'    => ['required', 'exists:tarifas,id'],
            'monto_pendiente' => ['required', 'numeric', 'min:0'],
            'fecha_emision'   => [
                'required','date',
                Rule::unique('deudas')
                    ->where(fn($q) => $q->where('propiedad_id', $request->propiedad_id))
            ],
            'fecha_vencimiento' => ['nullable', 'date'],
            'estado' => ['required', 'in:pendiente,pagada,vencida'],
            'pagada_adelantada' => ['boolean'],
        ]);

        // LÓGICA AUTOMÁTICA para pagada_adelantada
        if ($data['estado'] === 'pagada' && Carbon::parse($data['fecha_emision']) > now()) {
            $data['pagada_adelantada'] = true;
        } else {
            $data['pagada_adelantada'] = $data['pagada_adelantada'] ?? false;
        }

        $debt = Debt::create($data);

        return redirect()
            ->route('admin.debts.edit', $debt)
            ->with('info', 'Deuda registrada con éxito');
    }

    public function show(Debt $debt)
    {
        $debt->load(['propiedad.client', 'tarifa', 'multas']);
        return view('admin.debts.show', compact('debt'));
    }

    public function edit(Debt $debt)
    {
        $debt->load(['propiedad.client', 'tarifa']);
        
        $propiedades = Property::with(['client'])->orderBy('referencia')->get();
        $tarifas = Tariff::orderBy('nombre')->get();

        return view('admin.debts.edit', compact('debt','propiedades','tarifas'));
    }

    public function update(Request $request, Debt $debt)
    {
        // VALIDACIÓN: No cambiar propiedad
        if ($debt->propiedad_id != $request->propiedad_id) {
            return redirect()->back()
                ->withErrors(['propiedad_id' => 'No se puede cambiar la propiedad de una deuda existente.'])
                ->withInput();
        }

        $data = $request->validate([
            'propiedad_id' => ['required', 'exists:propiedades,id'],
            'tarifa_id'    => ['required', 'exists:tarifas,id'],
            'fecha_emision'   => [
                'required','date',
                Rule::unique('deudas')
                    ->where(fn($q) => $q->where('propiedad_id', $request->propiedad_id))
                    ->ignore($debt->id)
            ],
            'fecha_vencimiento' => ['nullable', 'date'],
            'estado' => ['required', 'in:pendiente,pagada,vencida'],
        ]);

        // MONTO AUTOMÁTICO según tarifa
        $tarifa = Tariff::find($data['tarifa_id']);
        $data['monto_pendiente'] = $tarifa->precio_mensual;

        // PAGADA_ADELANTADA automático
        if ($data['estado'] === 'pagada') {
            $data['pagada_adelantada'] = Carbon::parse($data['fecha_emision']) > now();
        } else {
            $data['pagada_adelantada'] = false;
        }

        $debt->update($data);

        return redirect()->route('admin.debts.index')
            ->with('info', 'Deuda actualizada con éxito');
    }

    public function destroy(Debt $debt)
    {
        $debt->delete();

        return redirect()
            ->route('admin.debts.index')
            ->with('info', 'Deuda eliminada con éxito');
    }
}