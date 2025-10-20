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
     * Mostrar propiedades con corte pendiente
     */
    public function indexCortePendiente(Request $request)
    {
        $query = Property::where('estado', 'corte_pendiente')
            ->with(['client', 'debts' => function($q) {
                $q->where('estado', 'corte_pendiente'); // ✅ CORREGIDO: usar string directamente
            }]);

        // ✅ ACTUALIZADO: BÚSQUEDA INCLUYE CÓDIGO CLIENTE
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%")
                        ->orWhere('codigo_cliente', 'like', "%{$search}%"); // ✅ NUEVO
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

        $propiedades = $query->orderBy('created_at', 'desc')->paginate(20);

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
                $q->where('estado', 'cortado'); // ✅ CORREGIDO: usar string directamente
            }, 'multas' => function($q) {
                $q->where('estado', 'pendiente'); // ✅ CORREGIDO: usar string directamente
            }]);

        // ✅ ACTUALIZADO: BÚSQUEDA INCLUYE CÓDIGO CLIENTE
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%")
                        ->orWhere('codigo_cliente', 'like', "%{$search}%"); // ✅ NUEVO
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
     * Marcar propiedad como cortada físicamente
     */
    public function marcarComoCortado($propiedadId)
    {
        DB::transaction(function () use ($propiedadId) {
            $propiedad = Property::findOrFail($propiedadId);
            
            // Verificar que la propiedad esté en estado corte_pendiente
            if ($propiedad->estado !== 'corte_pendiente') {
                return redirect()->back()
                    ->with('error', 'Solo se pueden cortar propiedades con estado "Corte Pendiente"');
            }

            // ✅ CORREGIDO: Usar strings directamente en lugar de constantes
            $propiedad->debts()
                ->where('estado', 'corte_pendiente')
                ->update(['estado' => 'cortado']);
            
            // Actualizar estado de la propiedad
            $propiedad->update(['estado' => 'cortado']);

            // Aplicar multa de reconexión automáticamente
            $this->aplicarMultaReconexionAutomatica($propiedad);
        });

        return redirect()->route('admin.cortes.pendientes')
            ->with('success', 'Propiedad marcada como cortada físicamente y multa aplicada automáticamente');
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
        
        // ✅ CORREGIDO: Usar strings directamente en lugar de constantes
        $tipoMulta = $mesesMora >= 12 ? 
            'reconexion_12meses' : 
            'reconexion_3meses';

        // ✅ CORREGIDO: Usar array de tipos de multa del modelo Fine
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
            'estado' => 'pendiente', // ✅ CORREGIDO: usar string directamente
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
        
        // ✅ CORREGIDO: Usar strings directamente
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
                'estado' => 'pendiente', // ✅ CORREGIDO: usar string directamente
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
                ->where('estado', 'cortado') // ✅ CORREGIDO: usar string directamente
                ->count();

            if ($deudasPendientes > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede restaurar el servicio mientras existan deudas cortadas pendientes');
            }

            // Verificar que no tenga multas pendientes
            $multasPendientes = $propiedad->multas()
                ->where('estado', 'pendiente') // ✅ CORREGIDO: usar string directamente
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
        $cortesPendientes = Property::where('estado', 'corte_pendiente')->count();
        $cortesRealizados = Property::where('estado', 'cortado')->count();
        $multasPendientes = Fine::where('estado', 'pendiente')->count(); // ✅ CORREGIDO: usar string directamente

        return response()->json([
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
        $propiedades = Property::where('estado', 'corte_pendiente')
            ->with(['client', 'debts' => function($q) {
                $q->where('estado', 'corte_pendiente'); // ✅ CORREGIDO: usar string directamente
            }])
            ->orderBy('barrio')
            ->orderBy('referencia')
            ->get();

        // Aquí puedes implementar la generación de PDF o Excel
        // Por ahora retornamos una vista simple

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
                            ->orWhere('codigo_cliente', 'like', "%{$search}%"); // ✅ NUEVO
                      });
            })
            ->whereIn('estado', ['corte_pendiente', 'cortado'])
            ->with(['client', 'debts' => function($q) {
                $q->whereIn('estado', ['corte_pendiente', 'cortado']); // ✅ CORREGIDO: usar strings directamente
            }])
            ->limit(10)
            ->get();

        return response()->json($propiedades);
    }
}