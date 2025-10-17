<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Property;
use App\Models\Debt;
use App\Models\Pago;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReporteController extends Controller
{
    public function index()
    {
        $estadisticas = $this->obtenerEstadisticasReportes();
        return view('admin.reportes.index', compact('estadisticas'));
    }

    public function morosidad(Request $request)
    {
        // Filtros
        $filtroEstado = $request->get('estado', 'todos');
        $filtroMeses = $request->get('meses', 1);
        
        // ✅ CORREGIDO: Consulta optimizada
        $query = Property::with([
            'client', 
            'tariff', 
            'debts' => function($q) {
                $q->where('monto_pendiente', '>', 0)
                  ->whereIn('estado', ['pendiente', 'vencida', 'corte_pendiente', 'cortado'])
                  ->orderBy('fecha_emision', 'asc'); // Ordenar por fecha más antigua
            }
        ])
        ->whereHas('debts', function($q) {
            $q->where('monto_pendiente', '>', 0)
              ->whereIn('estado', ['pendiente', 'vencida', 'corte_pendiente', 'cortado']);
        });

        // Aplicar filtros
        if ($filtroEstado !== 'todos') {
            $query->where('estado', $filtroEstado);
        }

        $propiedades = $query->get()->map(function($propiedad) {
            $deudasPendientes = $propiedad->debts->where('monto_pendiente', '>', 0);
            
            return [
                'propiedad' => $propiedad,
                'cliente' => $propiedad->client,
                'deudas' => $deudasPendientes,
                'total_deuda' => $deudasPendientes->sum('monto_pendiente'),
                'meses_mora' => $this->calcularMesesMora($deudasPendientes),
                'estado_actual' => $propiedad->estado,
                'ultimo_mes_pagado' => $this->obtenerUltimoMesPagado($propiedad->id),
                'fecha_mas_antigua' => $deudasPendientes->isNotEmpty() ? 
                    $deudasPendientes->min('fecha_emision') : null,
                'cantidad_deudas' => $deudasPendientes->count(),
            ];
        })->filter(function($item) use ($filtroMeses) {
            // Filtrar por meses en mora
            return $item['meses_mora'] >= $filtroMeses;
        })->sortByDesc('meses_mora');

        $estadisticas = [
            'total_clientes_moros' => $propiedades->unique('cliente.id')->count(),
            'total_propiedades_moras' => $propiedades->count(),
            'deuda_total' => $propiedades->sum('total_deuda'),
            'promedio_meses_mora' => $propiedades->avg('meses_mora') ?: 0,
        ];

        return view('admin.reportes.morosidad', compact('propiedades', 'estadisticas', 'filtroEstado', 'filtroMeses'));
    }

    private function calcularMesesMora($deudas)
    {
        if ($deudas->isEmpty()) return 0;

        // ✅ CORREGIDO: Calcular correctamente los meses en mora
        $fechaMasAntigua = $deudas->min('fecha_emision');
        
        if (!$fechaMasAntigua) return 0;

        // Calcular diferencia en meses desde la deuda más antigua
        $mesesMora = now()->diffInMonths($fechaMasAntigua);
        
        // Asegurar que sea al menos 1 mes si hay deudas pendientes
        return max(1, $mesesMora);
    }

    private function obtenerUltimoMesPagado($propiedadId)
    {
        $ultimoPago = Pago::where('propiedad_id', $propiedadId)
            ->orderBy('mes_pagado', 'desc')
            ->first();

        return $ultimoPago ? $ultimoPago->mes_pagado : 'Sin pagos';
    }

    private function obtenerEstadisticasReportes()
    {
        return [
            'total_clientes' => Client::count(),
            'total_propiedades' => Property::count(),
            'propiedades_morosas' => Property::whereHas('debts', function($q) {
                $q->where('monto_pendiente', '>', 0)
                  ->whereIn('estado', ['pendiente', 'vencida', 'corte_pendiente', 'cortado']);
            })->count(),
            'deuda_total' => Debt::where('monto_pendiente', '>', 0)
                ->whereIn('estado', ['pendiente', 'vencida', 'corte_pendiente', 'cortado'])
                ->sum('monto_pendiente'),
            'ingresos_mes_actual' => Pago::whereYear('fecha_pago', now()->year)
                ->whereMonth('fecha_pago', now()->month)
                ->sum('monto'),
            'cortes_pendientes' => Property::where('estado', 'corte_pendiente')->count(),
            'propiedades_cortadas' => Property::where('estado', 'cortado')->count(),
        ];
    }

    // Métodos para PDF (próximamente)
    public function morosidadPdf(Request $request)
    {
        // Para implementar exportación a PDF
        return response()->json(['message' => 'PDF en desarrollo']);
    }

    public function ingresos(Request $request)
    {
        return view('admin.reportes.ingresos');
    }

    public function cortes(Request $request)
    {
        return view('admin.reportes.cortes');
    }

    public function propiedades(Request $request)
    {
        return view('admin.reportes.propiedades');
    }
}