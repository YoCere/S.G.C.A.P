@extends('layouts.admin-ultralight')

@section('title', 'Reporte de Trabajos Pendientes - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>🛠️ Reporte de Trabajos Pendientes</h1>
        <div>
            <button onclick="window.print()" class="btn btn-secondary btn-sm no-print">
                <i class="fas fa-print mr-1"></i> Imprimir/PDF
            </button>
            <a href="{{ route('admin.reportes.index') }}" class="btn btn-default btn-sm no-print">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>
    <p class="text-muted">Lista de trabajos pendientes de ejecución - {{ now()->format('d/m/Y H:i') }}</p>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card no-print mb-4">
        <div class="card-header">
            <h3 class="card-title">🔍 Filtros de Búsqueda</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reportes.trabajos-pendientes') }}" class="row">
                <div class="col-md-5">
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
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="tipo_trabajo">Tipo de Trabajo:</label>
                        <select name="tipo_trabajo" id="tipo_trabajo" class="form-control form-control-sm">
                            <option value="">Todos los tipos</option>
                            @foreach($tiposTrabajo as $key => $tipo)
                                <option value="{{ $key }}" {{ $filtroTipo == $key ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group w-100">
                        <button type="submit" class="btn btn-primary btn-sm w-100 mb-1">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.reportes.trabajos-pendientes') }}" class="btn btn-default btn-sm w-100">
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
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $trabajos->count() }}</h3>
                    <p>Total Trabajos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $trabajos->where('tipo_trabajo', 'Conexión Nueva')->count() }}</h3>
                    <p>Conexiones Nuevas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-faucet"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $trabajos->where('tipo_trabajo', 'Corte por Mora')->count() }}</h3>
                    <p>Cortes por Mora</p>
                </div>
                <div class="icon">
                    <i class="fas fa-bolt"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $trabajos->where('tipo_trabajo', 'Reconexión')->count() }}</h3>
                    <p>Reconexiones</p>
                </div>
                <div class="icon">
                    <i class="fas fa-plug"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Trabajos -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                📋 Lista de Trabajos Pendientes
                <small class="text-muted">({{ $trabajos->count() }} registros)</small>
            </h3>
        </div>
        <div class="card-body p-0">
            @if($trabajos->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover table-print mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="12%">Código</th>
                            <th width="23%">Cliente</th>
                            <th width="20%">Dirección</th>
                            <th width="13%" class="text-center">Barrio</th>
                            <th width="12%" class="text-center">Tipo Trabajo</th>

                            <th width="10%" class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trabajos as $trabajo)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $trabajo['codigo_cliente'] }}</strong>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $trabajo['cliente'] }}</div>
                            </td>
                            <td>
                                <div class="text-primary">{{ $trabajo['direccion'] }}</div>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $trabajo['barrio'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($trabajo['tipo_trabajo'] == 'Conexión Nueva')
                                    <span class="badge badge-success">{{ $trabajo['tipo_trabajo'] }}</span>
                                @elseif($trabajo['tipo_trabajo'] == 'Corte por Mora')
                                    <span class="badge badge-danger">{{ $trabajo['tipo_trabajo'] }}</span>
                                @else
                                    <span class="badge badge-info">{{ $trabajo['tipo_trabajo'] }}</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                <span class="badge badge-warning">PENDIENTE</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4>¡No hay trabajos pendientes!</h4>
                <p class="text-muted">Todos los trabajos están al día.</p>
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
                        <li><strong>Total trabajos:</strong> {{ $trabajos->count() }}</li>
                        <li><strong>Conexiones nuevas:</strong> {{ $trabajos->where('tipo_trabajo', 'Conexión Nueva')->count() }}</li>
                        <li><strong>Cortes por mora:</strong> {{ $trabajos->where('tipo_trabajo', 'Corte por Mora')->count() }}</li>
                        <li><strong>Reconexiones:</strong> {{ $trabajos->where('tipo_trabajo', 'Reconexión')->count() }}</li>
                        <li><strong>Filtro barrio:</strong> {{ $filtroBarrio ?: 'Todos' }}</li>
                        <li><strong>Filtro tipo:</strong> {{ $filtroTipo ? $tiposTrabajo[$filtroTipo] : 'Todos' }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>🎯 Leyenda de Estados:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-success">CONEXIÓN NUEVA</span>
                        <span class="badge badge-danger">CORTE POR MORA</span>
                        <span class="badge badge-info">RECONEXIÓN</span>
                        <span class="badge badge-danger">7+ días</span>
                        <span class="badge badge-warning">4-7 días</span>
                        <span class="badge badge-secondary">1-3 días</span>
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
@stop