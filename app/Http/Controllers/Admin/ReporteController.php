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
     * Lista simple de deudores - Compacta para impresión
     */
    public function morosidad(Request $request)
    {
        $filtroBarrio = $request->get('barrio');
        $filtroMeses = $request->get('meses_mora', 3);

        $query = Property::with(['client', 'debts' => function($query) {
            $query->pendientes();
        }])
        ->whereHas('debts', function($query) {
            $query->pendientes();
        });

        // Filtros
        if ($filtroBarrio) {
            $query->where('barrio', 'like', "%{$filtroBarrio}%");
        }

        $propiedades = $query->get()
            ->map(function($property) {
                $mesesAdeudados = $property->obtenerMesesAdeudados();
                $mesesMora = count($mesesAdeudados);
                $deudaTotal = $property->total_deudas_pendientes;

                return [
                    'codigo_cliente' => $property->client->codigo_cliente ?? 'N/A',
                    'cliente' => $property->client->nombre ?? 'Cliente no asignado',
                    'propiedad' => $property->referencia,
                    'barrio' => $property->barrio,
                    'deuda_total' => $deudaTotal,
                    'meses_mora' => $mesesMora,
                    'estado_servicio' => $property->estado,
                    'ultimo_mes_pagado' => $this->obtenerUltimoMesPagado($property->id)
                ];
            })
            ->filter(function($item) use ($filtroMeses) {
                return $item['meses_mora'] >= $filtroMeses;
            })
            ->sortByDesc('deuda_total')
            ->values();

        $barrios = Property::select('barrio')->distinct()->pluck('barrio');

        return view('admin.reportes.morosidad', compact('propiedades', 'barrios', 'filtroBarrio', 'filtroMeses'));
    }

    /**
     * Lista simple de clientes - Compacta para impresión
     */
    public function clientes(Request $request)
    {
        $filtroBarrio = $request->get('barrio');
        $filtroEstado = $request->get('estado');

        $query = Client::with(['properties']);

        if ($filtroEstado) {
            $query->where('estado_cuenta', $filtroEstado);
        }

        $clientes = $query->get()
            ->map(function($client) use ($filtroBarrio) {
                $propiedades = $client->properties;
                
                if ($filtroBarrio) {
                    $propiedades = $propiedades->where('barrio', 'like', "%{$filtroBarrio}%");
                }

                return [
                    'codigo' => $client->codigo_cliente,
                    'nombre' => $client->nombre,
                    'ci' => $client->ci,
                    'telefono' => $client->telefono,
                    'barrio_principal' => $propiedades->first()->barrio ?? 'N/A',
                    'estado_cliente' => $client->estado_cuenta,
                    'total_propiedades' => $propiedades->count(),
                    'fecha_registro' => $client->fecha_registro_formateada
                ];
            })
            ->sortBy('nombre')
            ->values();

        $barrios = Property::select('barrio')->distinct()->pluck('barrio');

        return view('admin.reportes.clientes', compact('clientes', 'barrios', 'filtroBarrio', 'filtroEstado'));
    }

    /**
     * Lista simple de propiedades - Compacta para impresión
     */
    public function propiedades(Request $request)
    {
        $filtroBarrio = $request->get('barrio');
        $filtroEstado = $request->get('estado');

        $query = Property::with(['client']);

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
                    'tiene_deuda' => $property->total_deudas_pendientes > 0 ? 'Sí' : 'No'
                ];
            })
            ->sortBy('barrio')
            ->values();

        $barrios = Property::select('barrio')->distinct()->pluck('barrio');
        $estados = Property::getEstados();

        return view('admin.reportes.propiedades', compact('propiedades', 'barrios', 'estados', 'filtroBarrio', 'filtroEstado'));
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
            return Carbon::createFromFormat('Y-m', $ultimoPago->mes_pagado)
                ->locale('es')
                ->translatedFormat('M Y');
        }

        return 'Sin pagos';
    }
}