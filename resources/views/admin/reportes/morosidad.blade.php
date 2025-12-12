@extends('layouts.admin-ultralight')

@section('title', 'Reporte de Morosidad - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>🔴 Reporte de Morosidad</h1>
        <div>
            <button onclick="window.print()" class="btn btn-secondary btn-sm no-print">
                <i class="fas fa-print mr-1"></i> Imprimir/PDF
            </button>
            <a href="{{ route('admin.reportes.index') }}" class="btn btn-default btn-sm no-print">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>
    <p class="text-muted">Lista de clientes con deudas pendientes - {{ now()->format('d/m/Y H:i') }}</p>
@stop

@section('content')
    <!-- Filtros Avanzados -->
    <div class="card no-print mb-4">
        <div class="card-header">
            <h3 class="card-title">🔍 Filtros Avanzados</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reportes.morosidad') }}" class="row">
                <div class="col-md-3">
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
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="meses_mora">Mínimo meses:</label>
                        <select name="meses_mora" id="meses_mora" class="form-control form-control-sm">
                            <option value="1" {{ $filtroMeses == 1 ? 'selected' : '' }}>1+ mes</option>
                            <option value="2" {{ $filtroMeses == 2 ? 'selected' : '' }}>2+ meses</option>
                            <option value="3" {{ $filtroMeses == 3 ? 'selected' : '' }}>3+ meses</option>
                            <option value="6" {{ $filtroMeses == 6 ? 'selected' : '' }}>6+ meses</option>
                            <option value="12" {{ $filtroMeses == 12 ? 'selected' : '' }}>12+ meses</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="anio">Año:</label>
                        <select name="anio" id="anio" class="form-control form-control-sm">
                            @foreach($aniosDisponibles as $anio)
                                <option value="{{ $anio }}" {{ $filtroAnio == $anio ? 'selected' : '' }}>
                                    {{ $anio == 'todos' ? 'Todos los años' : $anio }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="tarifa">Tipo Cliente (Tarifa):</label>
                        <select name="tarifa" id="tarifa" class="form-control form-control-sm">
                            @foreach($tarifas as $tarifa)
                                <option value="{{ $tarifa }}" {{ $filtroTarifa == $tarifa ? 'selected' : '' }}>
                                    {{ $tarifa == 'todos' ? 'Todas las tarifas' : $tarifa }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group w-100">
                        <button type="submit" class="btn btn-primary btn-sm w-100 mb-1">
                            <i class="fas fa-filter mr-1"></i> Aplicar
                        </button>
                        <a href="{{ route('admin.reportes.morosidad') }}" class="btn btn-default btn-sm w-100">
                            <i class="fas fa-redo mr-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas Principales -->
    <div class="row mb-4">
        <div class="col-md-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $estadisticas['total_deudores'] }}</h3>
                    <p>Clientes Morosos</p>
                    <small>{{ $estadisticas['porcentaje_morosidad'] }}% del total</small>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>Bs {{ number_format($estadisticas['deuda_total'], 2) }}</h3>
                    <p>Deuda Total</p>
                    <small>Bs {{ number_format($estadisticas['promedio_deuda'], 2) }} promedio</small>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($propiedades->avg('meses_mora'), 1) }}</h3>
                    <p>Promedio Meses</p>
                    <small>{{ $estadisticas['total_clientes'] }} clientes totales</small>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>Bs {{ number_format($propiedades->max('deuda_total') ?? 0, 2) }}</h3>
                    <p>Deuda Máxima</p>
                    <small>Top 10 deudores</small>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4 no-print">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📊 Distribución por Tipo de Cliente (Tarifa)</h3>
                </div>
                <div class="card-body">
                    <canvas id="graficoCliente" height="200"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tarifa/Tipo Cliente</th>
                                    <th class="text-center">Clientes</th>
                                    <th class="text-center">%</th>
                                    <th class="text-right">Deuda Total</th>
                                    <th class="text-right">Promedio</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $porTipoCliente = $estadisticas['por_tipo_cliente']->sortByDesc('cantidad');
                                @endphp
                                @foreach($porTipoCliente as $data)
                                <tr>
                                    <td>{{ $data['tipo'] }}</td>
                                    <td class="text-center">{{ $data['cantidad'] }}</td>
                                    <td class="text-center">{{ $data['porcentaje'] }}%</td>
                                    <td class="text-right">Bs {{ number_format($data['deuda_total'], 2) }}</td>
                                    <td class="text-right">Bs {{ number_format($data['promedio_deuda'], 2) }}</td>
                                </tr>
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
                    <h3 class="card-title">📊 Distribución por Meses de Mora</h3>
                </div>
                <div class="card-body">
                    <canvas id="graficoMeses" height="200"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Rango Meses</th>
                                    <th class="text-center">Clientes</th>
                                    <th class="text-center">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estadisticas['por_meses_mora'] as $rango => $cantidad)
                                <tr>
                                    <td>{{ $rango }} meses</td>
                                    <td class="text-center">{{ $cantidad }}</td>
                                    <td class="text-center">
                                        @if($estadisticas['total_deudores'] > 0)
                                            {{ round(($cantidad / $estadisticas['total_deudores']) * 100, 1) }}%
                                        @else
                                            0%
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

    <!-- Gráfico de comparación por tarifa -->
    <div class="row mb-4 no-print">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📊 Morosidad por Tarifa (Clientes vs Deudores)</h3>
                </div>
                <div class="card-body">
                    <canvas id="graficoTarifasComparacion" height="150"></canvas>
                    <div class="mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Tarifa</th>
                                    <th class="text-center">Total Clientes</th>
                                    <th class="text-center">Clientes Deudores</th>
                                    <th class="text-center">% Morosidad</th>
                                    <th class="text-right">Deuda Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($estadisticas['estadisticas_tarifas'] as $tarifa => $data)
                                @php
                                    $deudaTarifa = $estadisticas['por_tipo_cliente']->firstWhere('tipo', $tarifa);
                                @endphp
                                <tr>
                                    <td>{{ $tarifa }}</td>
                                    <td class="text-center">{{ $data['total_clientes'] }}</td>
                                    <td class="text-center">{{ $data['total_deudores'] }}</td>
                                    <td class="text-center">{{ $data['porcentaje_morosidad'] }}%</td>
                                    <td class="text-right">
                                        Bs {{ number_format($deudaTarifa['deuda_total'] ?? 0, 2) }}
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

    <!-- Top 10 Deudores -->
    @if($estadisticas['top_deudores']->count() > 0)
    <div class="card mb-4 no-print">
        <div class="card-header">
            <h3 class="card-title">🏆 Top 10 Deudores</h3>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Código</th>
                            <th>Tipo</th>
                            <th class="text-right">Deuda</th>
                            <th class="text-center">Meses</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($estadisticas['top_deudores'] as $index => $deudor)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $deudor['cliente'] }}</td>
                            <td><strong>{{ $deudor['codigo_cliente'] }}</strong></td>
                            <td>{{ $deudor['tipo_cliente'] }}</td>
                            <td class="text-right font-weight-bold text-danger">
                                Bs {{ number_format($deudor['deuda_total'], 2) }}
                            </td>
                            <td class="text-center">
                                @if($deudor['meses_mora'] >= 12)
                                    <span class="badge badge-danger">{{ $deudor['meses_mora'] }}</span>
                                @elseif($deudor['meses_mora'] >= 6)
                                    <span class="badge badge-warning">{{ $deudor['meses_mora'] }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ $deudor['meses_mora'] }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($deudor['estado_servicio'] == 'cortado')
                                    <span class="badge badge-danger">CORTADO</span>
                                @elseif($deudor['estado_servicio'] == 'corte_pendiente')
                                    <span class="badge badge-warning">PENDIENTE</span>
                                @else
                                    <span class="badge badge-success">ACTIVO</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Lista Completa de Morosos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                📋 Lista Completa de Clientes Morosos 
                <small class="text-muted">({{ $propiedades->count() }} registros)</small>
            </h3>
        </div>
        <div class="card-body p-0">
            @if($propiedades->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover table-print mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="10%">Código</th>
                            <th width="25%">Cliente</th>
                            <th width="20%">Dirección</th>
                            <th width="10%" class="text-center">Tipo</th>
                            <th width="10%" class="text-center">Barrio</th>
                            <th width="10%" class="text-right">Deuda</th>
                            <th width="7%" class="text-center">Meses</th>
                            <th width="8%" class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($propiedades as $item)
                        <tr>
                            <td>
                                <strong class="text-danger">{{ $item['codigo_cliente'] }}</strong>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $item['cliente'] }}</div>
                                <small class="text-muted">{{ $item['tarifa'] }}</small>
                            </td>
                            <td>
                                <div class="text-primary">{{ $item['propiedad'] }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $item['tipo_cliente'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $item['barrio'] }}</span>
                            </td>
                            <td class="text-right">
                                <span class="font-weight-bold text-danger">
                                    Bs {{ number_format($item['deuda_total'], 2) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item['meses_mora'] >= 12)
                                    <span class="badge badge-danger">{{ $item['meses_mora'] }}</span>
                                @elseif($item['meses_mora'] >= 6)
                                    <span class="badge badge-warning">{{ $item['meses_mora'] }}</span>
                                @else
                                    <span class="badge badge-secondary">{{ $item['meses_mora'] }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item['estado_servicio'] == 'cortado')
                                    <span class="badge badge-danger">CORTADO</span>
                                @elseif($item['estado_servicio'] == 'corte_pendiente')
                                    <span class="badge badge-warning">PENDIENTE</span>
                                @else
                                    <span class="badge badge-success">ACTIVO</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-light">
                            <td colspan="5" class="text-right font-weight-bold">TOTAL GENERAL:</td>
                            <td class="text-right font-weight-bold text-danger">
                                Bs {{ number_format($propiedades->sum('deuda_total'), 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4>¡No hay morosidad!</h4>
                <p class="text-muted">No se encontraron clientes con deudas pendientes según los filtros aplicados.</p>
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
                        <li><strong>Total registros:</strong> {{ $propiedades->count() }}</li>
                        <li><strong>Deuda total:</strong> Bs {{ number_format($propiedades->sum('deuda_total'), 2) }}</li>
                        <li><strong>Filtro barrio:</strong> {{ $filtroBarrio ?: 'Todos' }}</li>
                        <li><strong>Mínimo meses mora:</strong> {{ $filtroMeses }}+ meses</li>
                        <li><strong>Año:</strong> {{ $filtroAnio }}</li>
                        <li><strong>Tipo cliente:</strong> {{ $filtroTarifa && $filtroTarifa != 'todos' ? $filtroTarifa : 'Todos' }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>🎯 Leyenda de Estados:</h6>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge badge-success">ACTIVO</span>
                        <span class="badge badge-warning">PENDIENTE</span>
                        <span class="badge badge-danger">CORTADO</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2 mb-2">
                        <span class="badge badge-danger">12+ meses</span>
                        <span class="badge badge-warning">6+ meses</span>
                        <span class="badge badge-secondary">1-5 meses</span>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-info">NORMAL</span>
                        <span class="badge badge-primary">ADULTO MAYOR</span>
                        <span class="badge badge-warning">COMERCIO</span>
                        <span class="badge badge-dark">INDUSTRIAL</span>
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
        .small-box .inner h3 {
            font-size: 1.5rem;
        }
        .small-box .inner p {
            font-size: 0.85rem;
        }
        .small-box .inner small {
            font-size: 0.75rem;
            display: block;
            margin-top: 5px;
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
            .table-print th,
            .table-print td {
                padding: 5px 6px;
            }
            .badge {
                border: 1px solid #000;
                font-size: 10px;
            }
        }
        @media (max-width: 768px) {
            .table-print {
                font-size: 11px;
            }
            .small-box .inner h3 {
                font-size: 1.2rem;
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
        const datosTipoCliente = {!! json_encode($estadisticas['por_tipo_cliente']->values()) !!};
        const datosMesesMora = {!! json_encode($estadisticas['por_meses_mora']) !!};
        const datosTarifasComparacion = {!! json_encode($estadisticas['estadisticas_tarifas']) !!};

        // Gráfico de torta por tipo de cliente (tarifa)
        const ctxCliente = document.getElementById('graficoCliente').getContext('2d');
        const labelsCliente = datosTipoCliente.map(item => item.tipo);
        const dataCliente = datosTipoCliente.map(item => item.cantidad);
        
        // Colores para las tarifas
        const colorsByTarifa = {
            'Adulto Mayor': ['#FF6384', '#FFB1C1'],
            'Comercial': ['#36A2EB', '#A8D4FF'],
            'Normal': ['#FFCE56', '#FFE8A8'],
            'Industrial': ['#4BC0C0', '#A6E3E3'],
            'Especial': ['#9966FF', '#CCB3FF'],
            'Sin tarifa': ['#C9CBCF', '#E9EAEC']
        };

        const backgroundColorsCliente = labelsCliente.map(tarifa => {
            return colorsByTarifa[tarifa] ? colorsByTarifa[tarifa][0] : '#C9CBCF';
        });

        new Chart(ctxCliente, {
            type: 'pie',
            data: {
                labels: labelsCliente,
                datasets: [{
                    data: dataCliente,
                    backgroundColor: backgroundColorsCliente,
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

        // Gráfico de dona por meses de mora
        const ctxMeses = document.getElementById('graficoMeses').getContext('2d');
        new Chart(ctxMeses, {
            type: 'doughnut',
            data: {
                labels: ['1-3 meses', '4-6 meses', '7-12 meses', '13+ meses'],
                datasets: [{
                    data: [
                        datosMesesMora['1-3'],
                        datosMesesMora['4-6'],
                        datosMesesMora['7-12'],
                        datosMesesMora['13+']
                    ],
                    backgroundColor: [
                        '#36A2EB',  // 1-3 meses: Azul
                        '#FFCE56',  // 4-6 meses: Amarillo
                        '#FF6384',  // 7-12 meses: Rojo
                        '#9966FF'   // 13+ meses: Morado
                    ]
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

        // Gráfico de comparación por tarifa
        const ctxTarifasComparacion = document.getElementById('graficoTarifasComparacion').getContext('2d');
        const tarifasLabels = Object.keys(datosTarifasComparacion);
        const totalClientesData = tarifasLabels.map(tarifa => datosTarifasComparacion[tarifa].total_clientes);
        const deudoresData = tarifasLabels.map(tarifa => datosTarifasComparacion[tarifa].total_deudores);

        new Chart(ctxTarifasComparacion, {
            type: 'bar',
            data: {
                labels: tarifasLabels,
                datasets: [
                    {
                        label: 'Total Clientes',
                        data: totalClientesData,
                        backgroundColor: '#36A2EB',
                        borderColor: '#36A2EB',
                        borderWidth: 1
                    },
                    {
                        label: 'Clientes Deudores',
                        data: deudoresData,
                        backgroundColor: '#FF6384',
                        borderColor: '#FF6384',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Cantidad de Clientes'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tipos de Tarifa'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterLabel: function(context) {
                                const tarifa = context.label;
                                const data = datosTarifasComparacion[tarifa];
                                const porcentaje = data.porcentaje_morosidad;
                                return `Morosidad: ${porcentaje}%`;
                            }
                        }
                    }
                }
            }
        });

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