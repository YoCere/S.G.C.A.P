<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\Property;
use App\Models\Debt;
use App\Models\Pago;
use App\Models\Fine;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.home')->only('index');
    }

    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('Operador')) {
            return $this->dashboardOperador();
        }
        
        return $this->dashboardAdministrativo();
    }

    private function dashboardOperador()
    {
        $trabajosPendientes = Property::with('client')
            ->whereNotNull('tipo_trabajo_pendiente')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($property) {
                return [
                    'codigo' => $property->client->codigo_cliente ?? 'N/A',
                    'cliente' => $property->client->nombre ?? 'Cliente no asignado',
                    'direccion' => $property->referencia,
                    'barrio' => $property->barrio,
                    'tipo_trabajo' => $property->texto_trabajo_pendiente,
                    'color' => $property->color_trabajo,
                    'icono' => $property->icono_trabajo,
                    'fecha_solicitud' => $property->created_at->format('d/m/Y')
                ];
            });

        $propiedadesCortadas = Property::where('estado', Property::ESTADO_CORTADO)->count();

        return view('admin.home.operador', compact('trabajosPendientes', 'propiedadesCortadas'));
    }

    private function dashboardAdministrativo()
    {
        // Métricas principales
        $metrics = [
            'total_clientes_activos' => Client::activos()->count(),
            'recaudacion_mes_actual' => Pago::where('mes_pagado', Carbon::now()->format('Y-m'))->sum('monto'),
            'deuda_total_pendiente' => Debt::pendientes()->sum('monto_pendiente'),
            'trabajos_pendientes' => Property::whereNotNull('tipo_trabajo_pendiente')->count(),
            'propiedades_activas' => Property::where('estado', Property::ESTADO_ACTIVO)->count(),
            'propiedades_cortadas' => Property::where('estado', Property::ESTADO_CORTADO)->count(),
        ];

        // Datos para gráficos
        $chartData = [
            'recaudacion_ultimos_meses' => $this->getRecaudacionUltimosMeses(),
            'estados_propiedades' => $this->getEstadosPropiedades(),
        ];

        // Alertas
        $alerts = [
            'top_deudores' => $this->getTopDeudores(),
            'trabajos_pendientes_criticos' => $this->getTrabajosPendientesCriticos(),
        ];

        return view('admin.home.administrativo', compact('metrics', 'chartData', 'alerts'));
    }

    // ... (mantener los mismos métodos auxiliares que ya teníamos)
    private function getRecaudacionUltimosMeses()
    {
        $meses = [];
        $recaudacion = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $mes = Carbon::now()->subMonths($i);
            $mesFormateado = $mes->format('Y-m');
            $mesNombre = $mes->locale('es')->translatedFormat('M Y');
            
            $totalMes = Pago::where('mes_pagado', $mesFormateado)->sum('monto');
            
            $meses[] = $mesNombre;
            $recaudacion[] = floatval($totalMes);
        }

        return [
            'meses' => $meses,
            'recaudacion' => $recaudacion
        ];
    }

    private function getEstadosPropiedades()
    {
        $estados = Property::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        $labels = [];
        $data = [];
        $colors = [
            Property::ESTADO_ACTIVO => '#28a745',
            Property::ESTADO_CORTADO => '#dc3545',
            Property::ESTADO_CORTE_PENDIENTE => '#ffc107',
            Property::ESTADO_PENDIENTE_CONEXION => '#17a2b8',
            Property::ESTADO_INACTIVO => '#6c757d'
        ];

        foreach ($estados as $estado) {
            $labels[] = $this->getEstadoLegible($estado->estado);
            $data[] = $estado->total;
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => array_values($colors)
        ];
    }

    private function getTopDeudores($limit = 5)
    {
        return Property::with(['client', 'debts' => function($query) {
                $query->pendientes();
            }])
            ->get()
            ->map(function($property) {
                return [
                    'codigo_cliente' => $property->client->codigo_cliente ?? 'N/A',
                    'cliente' => $property->client->nombre ?? 'Cliente no asignado',
                    'propiedad' => $property->referencia,
                    'deuda_total' => $property->total_deudas_pendientes,
                    'meses_mora' => count($property->obtenerMesesAdeudados())
                ];
            })
            ->filter(function($item) {
                return $item['deuda_total'] > 0;
            })
            ->sortByDesc('deuda_total')
            ->take($limit)
            ->values();
    }

    private function getTrabajosPendientesCriticos($limit = 5)
    {
        return Property::with('client')
            ->whereNotNull('tipo_trabajo_pendiente')
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get()
            ->map(function($property) {
                return [
                    'codigo' => $property->client->codigo_cliente ?? 'N/A',
                    'cliente' => $property->client->nombre ?? 'Cliente no asignado',
                    'direccion' => $property->referencia,
                    'barrio' => $property->barrio,
                    'tipo_trabajo' => $property->texto_trabajo_pendiente,
                    'color' => $property->color_trabajo,
                    'icono' => $property->icono_trabajo,
                    'fecha_solicitud' => $property->created_at->format('d/m/Y')
                ];
            });
    }

    private function getEstadoLegible($estado)
    {
        return match($estado) {
            Property::ESTADO_ACTIVO => 'Activo',
            Property::ESTADO_CORTADO => 'Cortado',
            Property::ESTADO_CORTE_PENDIENTE => 'Corte Pendiente',
            Property::ESTADO_PENDIENTE_CONEXION => 'Pendiente Conexión',
            Property::ESTADO_INACTIVO => 'Inactivo',
            default => $estado
        };
    }
}