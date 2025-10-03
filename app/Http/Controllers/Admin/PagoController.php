<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Property;
use App\Models\Debt; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagoController extends Controller
{
    public function index(Request $request)
    {
        $query = Pago::with(['cliente', 'propiedad', 'registradoPor', 'propiedad.tariff']);

        // Búsqueda - VERSIÓN SEGURA
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('cliente', function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('ci', 'like', "%{$search}%");
                })->orWhereHas('propiedad', function($q) use ($search) {
                    $q->where('referencia', 'like', "%{$search}%")
                    ->orWhere('barrio', 'like', "%{$search}%");
                });
            });
        }

        // Resto de filtros...
        if ($request->filled('mes')) {
            $query->where('mes_pagado', $request->mes);
        }

        if ($request->filled('metodo')) {
            $query->where('metodo', $request->metodo);
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_pago', '>=', $request->fecha_desde);
        }
        
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_pago', '<=', $request->fecha_hasta);
        }

        $pagos = $query->orderBy('fecha_pago', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

        return view('admin.pagos.index', compact('pagos'));
    }

    public function create(Request $request)
    {
        $propiedadSeleccionada = null;
        $deudasPendientes = collect();
        
        // ✅ Si viene propiedad_id por URL, cargar esa propiedad Y SUS DEUDAS
        if ($request->has('propiedad_id')) {
            $propiedadSeleccionada = Property::with(['client', 'tariff'])
                ->where('id', $request->propiedad_id)
                ->where('estado', 'activo')
                ->first();
                
            if ($propiedadSeleccionada) {
                // CARGAR DEUDAS PENDIENTES de esta propiedad
                $deudasPendientes = Debt::with('multas')
                    ->where('propiedad_id', $propiedadSeleccionada->id)
                    ->where('estado', 'pendiente')
                    ->orderBy('fecha_emision', 'asc')
                    ->get();
            }
        }
        
        $propiedades = Property::with(['client', 'tariff'])
                            ->where('estado', 'activo')
                            ->orderBy('referencia')
                            ->get();
        
        return view('admin.pagos.create', compact('propiedades', 'propiedadSeleccionada', 'deudasPendientes'));
    }
    private function generarNumeroRecibo()
    {
        $ultimoPago = Pago::orderBy('id', 'desc')->first();
        
        if ($ultimoPago && preg_match('/REC-(\d+)/', $ultimoPago->numero_recibo, $matches)) {
            $numero = intval($matches[1]) + 1;
        } else {
            // Si no hay pagos o el formato es diferente, empezar desde 1
            $numero = 1;
        }
        
        return 'REC-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function store(Request $request)
    {
        // Validación
        $request->validate([
            'propiedad_id' => 'required|exists:propiedades,id',
            'mes_desde' => 'required|date_format:Y-m',
            'mes_hasta' => 'required|date_format:Y-m',
            'fecha_pago' => 'required|date|before_or_equal:today',
            'metodo' => 'required|in:efectivo,transferencia,qr',
            'comprobante' => 'nullable|string|max:50',
            'observaciones' => 'nullable|string|max:255'
        ]);
    
        try {
            DB::beginTransaction();
    
            $propiedad = Property::with(['client', 'tariff'])->findOrFail($request->propiedad_id);
            
            if (!$propiedad->tariff) {
                throw new \Exception('La propiedad no tiene una tarifa asignada');
            }
    
            $tarifaMensual = $propiedad->tariff->precio_mensual;
    
            // Calcular meses a pagar
            $start = Carbon::createFromFormat('Y-m', $request->mes_desde);
            $end = Carbon::createFromFormat('Y-m', $request->mes_hasta);
            $meses = [];
    
            $current = clone $start;
            while ($current <= $end) {
                $meses[] = $current->format('Y-m');
                $current->addMonth();
            }
    
            // Verificar meses ya pagados
            $mesesPagados = Pago::where('propiedad_id', $request->propiedad_id)
                ->whereIn('mes_pagado', $meses)
                ->pluck('mes_pagado')
                ->toArray();
    
            if (!empty($mesesPagados)) {
                $mesesPagadosFormateados = array_map(function($mes) {
                    return Carbon::createFromFormat('Y-m', $mes)->format('F Y');
                }, $mesesPagados);
    
                return back()->withErrors([
                    'mes_desde' => 'Los siguientes meses ya están pagados: ' . 
                                  implode(', ', $mesesPagadosFormateados)
                ])->withInput();
            }
    
            // Crear pagos individuales - ✅ INCLUIR numero_recibo
            $pagosCreados = [];
            foreach ($meses as $mes) {
                $pago = Pago::create([
                    'numero_recibo' => $this->generarNumeroRecibo(),
                    'propiedad_id' => $request->propiedad_id,
                    'cliente_id' => $propiedad->cliente_id,
                    'mes_pagado' => $mes,
                    'monto' => $tarifaMensual,
                    'fecha_pago' => $request->fecha_pago,
                    'metodo' => $request->metodo,
                    'comprobante' => $request->comprobante,
                    'observaciones' => $request->observaciones,
                    'registrado_por' => auth()->id(),
                ]);
    
                $pagosCreados[] = $pago;
            }
    
            DB::commit();
    
            $mensaje = count($pagosCreados) > 1 
                ? "Se registraron " . count($pagosCreados) . " pagos exitosamente" 
                : "Pago registrado exitosamente";
    
            return redirect()->route('admin.pagos.index')->with('info', $mensaje);
    
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al crear pago: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Error al registrar los pagos: ' . $e->getMessage()])->withInput();
        }
    }

    public function show(Pago $pago)
    {
        $pago->load(['cliente', 'propiedad', 'registradoPor']);
        return view('admin.pagos.show', compact('pago'));
    }

    public function print(Pago $pago)
    {
        $pago->load(['cliente', 'propiedad', 'registradoPor']);
        return view('admin.pagos.print', compact('pago'));
    }

    public function anular(Pago $pago)
    {
        // Validar que no tenga más de 30 días
        if (!$pago->fecha_pago->greaterThanOrEqualTo(now()->subDays(30))) {
            return redirect()->back()
                ->with('error', 'No se puede anular un pago con más de 30 días de antigüedad.');
        }

        $pago->delete();

        return redirect()->route('admin.pagos.index')
            ->with('info', 'Pago anulado correctamente');
    }
    public function obtenerDeudasPendientes(Property $propiedad)
{
    $deudasPendientes = Debt::with('multas')
        ->where('propiedad_id', $propiedad->id)
        ->where('estado', 'pendiente')
        ->orderBy('fecha_emision', 'asc')
        ->get();
    
    $mesesAdeudados = $propiedad->obtenerMesesAdeudados();
    
    return response()->json([
        'deudas' => $deudasPendientes,
        'meses_adeudados' => $mesesAdeudados,
        'total_deudas' => $deudasPendientes->sum('monto_pendiente')
    ]);
}
}