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

        // ✅ BÚSQUEDA INCLUYE CÓDIGO CLIENTE
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('cliente', function($q) use ($search) {
                    $q->where('nombre', 'like', "%{$search}%")
                      ->orWhere('ci', 'like', "%{$search}%")
                      ->orWhere('codigo_cliente', 'like', "%{$search}%");
                })->orWhereHas('propiedad', function($q) use ($search) {
                    $q->where('referencia', 'like', "%{$search}%")
                      ->orWhere('barrio', 'like', "%{$search}%");
                });
            });
        }

        // ✅ FILTRO POR CÓDIGO CLIENTE
        if ($request->filled('codigo_cliente')) {
            $query->whereHas('cliente', function($q) use ($request) {
                $q->where('codigo_cliente', 'like', "%{$request->codigo_cliente}%");
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
        $mesesPendientes = [];
        
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
                
                // ✅ NUEVO: CARGAR MESES PENDIENTES para la propiedad seleccionada
                $mesesPendientes = $this->obtenerMesesPendientes($propiedadSeleccionada->id);
            }
        }
        
        $propiedades = Property::with(['client', 'tariff'])
                            ->where('estado', 'activo')
                            ->orderBy('referencia')
                            ->get();
        
        return view('admin.pagos.create', compact('propiedades', 'propiedadSeleccionada', 'deudasPendientes', 'mesesPendientes'));
    }

    /**
     * ✅ NUEVO: Obtener meses pendientes para una propiedad
     */
    private function obtenerMesesPendientes($propiedadId)
    {
        // Obtener meses ya pagados para esta propiedad
        $mesesPagados = Pago::where('propiedad_id', $propiedadId)
            ->pluck('mes_pagado')
            ->toArray();

        // Generar lista de últimos 12 meses + año actual completo
        $mesesPendientes = [];
        $startDate = now()->subMonths(12)->startOfMonth();
        $endDate = now()->endOfYear();
        
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $mesFormato = $current->format('Y-m');
            
            // Solo incluir si NO está pagado
            if (!in_array($mesFormato, $mesesPagados)) {
                $mesesPendientes[$mesFormato] = $current->locale('es')->translatedFormat('F Y');
            }
            
            $current->addMonth();
        }

        return $mesesPendientes;
    }

    /**
     * ✅ NUEVO: API para obtener meses pendientes (AJAX)
     */
    public function obtenerMesesPendientesApi($propiedadId)
    {
        try {
            $propiedad = Property::with(['client', 'tariff'])->findOrFail($propiedadId);
            $mesesPendientes = $this->obtenerMesesPendientes($propiedadId);

            return response()->json([
                'success' => true,
                'mesesPendientes' => $mesesPendientes,
                'propiedad' => [
                    'id' => $propiedad->id,
                    'referencia' => $propiedad->referencia,
                    'cliente' => $propiedad->client->nombre,
                    'tarifa' => $propiedad->tariff->precio_mensual
                ],
                'totalPendientes' => count($mesesPendientes)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener meses pendientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NUEVO: Validación en tiempo real de meses
     */
    public function validarMeses(Request $request)
    {
        try {
            $propiedadId = $request->propiedad_id;
            $mesDesde = $request->mes_desde;
            $mesHasta = $request->mes_hasta;

            if (!$propiedadId || !$mesDesde || !$mesHasta) {
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'Datos incompletos'
                ]);
            }

            // Verificar que el rango sea válido
            if ($mesDesde > $mesHasta) {
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'El mes final no puede ser anterior al mes inicial'
                ]);
            }

            // Obtener meses pagados en el rango seleccionado
            $mesesEnRango = $this->generarRangoMeses($mesDesde, $mesHasta);
            $mesesPagados = Pago::where('propiedad_id', $propiedadId)
                ->whereIn('mes_pagado', $mesesEnRango)
                ->pluck('mes_pagado')
                ->toArray();

            if (!empty($mesesPagados)) {
                $mesesFormateados = array_map(function($mes) {
                    return Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('F Y');
                }, $mesesPagados);

                return response()->json([
                    'valido' => false,
                    'mensaje' => 'Algunos meses ya están pagados: ' . implode(', ', $mesesFormateados),
                    'meses_pagados' => $mesesPagados
                ]);
            }

            return response()->json([
                'valido' => true,
                'mensaje' => 'Rango de meses válido'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'valido' => false,
                'mensaje' => 'Error en validación: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ NUEVO: Generar array de meses en un rango
     */
    private function generarRangoMeses($mesDesde, $mesHasta)
    {
        $meses = [];
        $current = Carbon::createFromFormat('Y-m', $mesDesde);
        $hasta = Carbon::createFromFormat('Y-m', $mesHasta);
        
        while ($current <= $hasta) {
            $meses[] = $current->format('Y-m');
            $current->addMonth();
        }
        
        return $meses;
    }

    private function generarNumeroRecibo()
    {
        $ultimoPago = Pago::orderBy('id', 'desc')->first();
        
        if ($ultimoPago && preg_match('/REC-(\d+)/', $ultimoPago->numero_recibo, $matches)) {
            $numero = intval($matches[1]) + 1;
        } else {
            $numero = 1;
        }
        
        $numeroRecibo = 'REC-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        
        // ✅ VERIFICAR que el número no exista
        while (Pago::where('numero_recibo', $numeroRecibo)->exists()) {
            $numero++;
            $numeroRecibo = 'REC-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        }
        
        return $numeroRecibo;
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
            $meses = $this->generarRangoMeses($request->mes_desde, $request->mes_hasta);

            // Verificar meses ya pagados
            $mesesPagados = Pago::where('propiedad_id', $request->propiedad_id)
                ->whereIn('mes_pagado', $meses)
                ->pluck('mes_pagado')
                ->toArray();

            if (!empty($mesesPagados)) {
                $mesesPagadosFormateados = array_map(function($mes) {
                    return Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('F Y');
                }, $mesesPagados);

                DB::rollBack();

                return redirect()
                    ->route('admin.pagos.create', ['propiedad_id' => $request->propiedad_id])
                    ->withErrors([
                        'mes_desde' => 'Los siguientes meses ya están pagados: ' . 
                                      implode(', ', $mesesPagadosFormateados)
                    ])
                    ->withInput();
            }

            // ✅ Generar UN solo número de recibo para todos los pagos
            $numeroRecibo = $this->generarNumeroRecibo();
            
            // Crear pagos individuales - ✅ TODOS con el MISMO numero_recibo
            $pagosCreados = [];
            foreach ($meses as $mes) {
                $pago = Pago::create([
                    'numero_recibo' => $numeroRecibo,
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
            
            return redirect()
                ->route('admin.pagos.create', ['propiedad_id' => $request->propiedad_id])
                ->withErrors(['error' => 'Error al registrar los pagos: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Pago $pago)
    {
        $pago->load(['cliente', 'propiedad', 'registradoPor']);
        return view('admin.pagos.show', compact('pago'));
    }

    public function print(Pago $pago)
    {
        // ✅ Cargar TODOS los pagos con el mismo número de recibo
        $pagosDelRecibo = Pago::where('numero_recibo', $pago->numero_recibo)
                            ->with(['cliente', 'propiedad', 'registradoPor'])
                            ->orderBy('mes_pagado', 'asc')
                            ->get();
        
        $pagoPrincipal = $pagosDelRecibo->first();
        
        return view('admin.pagos.print', compact('pagoPrincipal', 'pagosDelRecibo'));
    }

    public function anular(Pago $pago)
    {
        // ✅ Anular TODOS los pagos con el mismo número de recibo
        $pagosAnular = Pago::where('numero_recibo', $pago->numero_recibo)->get();
        
        // Validar que no tenga más de 30 días
        if (!$pago->fecha_pago->greaterThanOrEqualTo(now()->subDays(30))) {
            return redirect()->back()
                ->with('error', 'No se puede anular un pago con más de 30 días de antigüedad.');
        }

        foreach ($pagosAnular as $pagoAnular) {
            $pagoAnular->delete();
        }

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