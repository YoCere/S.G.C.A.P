<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use App\Http\Requests\PropertyRequest;
use App\Models\Property;
use App\Models\Client;
use App\Models\Tariff;
use App\Models\Fine;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.properties.index')->only('index');
        $this->middleware('can:admin.properties.create')->only(['create', 'store']);
        $this->middleware('can:admin.properties.edit')->only(['edit', 'update']);
        $this->middleware('can:admin.properties.show')->only('show');
        $this->middleware('can:admin.properties.destroy')->only('destroy');
        $this->middleware('can:admin.properties.cut')->only('cutService');
        $this->middleware('can:admin.properties.restore')->only('restoreService');
        $this->middleware('can:admin.properties.cancel-cut')->only('cancelCutService');
        $this->middleware('can:admin.properties.request-reconnection')->only('requestReconnection');
        $this->middleware('can:admin.propiedades.search')->only('search');
    }
    
    public function index(Request $request)
    {
        $query = Property::with(['client', 'tariff']);

        // ✅ NUEVO: FILTRO POR CÓDIGO DE CLIENTE
        if ($request->filled('codigo_cliente')) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('codigo_cliente', 'like', "%{$request->codigo_cliente}%");
            });
        }

        // ✅ ACTUALIZADO: BÚSQUEDA GENERAL INCLUYE CÓDIGO CLIENTE
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhere('barrio', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%")
                        ->orWhere('codigo_cliente', 'like', "%{$search}%"); // ✅ NUEVO
                  });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('barrio')) {
            $query->where('barrio', $request->barrio);
        }

        if ($request->filled('tarifa_id')) {
            $query->where('tarifa_id', $request->tarifa_id);
        }

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }

        if ($request->filled('estado_cuenta')) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('estado_cuenta', $request->estado_cuenta);
            });
        }

        // Ordenamiento
        switch ($request->orden) {
            case 'antiguo':
                $query->orderBy('created_at', 'asc');
                break;
            case 'referencia':
                $query->orderBy('referencia', 'asc');
                break;
            case 'cliente':
                $query->join('clientes', 'propiedades.cliente_id', '=', 'clientes.id')
                      ->orderBy('clientes.nombre', 'asc')
                      ->select('propiedades.*');
                break;
            case 'barrio':
                $query->orderBy('barrio', 'asc');
                break;
            default: // reciente
                $query->orderBy('created_at', 'desc');
                break;
        }

        $properties = $query->paginate(20);

        // 🆕 ACTUALIZADO: Estadísticas incluyen pendientes_conexion
        $estadisticas = [
            'pendientes_conexion' => Property::where('estado', Property::ESTADO_PENDIENTE_CONEXION)->count(),
            'activas' => Property::where('estado', Property::ESTADO_ACTIVO)->count(),
            'corte_pendiente' => Property::where('estado', Property::ESTADO_CORTE_PENDIENTE)->count(),
            'cortadas' => Property::where('estado', Property::ESTADO_CORTADO)->count(),
            'con_ubicacion' => Property::whereNotNull('latitud')->whereNotNull('longitud')->count(),
            'clientes_activos' => Client::where('estado_cuenta', 'activo')->count(),
        ];

        $clients = Client::orderBy('nombre')->get();
        $tariffs = Tariff::orderBy('nombre')->get();

        return view('admin.properties.index', compact(
            'properties', 
            'clients', 
            'tariffs',
            'estadisticas'
        ))->with('totalPropiedades', Property::count());
    }

    public function create()
    {
        $clients = Client::orderBy('nombre')->get(['id', 'nombre', 'ci', 'codigo_cliente']);
        $tariffs = Tariff::activas()->orderBy('nombre')->get();
        
        return view('admin.properties.create', compact('clients', 'tariffs'));
    }

    public function store(PropertyRequest $request)
    {
        try {
            // 🆕 ESTABLECER ESTADO POR DEFECTO: pendiente_conexion
            $data = $request->validated();
            $data['estado'] = Property::ESTADO_PENDIENTE_CONEXION;
            $data['tipo_trabajo_pendiente'] = Property::TRABAJO_CONEXION_NUEVA; // 🆕 NUEVO
            
            Property::create($data);
            
            return redirect()->route('admin.properties.index')
                ->with('info', 'Propiedad creada con éxito - Estado: Pendiente de Conexión');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al crear la propiedad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(Property $property)
    {
        $property->load(['client', 'tariff', 'debts']);
        return view('admin.properties.show', compact('property'));
    }

    public function edit(Property $property)
    {
        $clients = Client::orderBy('nombre')->get(['id', 'nombre', 'ci', 'codigo_cliente']);
        $tariffs = Tariff::orderBy('activo', 'desc')->orderBy('nombre')->get();
        
        return view('admin.properties.edit', compact('property', 'clients', 'tariffs'));
    }

    public function update(PropertyRequest $request, Property $property)
    {
        try {
            $property->update($request->validated());
            
            return redirect()->route('admin.properties.index')
                ->with('info', 'Propiedad actualizada correctamente');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar la propiedad: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy(Property $property)
    {
        try {
            $property->delete();
            return redirect()->route('admin.properties.index')
                ->with('info', 'Propiedad eliminada con éxito');
                
        } catch (QueryException $e) {
            if ((int)($e->errorInfo[1] ?? 0) === 1451) {
                return back()->with('error', 'No se puede eliminar: tiene registros asociados (deudas, pagos, etc.).');
            }
            return back()->with('error', 'Error al eliminar la propiedad: ' . $e->getMessage());
        }
    }

    // 🆕 ACTUALIZADO: Solicitar corte por mora
    public function cutService(Property $property)
    {
        try {
            // 🆕 ASIGNAR TRABAJO PENDIENTE: CORTE POR MORA
            $property->asignarTrabajoPendiente(Property::TRABAJO_CORTE_MORA);

            return redirect()->route('admin.properties.index')
                ->with('success', 'Corte por mora solicitado. El equipo físico procederá con el corte.');
                
        } catch (\Exception $e) {
            \Log::error("Error en cutService: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al solicitar corte: ' . $e->getMessage());
        }
    }

    // 🆕 ACTUALIZADO: Cancelar trabajo pendiente
    public function cancelCutService(Property $property)
    {
        // Solo permitir si está en corte_pendiente
        if ($property->estado !== Property::ESTADO_CORTE_PENDIENTE) {
            return redirect()->back()
                ->with('error', 'Solo se puede cancelar trabajos pendientes');
        }

        try {
            // ✅ NUEVA LÓGICA: Diferente comportamiento según el tipo de trabajo
            $tipoTrabajo = $property->tipo_trabajo_pendiente;
            
            // 🚨 CASO CRÍTICO: Si es RECONEXIÓN y el cliente YA PAGÓ, NO permitir cancelación
            if ($tipoTrabajo === Property::TRABAJO_RECONEXION) {
                // Verificar si hay pagos recientes para reconexión
                $pagosRecientes = Pago::where('propiedad_id', $property->id)
                    ->where('created_at', '>=', now()->subDays(7)) // Últimos 7 días
                    ->exists();
                    
                if ($pagosRecientes) {
                    return redirect()->back()
                        ->with('error', 
                            'NO se puede cancelar la reconexión. ' .
                            'El cliente ya realizó el pago completo. ' .
                            'Contacte al operador para que ejecute la reconexión física.'
                        );
                }
                
                // Si no hay pagos recientes, permitir cancelación pero con estado CORTADO
                $property->update([
                    'estado' => Property::ESTADO_CORTADO,
                    'tipo_trabajo_pendiente' => null
                ]);
                
                \Log::info("✅ Reconexión cancelada (sin pago) - Propiedad {$property->id} vuelve a CORTADO");
                
            } else {
                // Para otros tipos de trabajo (corte_mora, conexion_nueva), lógica normal
                $nuevoEstado = $tipoTrabajo === Property::TRABAJO_CORTE_MORA ? 
                    Property::ESTADO_ACTIVO : Property::ESTADO_PENDIENTE_CONEXION;
                
                $property->update([
                    'estado' => $nuevoEstado,
                    'tipo_trabajo_pendiente' => null
                ]);
                
                \Log::info("✅ Trabajo {$tipoTrabajo} cancelado - Propiedad {$property->id} a estado: {$nuevoEstado}");
            }

            return redirect()->route('admin.properties.index')
                ->with('success', 'Trabajo pendiente cancelado correctamente.');
                
        } catch (\Exception $e) {
            \Log::error("Error en cancelCutService: " . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cancelar trabajo: ' . $e->getMessage());
        }
    }

    // 🆕 ACTUALIZADO: Restaurar servicio directamente (solo admin)
    public function restoreService(Property $property)
    {
        try {
            // 🆕 IR DIRECTAMENTE A ACTIVO Y LIMPIAR TRABAJO PENDIENTE
            $property->update([
                'estado' => Property::ESTADO_ACTIVO,
                'tipo_trabajo_pendiente' => null
            ]);
            
            return redirect()->back()
                ->with('info', 'Servicio restaurado para: ' . $property->referencia);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al restaurar servicio: ' . $e->getMessage());
        }
    }

    // 🆕 ACTUALIZADO: Solicitar reconexión (para secretaria)
    // En PropertyController.php - ACTUALIZAR el método requestReconnection:

// 🆕 ACTUALIZADO: Solicitar reconexión (para secretaria)
public function requestReconnection(Property $property)
{
    // Solo permitir si está cortado
    if ($property->estado !== Property::ESTADO_CORTADO) {
        return redirect()->back()
            ->with('error', 'Solo se puede solicitar reconexión para propiedades cortadas');
    }

    try {
        // ✅ Verificar que tenga meses adeudados
        $mesesAdeudados = $property->obtenerMesesAdeudados();
        $mesesMora = count($mesesAdeudados);
        
        if ($mesesMora === 0) {
            return redirect()->back()
                ->with('error', 'No se puede solicitar reconexión: la propiedad no tiene meses adeudados');
        }

        // ✅ Verificar si ya existe multa de reconexión pendiente
        if (Fine::existeMultaReconexionPendiente($property->id)) {
            $multaExistente = Fine::obtenerMultaReconexionPendiente($property->id);
            
            return redirect()->route('admin.pagos.create', [
                'propiedad_id' => $property->id,
                'reconexion' => true,
                'mes_desde' => !empty($mesesAdeudados) ? min($mesesAdeudados) : null,
                'mes_hasta' => !empty($mesesAdeudados) ? max($mesesAdeudados) : null,
                'multa_id' => $multaExistente->id,
                'forzar_pago_completo' => true // ✅ NUEVO PARÁMETRO
            ])->with('info', 
                "ℹ️ Ya existe una multa de reconexión pendiente (Bs. {$multaExistente->monto}). " .
                "Debe pagar los {$mesesMora} meses pendientes + la multa para proceder con la reconexión."
            );
        }

        // ✅ Calcular meses de mora y multa
        if ($mesesMora >= 12) {
            $tipoMulta = Fine::TIPO_RECONEXION_12MESES;
            $montoMulta = Fine::obtenerMontosBase()[$tipoMulta];
        } else {
            $tipoMulta = Fine::TIPO_RECONEXION_3MESES;
            $montoMulta = Fine::obtenerMontosBase()[$tipoMulta];
        }

        // ✅ Crear multa automáticamente
        $multa = Fine::create([
            'propiedad_id' => $property->id,
            'deuda_id' => null,
            'tipo' => $tipoMulta,
            'nombre' => 'Multa por Reconexión de Servicio',
            'monto' => $montoMulta,
            'descripcion' => 'Multa aplicada por solicitud de reconexión después de corte por mora. Meses en mora: ' . $mesesMora,
            'fecha_aplicacion' => now(),
            'creado_por' => auth()->id(),
            'estado' => Fine::ESTADO_PENDIENTE,
            'activa' => true,
            'aplicada_automaticamente' => true,
        ]);

        // ✅ Redirigir con TODOS los meses adeudados seleccionados automáticamente
        return redirect()->route('admin.pagos.create', [
            'propiedad_id' => $property->id,
            'reconexion' => true,
            'mes_desde' => !empty($mesesAdeudados) ? min($mesesAdeudados) : null,
            'mes_hasta' => !empty($mesesAdeudados) ? max($mesesAdeudados) : null,
            'multa_id' => $multa->id,
            'forzar_pago_completo' => true // ✅ NUEVO PARÁMETRO
        ])->with('success', 
            "✅ Multa de reconexión aplicada (Bs. {$montoMulta}). " .
            "Debe pagar los {$mesesMora} meses pendientes + la multa para proceder con la reconexión."
        );

    } catch (\Exception $e) {
        \Log::error("Error en requestReconnection: " . $e->getMessage());
        return redirect()->back()->with('error', 'Error al solicitar reconexión: ' . $e->getMessage());
    }
}

    public function search(Request $request)
    {
        $query = $request->get('q');
        
        $propiedades = Property::with(['client', 'tariff'])
        ->whereIn('estado', [
            Property::ESTADO_ACTIVO, 
            Property::ESTADO_CORTADO, // ✅ AGREGAR CORTADAS
            Property::ESTADO_CORTE_PENDIENTE // ✅ Y pendientes de corte
        ])
            ->where(function($q) use ($query) {
                $q->where('referencia', 'like', "%{$query}%")
                  ->orWhere('barrio', 'like', "%{$query}%")
                  ->orWhereHas('client', function($q) use ($query) {
                      $q->where('nombre', 'like', "%{$query}%")
                        ->orWhere('ci', 'like', "%{$query}%")
                        ->orWhere('codigo_cliente', 'like', "%{$query}%");
                  });
            })
            ->limit(10)
            ->get()
            ->map(function($propiedad) {
                return [
                    'id' => $propiedad->id,
                    'referencia' => $propiedad->referencia,
                    'barrio' => $propiedad->barrio,
                    'cliente_nombre' => $propiedad->client->nombre,
                    'cliente_ci' => $propiedad->client->ci,
                    'cliente_codigo' => $propiedad->client->codigo_cliente,
                    'tarifa_precio' => $propiedad->tariff->precio_mensual,
                    'tarifa_nombre' => $propiedad->tariff->nombre,
                    'estado' => $propiedad->estado,
                ];
            });
        
        return response()->json($propiedades);
    }
    private function obtenerTextoMesesMora($mesesMora)
    {
        if ($mesesMora >= 12) {
            return "más de 12 meses";
        } elseif ($mesesMora >= 6) {
            return "{$mesesMora} meses (mora grave)";
        } elseif ($mesesMora >= 3) {
            return "{$mesesMora} meses (mora significativa)";
        } else {
            return "{$mesesMora} meses";
        }
    }
}