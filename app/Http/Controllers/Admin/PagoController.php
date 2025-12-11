<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use App\Models\Pago;
use App\Models\Property;
use App\Models\Debt; 
use App\Models\Fine; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.pagos.index')->only('index');
        $this->middleware('can:admin.pagos.create')->only(['create', 'store']);
        $this->middleware('can:admin.pagos.edit')->only(['edit', 'update']);
        $this->middleware('can:admin.pagos.show')->only('show');
        $this->middleware('can:admin.pagos.print')->only('print');
        $this->middleware('can:admin.pagos.anular')->only('anular');
        $this->middleware('can:admin.pagos.obtenerMesesPendientes')->only('obtenerMesesPendientesApi');
        $this->middleware('can:admin.pagos.validar-meses')->only('validarMeses');
        $this->middleware('can:admin.propiedades.deudaspendientes')->only('obtenerDeudasPendientes');
    }

    public function index(Request $request)
    {
        $query = Pago::with(['propiedad.client', 'registradoPor', 'propiedad.tariff']);

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

        if ($request->filled('codigo_cliente')) {
            $query->whereHas('propiedad.client', function($q) use ($request) {
                $q->where('codigo_cliente', 'like', "%{$request->codigo_cliente}%");
            });
        }

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
        $multasPendientes = collect();
        
        $esReconexion = $request->has('reconexion');
        $mesDesdeReconexion = $request->get('mes_desde');
        $mesHastaReconexion = $request->get('mes_hasta');
        $multaIdReconexion = $request->get('multa_id');
        $forzarPagoCompleto = $request->has('forzar_pago_completo');
        
        if ($request->has('propiedad_id')) {
            $propiedadSeleccionada = Property::with(['client', 'tariff'])
                ->where('id', $request->propiedad_id)
                ->whereIn('estado', [
                    Property::ESTADO_ACTIVO,
                    Property::ESTADO_CORTADO,
                    Property::ESTADO_CORTE_PENDIENTE
                ])
                ->first();
                
            if ($propiedadSeleccionada) {
                $deudasPendientes = Debt::with('multas')
                    ->where('propiedad_id', $propiedadSeleccionada->id)
                    ->where('estado', 'pendiente')
                    ->orderBy('fecha_emision', 'asc')
                    ->get();
                
                $mesesPendientes = $this->obtenerMesesPendientes($propiedadSeleccionada->id);
                
                $multasPendientes = Fine::with(['propiedad.client'])
                    ->where('propiedad_id', $propiedadSeleccionada->id)
                    ->where('estado', Fine::ESTADO_PENDIENTE)
                    ->where('activa', true)
                    ->orderBy('fecha_aplicacion', 'asc')
                    ->get();

                if ($esReconexion && $multaIdReconexion) {
                    $multaReconexion = $multasPendientes->firstWhere('id', $multaIdReconexion);
                    if ($multaReconexion) {
                        $multaReconexion->auto_seleccionar = true;
                    }
                }
            }
        }

        $propiedades = Property::with(['client', 'tariff'])
            ->whereIn('estado', [
                Property::ESTADO_ACTIVO,
                Property::ESTADO_CORTADO,
                Property::ESTADO_CORTE_PENDIENTE
            ])
            ->orderBy('referencia')
            ->get();
        
        return view('admin.pagos.create', compact(
            'propiedades', 
            'propiedadSeleccionada', 
            'deudasPendientes', 
            'mesesPendientes',
            'multasPendientes',
            'esReconexion',
            'mesDesdeReconexion',
            'mesHastaReconexion',
            'multaIdReconexion',
            'forzarPagoCompleto'
        ));
    }

    private function obtenerMesesPendientes($propiedadId)
    {
        try {
            $propiedad = Property::find($propiedadId);
            if (!$propiedad) {
                return [];
            }

            $fechaRegistro = $propiedad->created_at ?? now()->subYears(2);
            $fechaInicio = Carbon::parse($fechaRegistro)->startOfMonth();
            $fechaFin = now()->endOfYear();

            $mesesPosibles = [];
            $fechaActual = $fechaInicio->copy();
            
            while ($fechaActual->lte($fechaFin)) {
                $mesesPosibles[] = $fechaActual->format('Y-m');
                $fechaActual->addMonth();
            }

            $mesesPagados = Pago::where('propiedad_id', $propiedadId)
                ->pluck('mes_pagado')
                ->toArray();

            $mesesConDeudasPendientes = Debt::where('propiedad_id', $propiedadId)
                ->where('estado', 'pendiente')
                ->where('monto_pendiente', '>', 0)
                ->get()
                ->map(function($deuda) {
                    return $deuda->fecha_emision->format('Y-m');
                })
                ->toArray();

            $mesesPendientes = array_unique(array_merge(
                array_diff($mesesPosibles, $mesesPagados),
                $mesesConDeudasPendientes
            ));
            
            sort($mesesPendientes);

            return $mesesPendientes;

        } catch (\Exception $e) {
            \Log::error('Error en obtenerMesesPendientes: ' . $e->getMessage());
            return [];
        }
    }

    public function obtenerMesesPendientesApi($propiedadId)
    {
        try {
            $propiedad = Property::with(['client', 'tariff'])->find($propiedadId);

            if (!$propiedad) {
                return response()->json([
                    'success' => false,
                    'message' => 'Propiedad no encontrada'
                ], 404);
            }

            $mesesPendientesArray = $this->obtenerMesesPendientes($propiedadId);

            $mesesPendientesObj = [];
            foreach ($mesesPendientesArray as $mes) {
                $fecha = Carbon::createFromFormat('Y-m', $mes);
                $mesesPendientesObj[$mes] = $fecha->locale('es')->translatedFormat('F Y');
            }

            return response()->json([
                'success' => true,
                'mesesPendientes' => $mesesPendientesObj,
                'propiedad' => [
                    'id' => $propiedad->id,
                    'referencia' => $propiedad->referencia,
                    'cliente' => $propiedad->client->nombre ?? 'Cliente no asignado',
                    'tarifa' => $propiedad->tariff->precio_mensual ?? 0
                ],
                'totalPendientes' => count($mesesPendientesArray)
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en obtenerMesesPendientesApi: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

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

            if ($mesDesde > $mesHasta) {
                return response()->json([
                    'valido' => false,
                    'mensaje' => 'El mes final no puede ser anterior al mes inicial'
                ]);
            }

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
        
        while (Pago::where('numero_recibo', $numeroRecibo)->exists()) {
            $numero++;
            $numeroRecibo = 'REC-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
        }
        
        return $numeroRecibo;
    }

    public function store(Request $request)
{
    $request->validate([
        'propiedad_id' => 'required|exists:propiedades,id',
        'mes_desde' => 'required|date_format:Y-m',
        'mes_hasta' => 'required|date_format:Y-m',
        'fecha_pago' => 'required|date|before_or_equal:today',
        'metodo' => 'required|in:efectivo,transferencia,qr',
        'comprobante' => 'nullable|string|max:50',
        'observaciones' => 'nullable|string|max:255',
        'multas_seleccionadas' => 'nullable|array',
        'multas_seleccionadas.*' => 'exists:multas,id'
    ]);

    $pagosCreados = [];
    $multasSeleccionadas = collect();
    $mensajeReconexion = "";
    $multasMoraCreadas = collect(); // ✅ NUEVO: Para multas por mora creadas

    try {
        DB::beginTransaction();

        \Log::info("=== INICIANDO PROCESO DE PAGO ===");
        \Log::info("Propiedad ID: " . $request->propiedad_id);
        
        $propiedad = Property::with(['client', 'tariff'])->findOrFail($request->propiedad_id);
        
        if (!$propiedad->tariff) {
            throw new \Exception('La propiedad no tiene una tarifa asignada');
        }

        $tarifaMensual = $propiedad->tariff->precio_mensual;

        $mesDesde = $request->mes_desde;
        $mesHasta = $request->mes_hasta;
        
        if (!$mesDesde || !$mesHasta) {
            throw new \Exception('Los meses desde y hasta son requeridos');
        }

        $meses = $this->generarRangoMeses($mesDesde, $mesHasta);
        $mesesPagadosCount = count($meses);

        if (empty($meses)) {
            throw new \Exception('No se pudo generar el rango de meses. Verifique las fechas.');
        }

        // ✅ CORREGIDO: VALIDACIÓN DE PAGO SECUENCIAL MEJORADA
        $mesesAdeudados = $propiedad->obtenerMesesAdeudados();
        sort($mesesAdeudados);

        if (!empty($mesesAdeudados)) {
            $primerMesAdeudado = $mesesAdeudados[0];
            
            \Log::info("🔍 Validando secuencia - Primer mes adeudado: {$primerMesAdeudado}");
            \Log::info("Meses adeudados totales: " . implode(', ', $mesesAdeudados));
            \Log::info("Meses a pagar: " . implode(', ', $meses));
            
            if ($mesDesde !== $primerMesAdeudado) {
                DB::rollBack();
                $primerMesFormateado = Carbon::createFromFormat('Y-m', $primerMesAdeudado)->locale('es')->translatedFormat('F Y');
                $mesDesdeFormateado = Carbon::createFromFormat('Y-m', $mesDesde)->locale('es')->translatedFormat('F Y');
                
                \Log::warning("❌ Validación secuencial fallida - Debe pagar desde {$primerMesAdeudado}, está pagando desde {$mesDesde}");
                
                return redirect()
                    ->route('admin.pagos.create', ['propiedad_id' => $propiedad->id])
                    ->withErrors([
                        'error' => "Debe pagar desde el primer mes adeudado ({$primerMesFormateado}). " .
                                "No puede pagar desde {$mesDesdeFormateado} sin antes pagar los meses anteriores."
                    ])
                    ->withInput();
            }
            
            $mesesSeleccionadosOrdenados = $meses;
            sort($mesesSeleccionadosOrdenados);
            
            $mesesEsperados = array_slice($mesesAdeudados, 0, count($mesesSeleccionadosOrdenados));
            
            if ($mesesSeleccionadosOrdenados !== $mesesEsperados) {
                DB::rollBack();
                
                $mesesEsperadosFormateados = array_map(function($mes) {
                    return Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('F Y');
                }, $mesesEsperados);
                
                $mesesSeleccionadosFormateados = array_map(function($mes) {
                    return Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('F Y');
                }, $mesesSeleccionadosOrdenados);
                
                \Log::warning("❌ Validación de secuencia fallida - Esperados: " . implode(', ', $mesesEsperados) . " - Seleccionados: " . implode(', ', $mesesSeleccionadosOrdenados));
                
                return redirect()
                    ->route('admin.pagos.create', ['propiedad_id' => $propiedad->id])
                    ->withErrors([
                        'error' => "Debe pagar los meses en orden secuencial. " .
                                "Los próximos meses a pagar son: " . implode(', ', $mesesEsperadosFormateados) . ". " .
                                "No puede saltar meses pendientes."
                    ])
                    ->withInput();
            }
            
            \Log::info("✅ Validación secuencial exitosa");
        }

        // ✅ CORREGIDO: VALIDACIÓN DE PAGO COMPLETO PARA RECONEXIÓN
        $esReconexion = $request->has('reconexion') || $request->has('forzar_pago_completo');

        if ($esReconexion) {
            try {
                \Log::info("=== PROCESANDO RECONEXIÓN ===");
                
                $propiedad->forzarReconexionPendiente();
                
                if ($propiedad->estado === Property::ESTADO_CORTE_PENDIENTE && 
                    $propiedad->tipo_trabajo_pendiente === Property::TRABAJO_RECONEXION) {
                    
                    \Log::info("✅ RECONEXIÓN EXITOSA - Propiedad {$propiedad->id} ahora visible para operadores");
                    $mensajeReconexion = " La propiedad ha sido puesta en COLA DE RECONEXIÓN para el equipo de operaciones.";
                } else {
                    \Log::error("❌ FALLA EN RECONEXIÓN - Estado: {$propiedad->estado}, Trabajo: {$propiedad->tipo_trabajo_pendiente}");
                    $mensajeReconexion = " ⚠️ Error al programar reconexión. Contacte al administrador.";
                }
                
            } catch (\Exception $e) {
                \Log::error("❌ ERROR CRÍTICO en reconexión: " . $e->getMessage());
                $mensajeReconexion = " ⚠️ Error al programar reconexión: " . $e->getMessage();
            }
        }

        // ✅ CREAR PAGOS Y CALCULAR MULTAS POR MORA
        $numeroReciboBase = $this->generarNumeroRecibo();
        $pagoPrincipalId = null;
        
        foreach ($meses as $index => $mes) {
            // Generar número de recibo (el primero para el recibo principal, luego recibo-hijo)
            $numeroRecibo = ($index === 0) 
                ? $numeroReciboBase 
                : $numeroReciboBase . '-' . ($index + 1);
            
            // Verificar si el mes está atrasado para aplicar multa
            $mesPagado = Carbon::createFromFormat('Y-m', $mes);
            $hoy = now()->startOfMonth();
            $mesesAtrasados = $mesPagado->diffInMonths($hoy);
            
            $montoPago = $tarifaMensual;
            $multaMora = null;
            $mesesAtrasadosEntero = (int) $mesesAtrasados; // Convertir a entero

            if ($mesesAtrasadosEntero >= 3) { // 3 o más meses de atraso
                $config = \App\Models\ConfigMultaMora::getConfiguracionActiva();
                
                if ($config && $config->activo && $mesesAtrasadosEntero >= $config->meses_gracia) {
                    $multaMonto = $tarifaMensual * ($config->porcentaje_multa / 100);
                    
                    // Crear multa automática por mora
                    $multaMora = Fine::create([
                        'propiedad_id' => $propiedad->id,
                        'tipo' => Fine::TIPO_MORA_PAGO,
                        'nombre' => "Multa por mora - " . Carbon::parse($mes)->translatedFormat('F Y'),
                        'descripcion' => "Pago del mes {$mes} registrado con {$mesesAtrasadosEntero} mes(es) de atraso. " .
                                        "Se aplicó {$config->porcentaje_multa}% de multa según configuración '{$config->nombre}'",
                        'monto' => $multaMonto,
                        'porcentaje_aplicado' => $config->porcentaje_multa, // ✅ NUEVO
                        'meses_atraso' => $mesesAtrasadosEntero, // ✅ NUEVO
                        'mes_aplicado' => $mes, // ✅ NUEVO
                        'fecha_aplicacion' => now(),
                        'estado' => Fine::ESTADO_PENDIENTE,
                        'aplicada_automaticamente' => true,
                        'activa' => true,
                        'creado_por' => auth()->id(),
                    ]);
                    
                    $multasMoraCreadas->push($multaMora);
                    $montoPago += $multaMonto; // Agregar multa al monto total
                    
                    \Log::info("✅ Multa por mora creada - Mes: {$mes}, Atraso: {$mesesAtrasados} meses, Multa: Bs {$multaMonto}");
                }
            }
            
            $pago = Pago::create([
                'numero_recibo' => $numeroRecibo,
                'propiedad_id' => $propiedad->id,
                'monto' => $montoPago,
                'mes_pagado' => $mes,
                'fecha_pago' => $request->fecha_pago,
                'metodo' => $request->metodo,
                'comprobante' => $request->comprobante,
                'observaciones' => $this->generarObservacionesConMulta($request->observaciones, $multaMora),
                'registrado_por' => auth()->id(),
            ]);
            
            // Guardar ID del primer pago para asociar multas
            if ($index === 0) {
                $pagoPrincipalId = $pago->id;
            }
            
            $pagosCreados[] = $pago;
            
            // Si hay multa por mora, asociarla al pago
            if ($multaMora) {
                $pago->multasPagadas()->attach($multaMora->id, [
                    'monto_pagado' => $multaMora->monto,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Actualizar deudas si existen
            $this->actualizarDeudaPorPago($propiedad->id, $mes);
        }

        // ✅ PROCESAR Y ASOCIAR MULTAS SELECCIONADAS MANUALMENTE
        if ($request->has('multas_seleccionadas')) {
            \Log::info("🔍 Procesando multas seleccionadas: " . json_encode($request->multas_seleccionadas));
            
            foreach ($request->multas_seleccionadas as $multaId) {
                $multa = Fine::find($multaId);
                if ($multa && $multa->estado === Fine::ESTADO_PENDIENTE) {
                    
                    try {
                        // ✅ ASOCIAR MULTA CON EL PRIMER PAGO DEL RECIBO
                        $primerPago = $pagosCreados[0];
                        $primerPago->multasPagadas()->attach($multaId, [
                            'monto_pagado' => $multa->monto,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        // ✅ MARCAR MULTA COMO PAGADA
                        $multa->update(['estado' => Fine::ESTADO_PAGADA]);
                        
                        $multasSeleccionadas->push($multa);
                        
                        \Log::info("✅ Multa #{$multa->id} '{$multa->nombre}' asociada al pago #{$primerPago->id} - Monto: {$multa->monto}");
                        
                    } catch (\Exception $e) {
                        \Log::error("❌ Error asociando multa #{$multaId}: " . $e->getMessage());
                    }
                } else {
                    \Log::warning("⚠️ Multa #{$multaId} no encontrada o ya está pagada");
                }
            }
        } else {
            \Log::info("ℹ️ No hay multas seleccionadas en el request");
        }

        DB::commit();

        $mensaje = $this->generarMensajeExito(
            $pagosCreados, 
            $multasSeleccionadas, 
            $propiedad, 
            $mesesPagadosCount, 
            $esReconexion,
            $multasMoraCreadas // ✅ NUEVO: Pasar multas por mora creadas
        ) . $mensajeReconexion;

        return redirect()->route('admin.pagos.index')->with('info', $mensaje);

    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error al crear pago: ' . $e->getMessage());
        \Log::error('Trace: ' . $e->getTraceAsString());
        
        $errorMessage = 'Error al registrar los pagos: ' . $e->getMessage();
        
        return redirect()
            ->route('admin.pagos.create', ['propiedad_id' => $request->propiedad_id])
            ->withErrors(['error' => $errorMessage])
            ->withInput();
    }
}

// ✅ NUEVO MÉTODO: Generar observaciones con información de multa
private function generarObservacionesConMulta($observacionesOriginal, $multaMora)
{
    $observaciones = $observacionesOriginal ?? '';
    
    if ($multaMora) {
        $infoMulta = "\n[MULTA POR MORA: Bs " . number_format($multaMora->monto, 2) . "]";
        
        if (!empty($observaciones)) {
            return $observaciones . $infoMulta;
        }
        return $infoMulta;
    }
    
    return $observaciones;
}

// ✅ ACTUALIZAR método generarMensajeExito
private function generarMensajeExito($pagosCreados, $multasSeleccionadas, $propiedad, $mesesPagadosCount, $esReconexion = false, $multasMoraCreadas = null)
{
    $mensaje = count($pagosCreados) > 1 
        ? "Se registraron " . count($pagosCreados) . " pagos exitosamente" 
        : "Pago registrado exitosamente";

    // Multas seleccionadas manualmente
    if ($multasSeleccionadas->count() > 0) {
        $mensaje .= " y " . $multasSeleccionadas->count() . " multa(s) pagada(s)";
    }
    
    // Multas por mora automáticas
    if ($multasMoraCreadas && $multasMoraCreadas->count() > 0) {
        $totalMultasMora = $multasMoraCreadas->sum('monto');
        $mensaje .= " con " . $multasMoraCreadas->count() . " multa(s) por mora (Bs " . number_format($totalMultasMora, 2) . ")";
    }

    if ($esReconexion) {
        $mensaje .= ". ✅ Pago completo para reconexión validado.";
    }

    return $mensaje;
}
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
        $pago->load(['propiedad.client', 'registradoPor']);
        return view('admin.pagos.show', compact('pago'));
    }

    public function print(Pago $pago)
    {
        $pagosDelRecibo = Pago::where('numero_recibo', $pago->numero_recibo)
                        ->with([
                            'propiedad.client', 
                            'registradoPor', 
                            'propiedad.tariff',
                            'multasPagadas'
                        ])
                        ->orderBy('mes_pagado', 'asc')
                        ->get();
    
        $pagoPrincipal = $pagosDelRecibo->first();
        
        $multasPagadas = $pagosDelRecibo->pluck('multasPagadas')->flatten()->unique('id');
        
        return view('admin.pagos.print', compact('pagoPrincipal', 'pagosDelRecibo', 'multasPagadas'));
    }

    public function anular(Pago $pago)
    {
        $pagosAnular = Pago::where('numero_recibo', $pago->numero_recibo)->get();
        
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

    public function obtenerDeudasPendientes($propiedadId)
    {
        try {
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

            $mesesAdeudados = $propiedad->obtenerMesesAdeudados();
            
            return response()->json([
                'deudas' => $deudasPendientes,
                'meses_adeudados' => $mesesAdeudados,
                'total_deudas' => $deudasPendientes->sum('monto_pendiente')
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en obtenerDeudasPendientes: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

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

    public function obtenerMultasPendientesApi($propiedadId)
    {
        try {
            $multasPendientes = Fine::with(['propiedad.client'])
                ->where('propiedad_id', $propiedadId)
                ->where('estado', Fine::ESTADO_PENDIENTE)
                ->where('activa', true)
                ->orderBy('fecha_aplicacion', 'asc')
                ->get()
                ->map(function($multa) {
                    return [
                        'id' => $multa->id,
                        'nombre' => $multa->nombre,
                        'descripcion' => $multa->descripcion,
                        'monto' => $multa->monto,
                        'tipo_nombre' => $multa->nombre_tipo,
                        'fecha_aplicacion_formateada' => $multa->fecha_aplicacion->format('d/m/Y')
                    ];
                });

            return response()->json([
                'success' => true,
                'multasPendientes' => $multasPendientes,
                'total' => $multasPendientes->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en obtenerMultasPendientesApi: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar multas pendientes'
            ], 500);
        }
    }
    private function verificarMesesConsecutivos($mesesPagados, $todosMesesAdeudados)
{
    if (empty($mesesPagados) || empty($todosMesesAdeudados)) {
        return false;
    }
    
    // Ordenar ambos arrays
    sort($mesesPagados);
    sort($todosMesesAdeudados);
    
    // Encontrar el índice del primer mes pagado en la lista completa
    $primerMesPagado = $mesesPagados[0];
    $indiceInicio = array_search($primerMesPagado, $todosMesesAdeudados);
    
    if ($indiceInicio === false) {
        return false;
    }
    
    // Verificar que los meses pagados sean consecutivos desde el inicio
    for ($i = 0; $i < count($mesesPagados); $i++) {
        $mesEsperado = $todosMesesAdeudados[$indiceInicio + $i];
        $mesPagado = $mesesPagados[$i];
        
        if ($mesPagado !== $mesEsperado) {
            \Log::warning("❌ Mes no consecutivo - Esperado: {$mesEsperado}, Pagado: {$mesPagado}");
            return false;
        }
    }
    
    return true;
}
}