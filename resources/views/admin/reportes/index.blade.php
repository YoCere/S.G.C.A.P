@extends('adminlte::page')

@section('title', 'Reportes del Sistema')

@section('content_header')
    <h1 class="h5 font-weight-bold">Dashboard de Reportes</h1>
    <small class="text-muted">Estadísticas y reportes del sistema SGCAF</small>
@stop

@section('content')
    <!-- Tarjetas de Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-2 col-sm-6">
            <div class="info-box bg-primary">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Clientes</span>
                    <span class="info-box-number">{{ $estadisticas['total_clientes'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-home"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Propiedades</span>
                    <span class="info-box-number">{{ $estadisticas['total_propiedades'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-sm-6">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Prop. Morosas</span>
                    <span class="info-box-number">{{ $estadisticas['propiedades_morosas'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Deuda Total</span>
                    <span class="info-box-number">Bs. {{ number_format($estadisticas['deuda_total'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Ingresos Mes</span>
                    <span class="info-box-number">Bs. {{ number_format($estadisticas['ingresos_mes_actual'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid de Reportes Disponibles -->
    <div class="row">
        <!-- Reporte de Morosidad -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-reporte h-100">
                <div class="card-header bg-warning">
                    <h6 class="mb-0 text-white"><i class="fas fa-exclamation-triangle mr-2"></i>Morosidad</h6>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-file-invoice-dollar fa-3x text-warning mb-3"></i>
                    <h5 class="card-title">Reporte de Morosidad</h5>
                    <p class="card-text text-muted small">
                        Clientes y propiedades con deudas pendientes, meses en mora y estados de corte.
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('admin.reportes.morosidad') }}" class="btn btn-warning btn-block btn-sm">
                        <i class="fas fa-chart-bar mr-1"></i>Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Ingresos -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-reporte h-100">
                <div class="card-header bg-success">
                    <h6 class="mb-0 text-white"><i class="fas fa-chart-line mr-2"></i>Ingresos</h6>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Reporte de Ingresos</h5>
                    <p class="card-text text-muted small">
                        Ingresos mensuales, métodos de pago y comparativos históricos.
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('admin.reportes.ingresos') }}" class="btn btn-success btn-block btn-sm">
                        <i class="fas fa-chart-pie mr-1"></i>Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Cortes -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-reporte h-100">
                <div class="card-header bg-danger">
                    <h6 class="mb-0 text-white"><i class="fas fa-bolt mr-2"></i>Cortes</h6>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-tint-slash fa-3x text-danger mb-3"></i>
                    <h5 class="card-title">Reporte de Cortes</h5>
                    <p class="card-text text-muted small">
                        Propiedades en corte pendiente, cortadas y multas aplicadas.
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('admin.reportes.cortes') }}" class="btn btn-danger btn-block btn-sm">
                        <i class="fas fa-list mr-1"></i>Ver Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Propiedades -->
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card card-reporte h-100">
                <div class="card-header bg-info">
                    <h6 class="mb-0 text-white"><i class="fas fa-home mr-2"></i>Propiedades</h6>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-building fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Reporte de Propiedades</h5>
                    <p class="card-text text-muted small">
                        Distribución por estados, zonas y estadísticas generales.
                    </p>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('admin.reportes.propiedades') }}" class="btn btn-info btn-block btn-sm">
                        <i class="fas fa-chart-area mr-1"></i>Ver Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Acceso Rápido desde el Menú Lateral -->
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-rocket mr-2"></i>Acceso Rápido</h6>
        </div>
        <div class="card-body">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-primary btn-block">
                        <i class="fas fa-users mr-1"></i>Clientes
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-info btn-block">
                        <i class="fas fa-home mr-1"></i>Propiedades
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-success btn-block">
                        <i class="fas fa-receipt mr-1"></i>Pagos
                    </a>
                </div>
                <div class="col-md-3 col-6 mb-3">
                    <a href="{{ route('admin.cortes.pendientes') }}" class="btn btn-outline-warning btn-block">
                        <i class="fas fa-bolt mr-1"></i>Cortes Pend.
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .info-box {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 0.25rem;
            background: #fff;
            display: flex;
            margin-bottom: 1rem;
            min-height: 80px;
            padding: 0.5rem;
            position: relative;
        }
        .info-box .info-box-icon {
            border-radius: 0.25rem;
            align-items: center;
            display: flex;
            font-size: 1.875rem;
            justify-content: center;
            text-align: center;
            width: 70px;
        }
        .info-box .info-box-content {
            display: flex;
            flex-direction: column;
            justify-content: center;
            line-height: 1.8;
            flex: 1;
            padding: 0 10px;
        }
        .info-box .info-box-text {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        .info-box .info-box-number {
            display: block;
            margin-top: 0.25rem;
            font-weight: 700;
            font-size: 1.2rem;
        }
        .card-reporte {
            transition: transform 0.2s;
            border: 1px solid #e3e6f0;
        }
        .card-reporte:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,.1);
        }
    </style>
@stop