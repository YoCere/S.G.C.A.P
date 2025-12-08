@extends('adminlte::page')

@section('title', 'Reporte de Propiedades - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>🏠 Reporte de Propiedades</h1>
        <div>
            <button onclick="window.print()" class="btn btn-secondary btn-sm no-print">
                <i class="fas fa-print mr-1"></i> Imprimir/PDF
            </button>
            <a href="{{ route('admin.reportes.index') }}" class="btn btn-default btn-sm no-print">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>
    <p class="text-muted">Inventario de propiedades - {{ now()->format('d/m/Y H:i') }}</p>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card no-print mb-4">
        <div class="card-header">
            <h3 class="card-title">🔍 Filtros de Búsqueda</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reportes.propiedades') }}" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="barrio">Barrio:</label>
                        <select name="barrio" id="barrio" class="form-control form-control-sm">
                            <option value="">Todos los barrios</option>
                            @foreach($barrios as $barrio)
                                <option value="{{ $barrio }}" {{ $filtroBarrio == $barrio ? 'selected' : '' }}>
                                    {{ $barrio }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="estado">Estado Servicio:</label>
                        <select name="estado" id="estado" class="form-control form-control-sm">
                            <option value="">Todos los estados</option>
                            @foreach($estados as $estado)
                                <option value="{{ $estado }}" {{ $filtroEstado == $estado ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $estado)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-group w-100">
                        <button type="submit" class="btn btn-primary btn-sm w-100 mb-1">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.reportes.propiedades') }}" class="btn btn-default btn-sm w-100">
                            <i class="fas fa-redo mr-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resumen -->
    <div class="row mb-4">
        <div class="col-md-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $estadisticas['total_propiedades'] }}</h3>
                    <p>Total Propiedades</p>
                    <small>{{ $estadisticas['clientes_unicos'] }} clientes únicos</small>
                </div>
                <div class="icon">
                    <i class="fas fa-home"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $propiedades->where('estado_servicio', 'activo')->count() }}</h3>
                    <p>Propiedades Activas</p>
                    <small>{{ $estadisticas['promedio_propiedades_por_cliente'] }} por cliente</small>
                </div>
                <div class="icon">
                    <i class="fas fa-plug"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas['con_deuda'] }}</h3>
                    <p>Propiedades con Deuda</p>
                    <small>{{ $estadisticas['porcentaje_con_deuda'] }}% del total</small>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $estadisticas['con_trabajo_pendiente'] }}</h3>
                    <p>Trabajos Pendientes</p>
                    <small>{{ $estadisticas['porcentaje_con_trabajo'] }}% del total</small>
                </div>
                <div class="icon">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4 no-print">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📊 Distribución por Estado del Servicio</h3>
                </div>
                <div class="card-body">
                    <canvas id="graficoEstado" height="200"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Estado</th>
                                    <th class="text-center">Propiedades</th>
                                    <th class="text-center">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estadisticas['por_estado'] as $estado => $data)
                                @if($data['cantidad'] > 0)
                                <tr>
                                    <td>
                                        @php
                                            $estadoTexto = ucfirst(str_replace('_', ' ', $estado));
                                            $badgeClass = [
                                                'activo' => 'success',
                                                'cortado' => 'danger',
                                                'corte_pendiente' => 'warning',
                                                'pendiente_conexion' => 'info',
                                                'inactivo' => 'secondary'
                                            ][$estado] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-{{ $badgeClass }}">{{ strtoupper($estadoTexto) }}</span>
                                    </td>
                                    <td class="text-center">{{ $data['cantidad'] }}</td>
                                    <td class="text-center">{{ $data['porcentaje'] }}%</td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📊 Deuda: Propiedades con Deuda vs Al Día</h3>
                </div>
                <div class="card-body">
                    <canvas id="graficoDeuda" height="200"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Situación</th>
                                    <th class="text-center">Propiedades</th>
                                    <th class="text-center">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-danger">CON DEUDA</span></td>
                                    <td class="text-center">{{ $estadisticas['por_deuda']['con_deuda']['cantidad'] }}</td>
                                    <td class="text-center">{{ $estadisticas['por_deuda']['con_deuda']['porcentaje'] }}%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-success">AL DÍA</span></td>
                                    <td class="text-center">{{ $estadisticas['por_deuda']['sin_deuda']['cantidad'] }}</td>
                                    <td class="text-center">{{ $estadisticas['por_deuda']['sin_deuda']['porcentaje'] }}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico de Barrios -->
    @if(count($estadisticas['por_barrio']) > 0)
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📍 Top 10 Barrios con más Propiedades</h3>
                </div>
                <div class="card-body">
                    <canvas id="graficoBarrios" height="150"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Barrio</th>
                                    <th class="text-center">Propiedades</th>
                                    <th class="text-center">%</th>
                                    <th class="text-center">Estado Mayoritario</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $index = 1; @endphp
                                @foreach($estadisticas['por_barrio'] as $barrio => $data)
                                @php
                                    // Obtener estado predominante en este barrio
                                    $propiedadesBarrio = $propiedades->where('barrio', $barrio);
                                    $estadosBarrio = $propiedadesBarrio->groupBy('estado_servicio');
                                    $estadoPredominante = $estadosBarrio->isNotEmpty() 
                                        ? $estadosBarrio->sortByDesc('count')->keys()->first() 
                                        : 'N/A';
                                @endphp
                                <tr>
                                    <td>{{ $index++ }}</td>
                                    <td>{{ $barrio }}</td>
                                    <td class="text-center">{{ $data['cantidad'] }}</td>
                                    <td class="text-center">{{ $data['porcentaje'] }}%</td>
                                    <td class="text-center">
                                        @if($estadoPredominante != 'N/A')
                                            @php
                                                $badgeClass = [
                                                    'activo' => 'success',
                                                    'cortado' => 'danger',
                                                    'corte_pendiente' => 'warning',
                                                    'pendiente_conexion' => 'info',
                                                    'inactivo' => 'secondary'
                                                ][$estadoPredominante] ?? 'secondary';
                                            @endphp
                                            <span class="badge badge-{{ $badgeClass }}">
                                                {{ strtoupper(substr($estadoPredominante, 0, 3)) }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista de Propiedades -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                📋 Lista de Propiedades
                <small class="text-muted">({{ $propiedades->count() }} registros)</small>
            </h3>
        </div>
        <div class="card-body p-0">
            @if($propiedades->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover table-print mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="10%">ID</th>
                            <th width="25%">Dirección</th>
                            <th width="15%">Barrio</th>
                            <th width="20%">Cliente</th>
                            <th width="10%" class="text-center">Código</th>
                            <th width="10%" class="text-center">Deuda</th>
                            <th width="10%" class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($propiedades as $propiedad)
                        <tr>
                            <td>
                                <strong class="text-primary">#{{ $propiedad['codigo_propiedad'] }}</strong>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $propiedad['direccion'] }}</div>
                                @if($propiedad['trabajo_pendiente'] != 'Sin trabajo pendiente')
                                    <small class="text-warning">
                                        <i class="fas fa-clock mr-1"></i>{{ $propiedad['trabajo_pendiente'] }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted">{{ $propiedad['barrio'] }}</span>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $propiedad['cliente'] }}</div>
                                @if(isset($propiedad['tarifa']) && $propiedad['tarifa'])
                                    <small class="text-info">{{ $propiedad['tarifa'] }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $propiedad['codigo_cliente'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($propiedad['tiene_deuda'] == 'Sí')
                                    <span class="badge badge-danger">CON DEUDA</span>
                                @else
                                    <span class="badge badge-success">AL DÍA</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($propiedad['estado_servicio'] == 'activo')
                                    <span class="badge badge-success">ACTIVO</span>
                                @elseif($propiedad['estado_servicio'] == 'cortado')
                                    <span class="badge badge-danger">CORTADO</span>
                                @elseif($propiedad['estado_servicio'] == 'corte_pendiente')
                                    <span class="badge badge-warning">PENDIENTE</span>
                                @else
                                    <span class="badge badge-secondary">{{ strtoupper($propiedad['estado_servicio']) }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>No se encontraron propiedades</h4>
                <p class="text-muted">No hay propiedades que coincidan con los filtros aplicados.</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Información del Reporte -->
    <div class="card no-print mt-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6>📊 Información del Reporte:</h6>
                    <ul class="list-unstyled">
                        <li><strong>Fecha generación:</strong> {{ now()->format('d/m/Y H:i') }}</li>
                        <li><strong>Total propiedades:</strong> {{ $propiedades->count() }}</li>
                        <li><strong>Clientes únicos:</strong> {{ $estadisticas['clientes_unicos'] }}</li>
                        <li><strong>Propiedades con deuda:</strong> {{ $estadisticas['con_deuda'] }} ({{ $estadisticas['porcentaje_con_deuda'] }}%)</li>
                        <li><strong>Trabajos pendientes:</strong> {{ $estadisticas['con_trabajo_pendiente'] }} ({{ $estadisticas['porcentaje_con_trabajo'] }}%)</li>
                        <li><strong>Filtro barrio:</strong> {{ $filtroBarrio ?: 'Todos' }}</li>
                        <li><strong>Filtro estado:</strong> {{ $filtroEstado ? ucfirst(str_replace('_', ' ', $filtroEstado)) : 'Todos' }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>🎯 Leyenda de Estados:</h6>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge badge-success">ACTIVO</span>
                        <span class="badge badge-danger">CORTADO</span>
                        <span class="badge badge-warning">PENDIENTE</span>
                        <span class="badge badge-info">PEND. CONEXIÓN</span>
                        <span class="badge badge-secondary">INACTIVO</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-danger">CON DEUDA</span>
                        <span class="badge badge-success">AL DÍA</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-print {
            font-size: 12px;
        }
        .table-print th,
        .table-print td {
            padding: 6px 8px;
        }
        .small-box {
            border-radius: 5px;
        }
        .no-print {
            display: block;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            .card-header {
                background: white !important;
                color: black !important;
            }
            .table-print {
                font-size: 12px;
            }
            .badge {
                border: 1px solid #000;
                font-size: 10px;
            }
        }
    </style>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Datos para gráficos
        const datosEstado = {!! json_encode($estadisticas['por_estado']) !!};
        const datosDeuda = {!! json_encode($estadisticas['por_deuda']) !!};
        const datosBarrios = {!! json_encode($estadisticas['por_barrio']) !!};

        // 1. Gráfico de estado del servicio
        const ctxEstado = document.getElementById('graficoEstado').getContext('2d');
        const labelsEstado = Object.keys(datosEstado).map(estado => {
            return estado.charAt(0).toUpperCase() + estado.slice(1).replace('_', ' ');
        });
        const dataEstado = Object.values(datosEstado).map(data => data.cantidad);
        
        // Colores para estados
        const estadoColors = {
            'activo': '#28a745',
            'cortado': '#dc3545',
            'corte_pendiente': '#ffc107',
            'pendiente_conexion': '#17a2b8',
            'inactivo': '#6c757d'
        };

        const backgroundColorsEstado = Object.keys(datosEstado).map(estado => {
            return estadoColors[estado] || '#6c757d';
        });

        new Chart(ctxEstado, {
            type: 'pie',
            data: {
                labels: labelsEstado,
                datasets: [{
                    data: dataEstado,
                    backgroundColor: backgroundColorsEstado,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // 2. Gráfico de deuda
        const ctxDeuda = document.getElementById('graficoDeuda').getContext('2d');
        new Chart(ctxDeuda, {
            type: 'doughnut',
            data: {
                labels: ['CON DEUDA', 'AL DÍA'],
                datasets: [{
                    data: [
                        datosDeuda.con_deuda.cantidad,
                        datosDeuda.sin_deuda.cantidad
                    ],
                    backgroundColor: ['#dc3545', '#28a745'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // 3. Gráfico de barrios (si hay datos)
        if (Object.keys(datosBarrios).length > 0) {
            const ctxBarrios = document.getElementById('graficoBarrios').getContext('2d');
            const barriosLabels = Object.keys(datosBarrios);
            const barriosData = Object.values(datosBarrios).map(data => data.cantidad);
            
            new Chart(ctxBarrios, {
                type: 'bar',
                data: {
                    labels: barriosLabels,
                    datasets: [{
                        label: 'Propiedades por Barrio',
                        data: barriosData,
                        backgroundColor: '#17a2b8',
                        borderColor: '#17a2b8',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Cantidad de Propiedades'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Barrios'
                            }
                        }
                    }
                }
            });
        }

        // Mensaje de carga al aplicar filtros
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            const button = this.querySelector('button[type="submit"]');
            button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Filtrando...';
            button.disabled = true;
        });
    });
</script>
@stop