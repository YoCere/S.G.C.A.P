@extends('layouts.admin-ultralight')

@section('title', 'Dashboard - SGCAF')

@section('content_header')
    <h1>📊 Panel de control - COMITE DE AGUA LA GRAMPA</h1>
@stop

@section('content')
    <!-- Tarjetas de Métricas -->
    <div class="row">
        <!-- Clientes Activos -->
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($metrics['total_clientes_activos']) }}</h3>
                    <p>Clientes Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
                <a href="{{ route('admin.clients.index') }}" class="small-box-footer">
                    Más info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Recaudación Mes -->
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>Bs {{ number_format($metrics['recaudacion_mes_actual'], 2) }}</h3>
                    <p>Recaudación Mes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <a href="{{ route('admin.pagos.index') }}" class="small-box-footer">
                    Más info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Deuda Total -->
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>Bs {{ number_format($metrics['deuda_total_pendiente'], 2) }}</h3>
                    <p>Deuda Total</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <a href="{{ route('admin.debts.index') }}" class="small-box-footer">
                    Más info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Trabajos Pendientes -->
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ number_format($metrics['trabajos_pendientes']) }}</h3>
                    <p>Trabajos Pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tools"></i>
                </div>
                <a href="{{ route('admin.cortes.pendientes') }}" class="small-box-footer">
                    Ir a trabajar <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Propiedades Activas -->
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($metrics['propiedades_activas']) }}</h3>
                    <p>Propiedades Activas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-home"></i>
                </div>
                <a href="{{ route('admin.properties.index') }}" class="small-box-footer">
                    Más info <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>

        <!-- Propiedades Cortadas -->
        <div class="col-lg-2 col-md-4 col-sm-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ number_format($metrics['propiedades_cortadas']) }}</h3>
                    <p>Propiedades Cortadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
                <a href="{{ route('admin.cortes.cortadas') }}" class="small-box-footer">
                    Ver cortadas <i class="fas fa-arrow-circle-right"></i>
                </a>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Gráficos (8 columnas) -->
        <div class="col-md-8">
            <!-- Gráfico de Recaudación -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📈 Recaudación Últimos 6 Meses</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="recaudacionChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Gráfico de Estados -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">🎯 Estados de Propiedades</h3>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="estadosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alertas (4 columnas) -->
        <div class="col-md-4">
            <!-- TOP 5 Deudores -->
            <div class="card">
                <div class="card-header bg-danger">
                    <h3 class="card-title text-white">🔴 TOP 5 Deudores</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <tbody>
                                @forelse($alerts['top_deudores'] as $deudor)
                                <tr>
                                    <td>
                                        <small class="font-weight-bold">{{ $deudor['cliente'] }}</small>
                                        <br>
                                        <small class="text-muted">{{ $deudor['propiedad'] }}</small>
                                        <br>
                                        <small class="text-danger font-weight-bold">
                                            Bs {{ number_format($deudor['deuda_total'], 2) }}
                                        </small>
                                        <br>
                                        <small class="text-muted">
                                            {{ $deudor['meses_mora'] }} meses mora
                                        </small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-center text-muted py-3">
                                        🎉 No hay deudores críticos
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Trabajos Pendientes -->
            <div class="card mt-4">
                <div class="card-header bg-warning">
                    <h3 class="card-title">⚠️ Trabajos Pendientes</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <tbody>
                                @forelse($alerts['trabajos_pendientes_criticos'] as $trabajo)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas {{ $trabajo['icono'] }} text-{{ $trabajo['color'] }} mr-2"></i>
                                            <small class="font-weight-bold">{{ $trabajo['cliente'] }}</small>
                                        </div>
                                        <small class="text-muted d-block">{{ $trabajo['direccion'] }}</small>
                                        <span class="badge badge-{{ $trabajo['color'] }} badge-sm">
                                            {{ $trabajo['tipo_trabajo'] }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            {{ $trabajo['fecha_solicitud'] }}
                                        </small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td class="text-center text-muted py-3">
                                        ✅ No hay trabajos pendientes
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .chart-container {
            position: relative;
        }
        .small-box {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .small-box:hover {
            transform: translateY(-2px);
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de Recaudación
            const ctx1 = document.getElementById('recaudacionChart').getContext('2d');
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: @json($chartData['recaudacion_ultimos_meses']['meses']),
                    datasets: [{
                        label: 'Recaudación (Bs)',
                        data: @json($chartData['recaudacion_ultimos_meses']['recaudacion']),
                        borderColor: '#007bff',
                        backgroundColor: 'rgba(0, 123, 255, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Bs ' + context.parsed.y.toLocaleString('es-BO');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Bs ' + value.toLocaleString('es-BO');
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de Estados
            const ctx2 = document.getElementById('estadosChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: @json($chartData['estados_propiedades']['labels']),
                    datasets: [{
                        data: @json($chartData['estados_propiedades']['data']),
                        backgroundColor: @json($chartData['estados_propiedades']['colors']),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        });
    </script>
@stop