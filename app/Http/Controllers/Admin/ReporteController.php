<?php

namespace App\Http\Controllers\Admin;

use App\Models\Client;
use App\Models\Property;
use App\Models\Debt;
use App\Models\Pago;
use App\Models\Tariff;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin.reportes.index')->only('index');
        $this->middleware('can:admin.reportes.morosidad')->only('morosidad');
        $this->middleware('can:admin.reportes.propiedades')->only('propiedades');
    }

    /**
     * Página principal de reportes - Menú
     */
    public function index()
    {
        return view('admin.reportes.index');
    }

    /**
     * Lista simple de deudores - Compacta para impresión CON GRÁFICOS
     */
    public function morosidad(Request $request)
    {
        $filtroBarrio = $request->get('barrio');
        $filtroMeses = $request->get('meses_mora', 3);
        $filtroAnio = $request->get('anio', date('Y'));
        $filtroTarifa = $request->get('tarifa');

        $query = Property::with(['client', 'tariff', 'debts' => function($query) {
            $query->pendientes();
        }])
        ->whereHas('debts', function($query) {
            $query->pendientes();
        });

        // Filtros
        if ($filtroBarrio) {
            $query->where('barrio', 'like', "%{$filtroBarrio}%");
        }

        if ($filtroAnio && $filtroAnio != 'todos') {
            $query->whereHas('debts', function($q) use ($filtroAnio) {
                $q->whereYear('fecha_emision', $filtroAnio);
            });
        }

        if ($filtroTarifa && $filtroTarifa != 'todos') {
            $query->whereHas('tariff', function($q) use ($filtroTarifa) {
                $q->where('nombre', 'like', "%{$filtroTarifa}%");
            });
        }

        $propiedades = $query->get()
            ->map(function($property) {
                $mesesAdeudados = $property->obtenerMesesAdeudados();
                $mesesMora = count($mesesAdeudados);
                $deudaTotal = $property->total_deudas_pendientes;
                
                // Obtener nombre de la tarifa (tipo de cliente)
                $tipoCliente = $property->tariff ? $property->tariff->nombre : 'Sin tarifa';
                
                // Obtener año de la deuda más antigua
                $deudaAntigua = $property->debts->sortBy('fecha_emision')->first();
                $anioDeuda = $deudaAntigua ? $deudaAntigua->fecha_emision->format('Y') : date('Y');

                return [
                    'codigo_cliente' => $property->client->codigo_cliente ?? 'N/A',
                    'cliente' => $property->client->nombre ?? 'Cliente no asignado',
                    'propiedad' => $property->referencia,
                    'barrio' => $property->barrio,
                    'deuda_total' => $deudaTotal,
                    'meses_mora' => $mesesMora,
                    'estado_servicio' => $property->estado,
                    'ultimo_mes_pagado' => $this->obtenerUltimoMesPagado($property->id),
                    'tipo_cliente' => $tipoCliente,
                    'tarifa' => $tipoCliente,
                    'anio_deuda' => $anioDeuda,
                    'cliente_id' => $property->client->id ?? null,
                    'primera_deuda' => $deudaAntigua ? $deudaAntigua->fecha_emision->format('d/m/Y') : null
                ];
            })
            ->filter(function($item) use ($filtroMeses) {
                return $item['meses_mora'] >= $filtroMeses;
            })
            ->sortByDesc('deuda_total')
            ->values();

        // ESTADÍSTICAS PARA GRÁFICOS
        $estadisticas = $this->generarEstadisticasMorosidad($propiedades);

        // Obtener años disponibles para filtro
        $aniosDisponibles = Debt::select(DB::raw('YEAR(fecha_emision) as year'))
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->toArray();

        if (empty($aniosDisponibles)) {
            $aniosDisponibles = [date('Y')];
        }

        array_unshift($aniosDisponibles, 'todos');

        $barrios = Property::select('barrio')->distinct()->pluck('barrio');
        
        // Obtener tarifas existentes para filtro
        $tarifas = Tariff::where('activo', true)
            ->select('nombre')
            ->distinct()
            ->pluck('nombre')
            ->toArray();
        
        array_unshift($tarifas, 'todos');

        return view('admin.reportes.morosidad', compact(
            'propiedades', 
            'barrios', 
            'filtroBarrio', 
            'filtroMeses',
            'filtroAnio',
            'filtroTarifa',
            'estadisticas',
            'aniosDisponibles',
            'tarifas'
        ));
    }

    /**
     * Generar estadísticas para gráficos de morosidad
     */
    private function generarEstadisticasMorosidad($propiedades)
    {
        $totalDeudores = $propiedades->count();
        $deudaTotal = $propiedades->sum('deuda_total');
        $totalClientes = Client::count();
        
        // Estadísticas por tipo de cliente (tarifa)
        $porTipoCliente = $propiedades->groupBy('tipo_cliente')->map(function($items, $tipo) use ($totalDeudores) {
            return [
                'tipo' => $tipo,
                'cantidad' => $items->count(),
                'porcentaje' => $totalDeudores > 0 ? round(($items->count() / $totalDeudores) * 100, 2) : 0,
                'deuda_total' => $items->sum('deuda_total'),
                'promedio_deuda' => $items->count() > 0 ? round($items->sum('deuda_total') / $items->count(), 2) : 0
            ];
        });

        // Distribución por meses de mora
        $porMesesMora = [
            '1-3' => $propiedades->whereBetween('meses_mora', [1, 3])->count(),
            '4-6' => $propiedades->whereBetween('meses_mora', [4, 6])->count(),
            '7-12' => $propiedades->whereBetween('meses_mora', [7, 12])->count(),
            '13+' => $propiedades->where('meses_mora', '>', 12)->count()
        ];

        // Top 10 deudores
        $topDeudores = $propiedades->sortByDesc('deuda_total')->take(10)->values();

        // Distribución por año de deuda
        $porAnioDeuda = $propiedades->groupBy('anio_deuda')->map(function($items, $anio) {
            return [
                'anio' => $anio,
                'cantidad' => $items->count(),
                'deuda_total' => $items->sum('deuda_total')
            ];
        });

        // Obtener total de clientes por tarifa
        $estadisticasTarifas = [];
        $tarifasConClientes = Tariff::where('activo', true)->get();
        
        foreach ($tarifasConClientes as $tarifa) {
            $totalClientesTarifa = Property::whereHas('tariff', function($q) use ($tarifa) {
                $q->where('nombre', $tarifa->nombre);
            })->count();
            
            $totalDeudoresTarifa = $propiedades->where('tipo_cliente', $tarifa->nombre)->count();
            
            $estadisticasTarifas[$tarifa->nombre] = [
                'total_clientes' => $totalClientesTarifa,
                'total_deudores' => $totalDeudoresTarifa,
                'porcentaje_morosidad' => $totalClientesTarifa > 0 ? 
                    round(($totalDeudoresTarifa / $totalClientesTarifa) * 100, 2) : 0
            ];
        }

        // Agregar "Sin tarifa"
        $totalSinTarifa = Property::whereNull('tarifa_id')->count();
        $deudoresSinTarifa = $propiedades->where('tipo_cliente', 'Sin tarifa')->count();
        
        if ($totalSinTarifa > 0 || $deudoresSinTarifa > 0) {
            $estadisticasTarifas['Sin tarifa'] = [
                'total_clientes' => $totalSinTarifa,
                'total_deudores' => $deudoresSinTarifa,
                'porcentaje_morosidad' => $totalSinTarifa > 0 ? 
                    round(($deudoresSinTarifa / $totalSinTarifa) * 100, 2) : 0
            ];
        }

        return [
            'total_deudores' => $totalDeudores,
            'deuda_total' => $deudaTotal,
            'promedio_deuda' => $totalDeudores > 0 ? round($deudaTotal / $totalDeudores, 2) : 0,
            'por_tipo_cliente' => $porTipoCliente,
            'por_meses_mora' => $porMesesMora,
            'por_anio_deuda' => $porAnioDeuda,
            'top_deudores' => $topDeudores,
            'total_clientes' => $totalClientes,
            'porcentaje_morosidad' => $totalClientes > 0 ? round(($totalDeudores / $totalClientes) * 100, 2) : 0,
            'estadisticas_tarifas' => $estadisticasTarifas
        ];
    }

    /**
     * Lista simple de clientes - Compacta para impresión
     */
    public function clientes(Request $request)
    {
        $filtroBarrio = $request->get('barrio');
        $filtroEstado = $request->get('estado');

        $query = Client::with(['properties', 'properties.tariff']);

        if ($filtroEstado) {
            $query->where('estado_cuenta', $filtroEstado);
        }

        $clientes = $query->get()
            ->map(function($client) use ($filtroBarrio) {
                $propiedades = $client->properties;
                
                if ($filtroBarrio) {
                    $propiedades = $propiedades->where('barrio', 'like', "%{$filtroBarrio}%");
                }

                // Obtener tarifa principal (la más común)
                $tarifasCount = [];
                foreach ($propiedades as $propiedad) {
                    $tarifa = $propiedad->tariff ? $propiedad->tariff->nombre : 'Sin tarifa';
                    $tarifasCount[$tarifa] = isset($tarifasCount[$tarifa]) ? $tarifasCount[$tarifa] + 1 : 1;
                }
                
                arsort($tarifasCount);
                $tarifaPrincipal = !empty($tarifasCount) ? array_key_first($tarifasCount) : 'Sin tarifa';

                return [
                    'codigo' => $client->codigo_cliente,
                    'nombre' => $client->nombre,
                    'ci' => $client->ci,
                    'telefono' => $client->telefono,
                    'barrio_principal' => $propiedades->first()->barrio ?? 'N/A',
                    'estado_cliente' => $client->estado_cuenta,
                    'total_propiedades' => $propiedades->count(),
                    'fecha_registro' => $client->fecha_registro_formateada,
                    'tarifa' => $tarifaPrincipal
                ];
            })
            ->sortBy('nombre')
            ->values();

        // Obtener estadísticas simples
        $estadisticas = [
            'total_clientes' => $clientes->count(),
            'clientes_activos' => $clientes->where('estado_cliente', 'activo')->count(),
            'clientes_inactivos' => $clientes->where('estado_cliente', 'inactivo')->count(),
            'total_propiedades' => $clientes->sum('total_propiedades'),
            'por_estado' => [
                'activo' => [
                    'cantidad' => $clientes->where('estado_cliente', 'activo')->count(),
                    'porcentaje' => $clientes->count() > 0 ? round(($clientes->where('estado_cliente', 'activo')->count() / $clientes->count()) * 100, 2) : 0
                ],
                'inactivo' => [
                    'cantidad' => $clientes->where('estado_cliente', 'inactivo')->count(),
                    'porcentaje' => $clientes->count() > 0 ? round(($clientes->where('estado_cliente', 'inactivo')->count() / $clientes->count()) * 100, 2) : 0
                ]
            ]
        ];

        $barrios = Property::select('barrio')->distinct()->pluck('barrio');

        return view('admin.reportes.clientes', compact('clientes', 'barrios', 'filtroBarrio', 'filtroEstado', 'estadisticas'));
    }

    /**
     * Lista simple de propiedades - Compacta para impresión
     */
    /**
 * Lista simple de propiedades - Compacta para impresión CON GRÁFICOS
 */
public function propiedades(Request $request)
{
    $filtroBarrio = $request->get('barrio');
    $filtroEstado = $request->get('estado');

    $query = Property::with(['client', 'tariff']);

    if ($filtroBarrio) {
        $query->where('barrio', 'like', "%{$filtroBarrio}%");
    }

    if ($filtroEstado) {
        $query->where('estado', $filtroEstado);
    }

    $propiedades = $query->get()
        ->map(function($property) {
            return [
                'codigo_propiedad' => $property->id,
                'direccion' => $property->referencia,
                'barrio' => $property->barrio,
                'cliente' => $property->client->nombre ?? 'Cliente no asignado',
                'codigo_cliente' => $property->client->codigo_cliente ?? 'N/A',
                'estado_servicio' => $property->estado,
                'trabajo_pendiente' => $property->texto_trabajo_pendiente,
                'tiene_deuda' => $property->total_deudas_pendientes > 0 ? 'Sí' : 'No',
                'tarifa' => $property->tariff ? $property->tariff->nombre : 'Sin tarifa'
            ];
        })
        ->sortBy('barrio')
        ->values();

    // ESTADÍSTICAS PARA GRÁFICOS
    $estadisticas = $this->generarEstadisticasPropiedades($propiedades);

    $barrios = Property::select('barrio')->distinct()->pluck('barrio');
    $estados = Property::getEstados();

    return view('admin.reportes.propiedades', compact('propiedades', 'barrios', 'estados', 'filtroBarrio', 'filtroEstado', 'estadisticas'));
}

/**
 * Generar estadísticas para gráficos de propiedades
 */
private function generarEstadisticasPropiedades($propiedades)
{
    $totalPropiedades = $propiedades->count();
    
    // Estadísticas por estado del servicio
    $porEstado = [];
    foreach ($propiedades->groupBy('estado_servicio') as $estado => $items) {
        $porEstado[$estado] = [
            'cantidad' => $items->count(),
            'porcentaje' => $totalPropiedades > 0 ? round(($items->count() / $totalPropiedades) * 100, 2) : 0
        ];
    }
    
    // Asegurar que existan todos los estados
    $estadosDisponibles = ['activo', 'cortado', 'corte_pendiente', 'pendiente_conexion', 'inactivo'];
    foreach ($estadosDisponibles as $estado) {
        if (!isset($porEstado[$estado])) {
            $porEstado[$estado] = ['cantidad' => 0, 'porcentaje' => 0];
        }
    }
    
    // Ordenar por cantidad
    uasort($porEstado, function($a, $b) {
        return $b['cantidad'] <=> $a['cantidad'];
    });

    // Estadísticas por barrio (top 10)
    $porBarrio = [];
    $barriosGroup = $propiedades->groupBy('barrio');
    foreach ($barriosGroup as $barrio => $items) {
        $porBarrio[$barrio] = [
            'cantidad' => $items->count(),
            'porcentaje' => $totalPropiedades > 0 ? round(($items->count() / $totalPropiedades) * 100, 2) : 0
        ];
    }
    
    // Ordenar y tomar solo top 10
    uasort($porBarrio, function($a, $b) {
        return $b['cantidad'] <=> $a['cantidad'];
    });
    $porBarrio = array_slice($porBarrio, 0, 10, true);

    // Estadísticas por deuda
    $conDeuda = $propiedades->where('tiene_deuda', 'Sí')->count();
    $sinDeuda = $propiedades->where('tiene_deuda', 'No')->count();
    
    $porDeuda = [
        'con_deuda' => [
            'cantidad' => $conDeuda,
            'porcentaje' => $totalPropiedades > 0 ? round(($conDeuda / $totalPropiedades) * 100, 2) : 0
        ],
        'sin_deuda' => [
            'cantidad' => $sinDeuda,
            'porcentaje' => $totalPropiedades > 0 ? round(($sinDeuda / $totalPropiedades) * 100, 2) : 0
        ]
    ];

    // Propiedades con trabajos pendientes
    $conTrabajoPendiente = $propiedades->where('trabajo_pendiente', '!=', 'Sin trabajo pendiente')->count();
    $sinTrabajoPendiente = $totalPropiedades - $conTrabajoPendiente;

    // Clientes únicos
    $clientesUnicos = collect($propiedades)->pluck('codigo_cliente')->unique()->count();

    return [
        'total_propiedades' => $totalPropiedades,
        'clientes_unicos' => $clientesUnicos,
        'promedio_propiedades_por_cliente' => $clientesUnicos > 0 ? round($totalPropiedades / $clientesUnicos, 2) : 0,
        'con_deuda' => $conDeuda,
        'sin_deuda' => $sinDeuda,
        'con_trabajo_pendiente' => $conTrabajoPendiente,
        'por_estado' => $porEstado,
        'por_barrio' => $porBarrio,
        'por_deuda' => $porDeuda,
        'porcentaje_con_deuda' => $totalPropiedades > 0 ? round(($conDeuda / $totalPropiedades) * 100, 2) : 0,
        'porcentaje_con_trabajo' => $totalPropiedades > 0 ? round(($conTrabajoPendiente / $totalPropiedades) * 100, 2) : 0
    ];
}
    /**
     * Lista simple de trabajos pendientes - Compacta para impresión
     */
    public function trabajosPendientes(Request $request)
    {
        $filtroBarrio = $request->get('barrio');
        $filtroTipo = $request->get('tipo_trabajo');

        $query = Property::with(['client'])
            ->whereNotNull('tipo_trabajo_pendiente');

        if ($filtroBarrio) {
            $query->where('barrio', 'like', "%{$filtroBarrio}%");
        }

        if ($filtroTipo) {
            $query->where('tipo_trabajo_pendiente', $filtroTipo);
        }

        $trabajos = $query->get()
            ->map(function($property) {
                return [
                    'codigo_cliente' => $property->client->codigo_cliente ?? 'N/A',
                    'cliente' => $property->client->nombre ?? 'Cliente no asignado',
                    'direccion' => $property->referencia,
                    'barrio' => $property->barrio,
                    'tipo_trabajo' => $property->texto_trabajo_pendiente,
                    'estado_actual' => $property->estado,
                    'fecha_solicitud' => $property->created_at->format('d/m/Y'),
                    'dias_pendiente' => $property->created_at->diffInDays(now())
                ];
            })
            ->sortBy('dias_pendiente')
            ->values();

        $barrios = Property::select('barrio')->distinct()->pluck('barrio');
        $tiposTrabajo = [
            'conexion_nueva' => 'Conexión Nueva',
            'corte_mora' => 'Corte por Mora', 
            'reconexion' => 'Reconexión'
        ];

        return view('admin.reportes.trabajos-pendientes', compact('trabajos', 'barrios', 'tiposTrabajo', 'filtroBarrio', 'filtroTipo'));
    }

    /**
     * Método auxiliar para obtener último mes pagado
     */
    private function obtenerUltimoMesPagado($propiedadId)
    {
        $ultimoPago = Pago::where('propiedad_id', $propiedadId)
            ->orderBy('mes_pagado', 'desc')
            ->first();

        if ($ultimoPago) {
            try {
                return Carbon::createFromFormat('Y-m', $ultimoPago->mes_pagado)
                    ->locale('es')
                    ->translatedFormat('M Y');
            } catch (\Exception $e) {
                return 'Fecha no válida';
            }
        }

        return 'Sin pagos';
    }
}