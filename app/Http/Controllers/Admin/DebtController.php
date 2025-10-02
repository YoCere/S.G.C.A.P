<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Debt;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class DebtController extends Controller
{
    public function index(Request $request)
    {
        $query = Debt::with(['propiedad.client', 'tarifa']);

        // Búsqueda por cliente o propiedad
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

        // Filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Filtro por mes
        if ($request->filled('mes')) {
            $query->whereYear('fecha_emision', Carbon::parse($request->mes)->year)
                  ->whereMonth('fecha_emision', Carbon::parse($request->mes)->month);
        }

        $debts = $query->orderBy('fecha_emision', 'desc')->paginate(15);

        return view('admin.debts.index', compact('debts'));
    }

    public function create()
    {
        $propiedades = Property::with(['client', 'tariff'])
                            ->where('estado', 'activo')
                            ->get();

        return view('admin.debts.create', compact('propiedades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'propiedad_id' => 'required|exists:propiedades,id',
            'monto_pendiente' => 'required|numeric|min:0',
            'fecha_emision' => [
                'required', 
                'date',
                // ✅ EVITAR DUPLICADOS: misma propiedad + mismo mes/año
                Rule::unique('deudas')->where(function ($query) use ($request) {
                    return $query->where('propiedad_id', $request->propiedad_id)
                                ->whereYear('fecha_emision', date('Y', strtotime($request->fecha_emision)))
                                ->whereMonth('fecha_emision', date('m', strtotime($request->fecha_emision)));
                })
            ],
            'fecha_vencimiento' => 'nullable|date|after:fecha_emision',
            'estado' => 'required|in:pendiente,pagada',
        ]);

        // Tarifa automática desde propiedad
        $propiedad = Property::find($data['propiedad_id']);
        $data['tarifa_id'] = $propiedad->tarifa_id;

        Debt::create($data);

        return redirect()->route('admin.debts.index')
            ->with('info', 'Deuda registrada exitosamente');
    }

    public function show(Debt $debt)
    {
        $debt->load(['propiedad.client', 'tarifa']);
        return view('admin.debts.show', compact('debt'));
    }

    public function destroy(Debt $debt)
    {
        if ($debt->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden eliminar deudas pendientes');
        }

        $debt->delete();

        return redirect()->route('admin.debts.index')
            ->with('info', 'Deuda eliminada');
    }

    public function annul(Debt $debt)
    {
        if ($debt->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden anular deudas pendientes');
        }

        $debt->update(['estado' => 'anulada']);

        return back()->with('info', 'Deuda anulada correctamente');
    }

    // ✅ NUEVO: Marcar como pagada
    public function markAsPaid(Debt $debt)
    {
        if ($debt->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden marcar como pagadas deudas pendientes');
        }

        $debt->update([
            'estado' => 'pagada',
            'pagada_adelantada' => now()->lt($debt->fecha_emision) // true si paga antes de emisión
        ]);

        return back()->with('info', 'Deuda marcada como pagada');
    }
}