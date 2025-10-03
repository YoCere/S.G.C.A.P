<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Debt;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CorteController extends Controller
{
    /**
     * Mostrar propiedades con corte pendiente
     */
    public function indexCortePendiente(Request $request)
    {
        $query = Property::where('estado', 'corte_pendiente')
            ->with(['client', 'debts' => function($q) {
                $q->where('estado', 'corte_pendiente');
            }]);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%");
                  });
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
                $q->where('estado', 'cortado');
            }, 'multas' => function($q) {
                $q->where('estado', Fine::ESTADO_PENDIENTE);
            }]);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('referencia', 'like', "%{$search}%")
                  ->orWhereHas('client', function($q) use ($search) {
                      $q->where('nombre', 'like', "%{$search}%")
                        ->orWhere('ci', 'like', "%{$search}%");
                  });
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

            // Cambiar estado de las deudas de "corte_pendiente" a "cortado"
            $propiedad->debts()
                ->where('estado', Debt::ESTADO_CORTE_PENDIENTE)
                ->update(['estado' => Debt::ESTADO_CORTADO]);
            
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
            ->where('estado', Debt::ESTADO_CORTADO)
            ->orderBy('fecha_emision', 'asc')
            ->first();

        if (!$deudaMasAntigua) {
            return;
        }

        // Calcular meses de mora
        $mesesMora = now()->diffInMonths($deudaMasAntigua->fecha_vencimiento);
        $tipoMulta = $mesesMora >= 12 ? 
            Fine::TIPO_RECONEXION_12MESES : 
            Fine::TIPO_RECONEXION_3MESES;

        // Crear multa automática
        Fine::create([
            'propiedad_id' => $propiedad->id,
            'deuda_id' => $deudaMasAntigua->id,
            'tipo' => $tipoMulta,
            'nombre' => Fine::obtenerTiposMulta()[$tipoMulta],
            'monto' => Fine::obtenerMontosBase()[$tipoMulta],
            'descripcion' => 'Multa por reconexión de servicio - ' . $mesesMora . ' meses de mora',
            'fecha_aplicacion' => now(),
            'estado' => Fine::ESTADO_PENDIENTE,
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
        if ($deuda->estado !== Debt::ESTADO_CORTADO) {
            return response()->json([
                'success' => false,
                'message' => 'Solo se puede aplicar multa a deudas cortadas'
            ], 422);
        }

        // Determinar tipo de multa basado en meses de mora
        $mesesMora = now()->diffInMonths($deuda->fecha_vencimiento);
        $tipoMulta = $mesesMora >= 12 ? 
            Fine::TIPO_RECONEXION_12MESES : 
            Fine::TIPO_RECONEXION_3MESES;

        DB::transaction(function () use ($deuda, $tipoMulta, $mesesMora) {
            // Crear multa
            Fine::create([
                'deuda_id' => $deuda->id,
                'propiedad_id' => $deuda->propiedad_id,
                'tipo' => $tipoMulta,
                'nombre' => Fine::obtenerTiposMulta()[$tipoMulta],
                'monto' => Fine::obtenerMontosBase()[$tipoMulta],
                'descripcion' => 'Multa aplicada manualmente - ' . $mesesMora . ' meses de mora',
                'fecha_aplicacion' => now(),
                'estado' => Fine::ESTADO_PENDIENTE,
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
                ->where('estado', Debt::ESTADO_CORTADO)
                ->count();

            if ($deudasPendientes > 0) {
                return redirect()->back()
                    ->with('error', 'No se puede restaurar el servicio mientras existan deudas cortadas pendientes');
            }

            // Verificar que no tenga multas pendientes
            $multasPendientes = $propiedad->multas()
                ->where('estado', Fine::ESTADO_PENDIENTE)
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
        $multasPendientes = Fine::where('estado', Fine::ESTADO_PENDIENTE)->count();

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
                $q->where('estado', 'corte_pendiente');
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
                            ->orWhere('ci', 'like', "%{$search}%");
                      });
            })
            ->whereIn('estado', ['corte_pendiente', 'cortado'])
            ->with(['client', 'debts' => function($q) {
                $q->whereIn('estado', [Debt::ESTADO_CORTE_PENDIENTE, Debt::ESTADO_CORTADO]);
            }])
            ->limit(10)
            ->get();

        return response()->json($propiedades);
    }
}