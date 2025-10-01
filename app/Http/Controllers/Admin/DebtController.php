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

    public function create()
    {
        $propiedades = Property::with(['client', 'tariff'])->orderBy('referencia')->get();
        // ❌ ELIMINAR: No necesitas cargar todas las tarifas
        // ✅ La tarifa viene de la propiedad seleccionada

        return view('admin.debts.create', compact('propiedades'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'propiedad_id' => ['required', 'exists:propiedades,id'],
            // ❌ ELIMINAR: 'tarifa_id' del validation
            'monto_pendiente' => ['required', 'numeric', 'min:0'],
            'fecha_emision'   => [
                'required','date',
                Rule::unique('deudas')
                    ->where(fn($q) => $q->where('propiedad_id', $request->propiedad_id))
            ],
            'fecha_vencimiento' => ['nullable', 'date', 'after:fecha_emision'],
            'estado' => ['required', 'in:pendiente,pagada,vencida'],
            'pagada_adelantada' => ['boolean'],
        ]);

        // ✅ OBTENER TARIFA AUTOMÁTICAMENTE de la propiedad
        $propiedad = Property::findOrFail($data['propiedad_id']);
        $data['tarifa_id'] = $propiedad->tarifa_id;

        // ✅ VERIFICAR que la tarifa esté activa
        if (!$propiedad->tariff->activo) {
            return redirect()->back()
                ->withErrors(['propiedad_id' => 'La propiedad tiene una tarifa inactiva. No se puede generar deuda.'])
                ->withInput();
        }

        // Lógica automática para pagada_adelantada
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
        
        // ❌ ELIMINAR: No necesitas cargar propiedades ni tarifas para edición
        // ✅ Solo mostrar información, no permitir cambios que rompan integridad

        return view('admin.debts.edit', compact('debt'));
    }

    public function update(Request $request, Debt $debt)
    {
        // ✅ BLOQUEAR edición si está pagada
        if ($debt->estado === 'pagada') {
            return redirect()->back()
                ->with('error', 'No se puede editar una deuda pagada.');
        }

        $data = $request->validate([
            // ❌ ELIMINAR: propiedad_id y tarifa_id (no editables)
            'fecha_emision'   => [
                'required','date',
                Rule::unique('deudas')
                    ->where(fn($q) => $q->where('propiedad_id', $debt->propiedad_id))
                    ->ignore($debt->id)
            ],
            'fecha_vencimiento' => ['nullable', 'date', 'after:fecha_emision'],
            'estado' => ['required', 'in:pendiente,pagada,vencida'],
        ]);

        // ✅ MANTENER la tarifa original (integridad histórica)
        $data['tarifa_id'] = $debt->tarifa_id;
        $data['propiedad_id'] = $debt->propiedad_id;
        $data['monto_pendiente'] = $debt->tarifa->precio_mensual; // Mantener monto original

        // Pagada_adelantada automático
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
        // ✅ BLOQUEAR eliminación si está pagada
        if ($debt->estado === 'pagada') {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una deuda pagada.');
        }

        $debt->delete();

        return redirect()
            ->route('admin.debts.index')
            ->with('info', 'Deuda eliminada con éxito');
    }

    // ✅ NUEVO: Endpoint para obtener tarifa de una propiedad (AJAX)
    public function getPropertyTariff(Property $property)
    {
        return response()->json([
            'tarifa_id' => $property->tarifa_id,
            'tarifa_nombre' => $property->tariff->nombre,
            'precio_mensual' => $property->tariff->precio_mensual,
            'tarifa_activa' => $property->tariff->activo
        ]);
    }
}