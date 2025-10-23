<?php

namespace App\Http\Controllers\Admin;

use App\Models\Property;
use App\Models\Debt;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Routing\Controller;

class CorteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.cortes.pendientes')->only('indexCortePendiente');
        $this->middleware('can:admin.cortes.cortadas')->only('indexCortadas');
        $this->middleware('can:admin.cortes.marcar-cortado')->only('marcarComoCortado');
        $this->middleware('can:admin.cortes.aplicar-multa')->only('aplicarMultaReconexion');
    }

    /**
     * 🆕 ACTUALIZADO: Mostrar TODOS los trabajos pendientes (conexiones, cortes y reconexiones)
     */
    public function indexCortePendiente(Request $request)
    {
        // 🆕 INCLUIR ambos estados: pendiente_conexion Y corte_pendiente
        $query = Property::whereIn('estado', ['pendiente_conexion', 'corte_pendiente'])
            ->with(['client', 'debts' => function($q) {
                $q->whereIn('estado', ['pendiente', 'corte_pendiente']);
            }]);

        // ✅ ACTUALIZADO: BÚSQUEDA INCLUYE CÓDIGO CLIENTE
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%")
                        ->orWhere('codigo_cliente', 'like', "%{$search}%");
                  });
            });
        }

        // ✅ NUEVO: FILTRO POR CÓDIGO CLIENTE
        if ($request->filled('codigo_cliente')) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('codigo_cliente', 'like', "%{$request->codigo_cliente}%");
            });
        }

        if ($request->filled('barrio')) {
            $query->where('barrio', $request->barrio);
        }

        // 🆕 FILTRO POR TIPO DE TRABAJO
        if ($request->filled('tipo_trabajo')) {
            if ($request->tipo_trabajo === 'conexion') {
                $query->where('estado', 'pendiente_conexion');
            } elseif ($request->tipo_trabajo === 'corte') {
                $query->where('estado', 'corte_pendiente');
            }
        }

        $propiedades = $query->orderByRaw("
            CASE 
                WHEN estado = 'pendiente_conexion' THEN 1
                WHEN estado = 'corte_pendiente' THEN 2
                ELSE 3
            END
        ")->orderBy('created_at', 'desc')->paginate(20);

        $barrios = Property::distinct()->pluck('barrio')->filter();

        return view('admin.cortes.pendientes', compact('propiedades', 'barrios'));
    }

    /**
     * Mostrar propiedades cortadas
     */
    public function indexCortadas(Request $request)
    {
        $query = Property::where('estado', 'cortado')
            ->with(['client', 'debts' => function($q) {
                $q->where('estado', 'cortado');
            }, 'multas' => function($q) {
                $q->where('estado', 'pendiente');
            }]);

        // ✅ ACTUALIZADO: BÚSQUEDA INCLUYE CÓDIGO CLIENTE
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%")
                        ->orWhere('codigo_cliente', 'like', "%{$search}%");
                  });
            });
        }

        // ✅ NUEVO: FILTRO POR CÓDIGO CLIENTE
        if ($request->filled('codigo_cliente')) {
            $query->whereHas('client', function($q) use ($request) {
                $q->where('codigo_cliente', 'like', "%{$request->codigo_cliente}%");
            });
        }

        if ($request->filled('barrio')) {
            $query->where('barrio', $request->barrio);
        }

        $propiedades = $query->orderBy('updated_at', 'desc')->paginate(20);

        $barrios = Property::distinct()->pluck('barrio')->filter();

        return view('admin.cortes.cortadas', compact('propiedades', 'barrios'));
    }

    /**
     * 🆕 ACTUALIZADO: Marcar propiedad como trabajada (instalación completada o corte ejecutado)
     */
    public function marcarComoCortado($propiedadId)
    {
        DB::transaction(function () use ($propiedadId) {
            $propiedad = Property::findOrFail($propiedadId);
            
            // 🆕 VERIFICAR QUE LA PROPIEDAD ESTÉ EN ESTADO PENDIENTE (conexión o corte)
            if (!in_array($propiedad->estado, ['pendiente_conexion', 'corte_pendiente'])) {
                return redirect()->back()
                    ->with('error', 'Solo se pueden procesar propiedades con trabajos pendientes');
            }

            // 🆕 LÓGICA DIFERENTE SEGÚN EL ESTADO ACTUAL
            if ($propiedad->estado === 'pendiente_conexion') {
                // 🆕 CAMBIO CRÍTICO: INSTALACIÓN NUEVA - Ir directamente a 'activo' (servicio funcionando)
                $propiedad->update(['estado' => 'activo']);
                
                return redirect()->route('admin.cortes.pendientes')
                    ->with('success', '✅ Instalación completada y servicio activado correctamente');

            } elseif ($propiedad->estado === 'corte_pendiente') {
                // CORTE O RECONEXIÓN: Lógica original con multas
                
                // Actualizar deudas relacionadas
                $propiedad->debts()
                    ->where('estado', 'corte_pendiente')
                    ->update(['estado' => 'cortado']);
                
                // Actualizar estado de la propiedad
                $propiedad->update(['estado' => 'cortado']);

                // Aplicar multa de reconexión automáticamente (solo para cortes por mora)
                $this->aplicarMultaReconexionAutomatica($propiedad);

                return redirect()->route('admin.cortes.pendientes')
                    ->with('success', 'Corte físico ejecutado y multa aplicada automáticamente');
            }
        });

        return redirect()->route('admin.cortes.pendientes')
            ->with('error', 'Acción no válida');
    }

    /**
     * Aplicar multa de reconexión automáticamente al cortar una propiedad
     */
    private function aplicarMultaReconexionAutomatica(Property $propiedad)
    {
        // Obtener la deuda más antigua en corte_pendiente para calcular meses de mora
        $deudaMasAntigua = $propiedad->debts()
            ->where('estado', 'cortado')
            ->orderBy('fecha_emision', 'asc')
            ->first();

        if (!$deudaMasAntigua) {
            return;
        }

        // Calcular meses de mora
        $mesesMora = now()->diffInMonths($deudaMasAntigua->fecha_vencimiento);
        
        $tipoMulta = $mesesMora >= 12 ? 
            'reconexion_12meses' : 
            'reconexion_3meses';

        $tiposMulta = [
            'reconexion_3meses' => 'Reconexión (3+ meses mora)',
            'reconexion_12meses' => 'Reconexión (12+ meses mora)',
            'conexion_clandestina' => 'Conexión Clandestina',
            'manipulacion_llaves' => 'Manipulación de Llaves',
            'construccion' => 'Construcción'
        ];

        $montosBase = [
            'reconexion_3meses' => 100,
            'reconexion_12meses' => 300,
            'conexion_clandestina' => 500,
            'manipulacion_llaves' => 500,
            'construccion' => 200
        ];

        // Crear multa automática
        Fine::create([
            'propiedad_id' => $propiedad->id,
            'deuda_id' => $deudaMasAntigua->id,
            'tipo' => $tipoMulta,
            'nombre' => $tiposMulta[$tipoMulta] ?? 'Multa de Reconexión',
            'monto' => $montosBase[$tipoMulta] ?? 100,
            'descripcion' => 'Multa por reconexión de servicio - ' . $mesesMora . ' meses de mora',
            'fecha_aplicacion' => now(),
            'estado' => 'pendiente',
            'aplicada_automaticamente' => true,
            'activa' => true,
            'creado_por' => auth()->id(),
        ]);
    }

    /**
     * Aplicar multa de reconexión manualmente (para casos especiales)
     */
    public function aplicarMultaReconexion(Request $request, $deudaId)
    {
        $deuda = Debt::findOrFail($deudaId);
        
        // Verificar que la deuda esté en estado cortado
        if ($deuda->estado !== 'cortado') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede aplicar multa a deudas cortadas'
            ], 422);
        }

        // Determinar tipo de multa basado en meses de mora
        $mesesMora = now()->diffInMonths($deuda->fecha_vencimiento);
        
        $tipoMulta = $mesesMora >= 12 ? 
            'reconexion_12meses' : 
            'reconexion_3meses';

        $tiposMulta = [
            'reconexion_3meses' => 'Reconexión (3+ meses mora)',
            'reconexion_12meses' => 'Reconexión (12+ meses mora)'
        ];

        $montosBase = [
            'reconexion_3meses' => 100,
            'reconexion_12meses' => 300
        ];

        DB::transaction(function () use ($deuda, $tipoMulta, $mesesMora, $tiposMulta, $montosBase) {
            // Crear multa
            Fine::create([
                'deuda_id' => $deuda->id,
                'propiedad_id' => $deuda->propiedad_id,
                'tipo' => $tipoMulta,
                'nombre' => $tiposMulta[$tipoMulta],
                'monto' => $montosBase[$tipoMulta],
                'descripcion' => 'Multa aplicada manualmente - ' . $mesesMora . ' meses de mora',
                'fecha_aplicacion' => now(),
                'estado' => 'pendiente',
                'aplicada_automaticamente' => false,
                'activa' => true,
                'creado_por' => auth()->id(),
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => 'Multa de reconexión aplicada correctamente'
        ]);
    }

    /**
     * Restaurar servicio de propiedad cortada (cuando pagan)
     */
    public function restaurarServicio($propiedadId)
    {
        DB::transaction(function () use ($propiedadId) {
            $propiedad = Property::findOrFail($propiedadId);
            
            // Verificar que la propiedad esté cortada
            if ($propiedad->estado !== 'cortado') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden restaurar propiedades cortadas');
            }

            // Verificar que no tenga deudas pendientes
            $deudasPendientes = $propiedad->debts()
                ->where('estado', 'cortado')
                ->count();

            if ($deudasPendientes > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede restaurar el servicio mientras existan deudas cortadas pendientes');
            }

            // Verificar que no tenga multas pendientes
            $multasPendientes = $propiedad->multas()
                ->where('estado', 'pendiente')
                ->count();

            if ($multasPendientes > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede restaurar el servicio mientras existan multas pendientes');
            }

            // Restaurar propiedad a estado activo
            $propiedad->update(['estado' => 'activo']);
        });

        return redirect()->route('admin.cortes.cortadas')
            ->with('success', 'Servicio restaurado correctamente');
    }

    /**
     * Obtener estadísticas de cortes para dashboard
     */
    public function obtenerEstadisticas()
    {
        $conexionesPendientes = Property::where('estado', 'pendiente_conexion')->count();
        $cortesPendientes = Property::where('estado', 'corte_pendiente')->count();
        $cortesRealizados = Property::where('estado', 'cortado')->count();
        $multasPendientes = Fine::where('estado', 'pendiente')->count();

        return response()->json([
            'conexiones_pendientes' => $conexionesPendientes,
            'cortes_pendientes' => $cortesPendientes,
            'cortes_realizados' => $cortesRealizados,
            'multas_pendientes' => $multasPendientes,
        ]);
    }

    /**
     * Generar reporte de cortes pendientes
     */
    public function generarReporteCortesPendientes()
    {
        $propiedades = Property::whereIn('estado', ['pendiente_conexion', 'corte_pendiente'])
            ->with(['client', 'debts' => function($q) {
                $q->whereIn('estado', ['pendiente', 'corte_pendiente']);
            }])
            ->orderByRaw("
                CASE 
                    WHEN estado = 'pendiente_conexion' THEN 1
                    WHEN estado = 'corte_pendiente' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('barrio')
            ->orderBy('referencia')
            ->get();

        return view('admin.cortes.reportes.pendientes', compact('propiedades'));
    }

    /**
     * Buscar propiedades para cortes
     */
    public function buscarPropiedades(Request $request)
    {
        $search = $request->get('search');

        $propiedades = Property::where(function($query) use ($search) {
                $query->where('referencia', 'like', "%{$search}%")
                      ->orWhereHas('client', function($q) use ($search) {
                          $q->where('nombre', 'like', "%{$search}%")
                            ->orWhere('ci', 'like', "%{$search}%")
                            ->orWhere('codigo_cliente', 'like', "%{$search}%");
                      });
            })
            ->whereIn('estado', ['pendiente_conexion', 'corte_pendiente', 'cortado'])
            ->with(['client', 'debts' => function($q) {
                $q->whereIn('estado', ['pendiente', 'corte_pendiente', 'cortado']);
            }])
            ->limit(10)
            ->get();

        return response()->json($propiedades);
    }
}