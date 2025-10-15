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
    // ✅ CORREGIDO: Cambiar 'cliente' por 'propiedad.client'
    $query = Pago::with(['propiedad.client', 'registradoPor', 'propiedad.tariff']);

    // ✅ CORREGIDO: Búsqueda corregida
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->whereHas('propiedad.client', function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('ci', 'like', "%{$search}%")
                  ->orWhere('codigo_cliente', 'like', "%{$search}%");
            })->orWhereHas('propiedad', function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhere('barrio', 'like', "%{$search}%");
            });
        });
    }

    // ✅ CORREGIDO: Filtro por código cliente
    if ($request->filled('codigo_cliente')) {
        $query->whereHas('propiedad.client', function($q) use ($request) {
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
        
        if ($request->has('propiedad_id')) {
            $propiedadSeleccionada = Property::with(['client', 'tariff'])
                ->where('id', $request->propiedad_id)
                ->where('estado', 'activo')
                ->first();
                
            if ($propiedadSeleccionada) {
                $deudasPendientes = Debt::with('multas')
                    ->where('propiedad_id', $propiedadSeleccionada->id)
                    ->where('estado', 'pendiente')
                    ->orderBy('fecha_emision', 'asc')
                    ->get();
                
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
     * ✅ CORREGIDO: Obtener meses pendientes para una propiedad
     */
    private function obtenerMesesPendientes($propiedadId)
{
    try {
        \Log::info("🔍 Iniciando obtenerMesesPendientes para propiedad: {$propiedadId}");

        $propiedad = Property::find($propiedadId);
        if (!$propiedad) {
            \Log::warning("❌ Property no encontrada: {$propiedadId}");
            return [];
        }

        // Fecha de inicio (desde que se registró la propiedad o hace 2 años)
        $fechaRegistro = $propiedad->created_at ?? now()->subYears(2);
        $fechaInicio = Carbon::parse($fechaRegistro)->startOfMonth();
        $fechaFin = now()->endOfYear(); // Hasta fin de año actual

        \Log::info("📅 Rango de fechas - Desde: {$fechaInicio->format('Y-m')} Hasta: {$fechaFin->format('Y-m')}");

        // Generar todos los meses posibles
        $mesesPosibles = [];
        $fechaActual = $fechaInicio->copy();
        
        while ($fechaActual->lte($fechaFin)) {
            $mesesPosibles[] = $fechaActual->format('Y-m');
            $fechaActual->addMonth();
        }

        \Log::info("📅 Meses posibles generados: " . count($mesesPosibles));

        // Obtener meses ya pagados
        $mesesPagados = Pago::where('propiedad_id', $propiedadId)
            ->pluck('mes_pagado')
            ->toArray();

        \Log::info("💰 Meses ya pagados: " . json_encode($mesesPagados));

        // ✅ CORREGIDO: Obtener meses con deudas pendientes
        $mesesConDeudasPendientes = Debt::where('propiedad_id', $propiedadId)
            ->where('estado', 'pendiente')
            ->where('monto_pendiente', '>', 0)
            ->get()
            ->map(function($deuda) {
                return $deuda->fecha_emision->format('Y-m');
            })
            ->toArray();

        \Log::info("📋 Meses con deudas pendientes: " . json_encode($mesesConDeudasPendientes));

        // ✅ CORREGIDO: Filtrar meses pendientes (no pagados O con deuda pendiente)
        $mesesPendientes = array_unique(array_merge(
            array_diff($mesesPosibles, $mesesPagados), // Meses no pagados
            $mesesConDeudasPendientes // Meses con deudas pendientes
        ));
        
        // Ordenar cronológicamente
        sort($mesesPendientes);

        \Log::info("✅ Meses pendientes finales: " . json_encode($mesesPendientes) . " - Total: " . count($mesesPendientes));

        return $mesesPendientes;

    } catch (\Exception $e) {
        \Log::error('💥 Error en obtenerMesesPendientes: ' . $e->getMessage());
        return [];
    }
}
    /**
     * ✅ CORREGIDO: API para obtener meses pendientes (AJAX)
     */
    public function obtenerMesesPendientesApi($propiedadId)
{
    try {
        \Log::info("🔍 Iniciando obtenerMesesPendientesApi para propiedad: {$propiedadId}");

        $propiedad = Property::with(['client', 'tariff'])->find($propiedadId);

        if (!$propiedad) {
            return response()->json([
                'success' => false,
                'message' => 'Propiedad no encontrada'
            ], 404);
        }

        $mesesPendientesArray = $this->obtenerMesesPendientes($propiedadId);

        // ✅ CORREGIDO: Convertir array a objeto con formato para el frontend
        $mesesPendientesObj = [];
        foreach ($mesesPendientesArray as $mes) {
            $fecha = Carbon::createFromFormat('Y-m', $mes);
            $mesesPendientesObj[$mes] = $fecha->locale('es')->translatedFormat('F Y');
        }

        \Log::info("📊 Meses pendientes formateados: " . json_encode($mesesPendientesObj));

        return response()->json([
            'success' => true,
            'mesesPendientes' => $mesesPendientesObj, // ✅ Ahora es un objeto
            'propiedad' => [
                'id' => $propiedad->id,
                'referencia' => $propiedad->referencia,
                'cliente' => $propiedad->client->nombre ?? 'Cliente no asignado',
                'tarifa' => $propiedad->tariff->precio_mensual ?? 0
            ],
            'totalPendientes' => count($mesesPendientesArray)
        ]);

    } catch (\Exception $e) {
        \Log::error('💥 Error en obtenerMesesPendientesApi: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Error interno del servidor'
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
    // Validación corregida
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

        // Generar número de recibo
        $numeroRecibo = $this->generarNumeroRecibo();
        
        // Crear pagos individuales
        $pagosCreados = [];
        foreach ($meses as $mes) {
            $pago = Pago::create([
                'numero_recibo' => $numeroRecibo,
                'propiedad_id' => $request->propiedad_id,
                'mes_pagado' => $mes,
                'monto' => $tarifaMensual,
                'fecha_pago' => $request->fecha_pago,
                'metodo' => $request->metodo,
                'comprobante' => $request->comprobante,
                'observaciones' => $request->observaciones,
                'registrado_por' => auth()->id(),
            ]);

            $pagosCreados[] = $pago;

            // ✅ NUEVO: ACTUALIZAR DEUDA CORRESPONDIENTE
            $this->actualizarDeudaPorPago($request->propiedad_id, $mes);
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

// ✅ NUEVO MÉTODO: Actualizar deuda cuando se registra un pago
private function actualizarDeudaPorPago($propiedadId, $mesPagado)
{
    try {
        $deuda = Debt::where('propiedad_id', $propiedadId)
            ->whereYear('fecha_emision', Carbon::parse($mesPagado)->year)
            ->whereMonth('fecha_emision', Carbon::parse($mesPagado)->month)
            ->where('estado', 'pendiente')
            ->first();

        if ($deuda) {
            $deuda->update([
                'estado' => 'pagada',
                'updated_at' => now()
            ]);
            \Log::info("Deuda #{$deuda->id} actualizada a PAGADA - Mes: {$mesPagado}");
        }

    } catch (\Exception $e) {
        \Log::error("Error actualizando deuda para mes {$mesPagado}: " . $e->getMessage());
    }
}
    public function show(Pago $pago)
    {
        // ✅ CORREGIDO: Cargar 'propiedad.cliente' en lugar de 'cliente'
        $pago->load(['propiedad.cliente', 'registradoPor']);
        return view('admin.pagos.show', compact('pago'));
    }

    public function print(Pago $pago)
    {
        // ✅ CORREGIDO: Cargar 'propiedad.cliente' en lugar de 'cliente'
        $pagosDelRecibo = Pago::where('numero_recibo', $pago->numero_recibo)
                            ->with(['propiedad.cliente', 'registradoPor'])
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

    public function obtenerDeudasPendientes($propiedadId) // ✅ Cambiar parameter
    {
        try {
            \Log::info("🔍 Obteniendo deudas pendientes para propiedad: {$propiedadId}");

            $propiedad = Property::find($propiedadId);
            if (!$propiedad) {
                return response()->json([
                    'error' => 'Propiedad no encontrada'
                ], 404);
            }

            $deudasPendientes = Debt::with('multas')
                ->where('propiedad_id', $propiedadId)
                ->where('estado', 'pendiente')
                ->orderBy('fecha_emision', 'asc')
                ->get();

            // ✅ Usar el método corregido de Property
            $mesesAdeudados = $propiedad->obtenerMesesAdeudados();
            
            \Log::info("✅ Deudas pendientes encontradas: " . $deudasPendientes->count());

            return response()->json([
                'deudas' => $deudasPendientes,
                'meses_adeudados' => $mesesAdeudados,
                'total_deudas' => $deudasPendientes->sum('monto_pendiente')
            ]);

        } catch (\Exception $e) {
            \Log::error('💥 Error en obtenerDeudasPendientes: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    // En PagoController o un servicio separado
    public function sincronizarDeudasConPagos()
    {
        $deudasPendientes = Debt::where('estado', 'pendiente')->get();
        $actualizadas = 0;
        
        foreach ($deudasPendientes as $deuda) {
            $mesDeuda = $deuda->fecha_emision->format('Y-m');
            $pagoExiste = Pago::where('propiedad_id', $deuda->propiedad_id)
                            ->where('mes_pagado', $mesDeuda)
                            ->exists();
            
            if ($pagoExiste) {
                $deuda->update(['estado' => 'pagada']);
                $actualizadas++;
                \Log::info("Deuda #{$deuda->id} marcada como pagada - Mes: {$mesDeuda}");
            }
        }
        
        return $actualizadas;
    }
}