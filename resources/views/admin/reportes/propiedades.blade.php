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
                    <h3>{{ $propiedades->count() }}</h3>
                    <p>Total Propiedades</p>
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
                </div>
                <div class="icon">
                    <i class="fas fa-plug"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $propiedades->where('estado_servicio', 'cortado')->count() }}</h3>
                    <p>Propiedades Cortadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $propiedades->where('trabajo_pendiente', '!=', 'Sin trabajo pendiente')->count() }}</h3>
                    <p>Trabajos Pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
        </div>
    </div>

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
                        <li><strong>Propiedades activas:</strong> {{ $propiedades->where('estado_servicio', 'activo')->count() }}</li>
                        <li><strong>Propiedades cortadas:</strong> {{ $propiedades->where('estado_servicio', 'cortado')->count() }}</li>
                        <li><strong>Filtro barrio:</strong> {{ $filtroBarrio ?: 'Todos' }}</li>
                        <li><strong>Filtro estado:</strong> {{ $filtroEstado ? ucfirst(str_replace('_', ' ', $filtroEstado)) : 'Todos' }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>🎯 Leyenda de Estados:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-success">ACTIVO</span>
                        <span class="badge badge-danger">CORTADO</span>
                        <span class="badge badge-warning">PENDIENTE</span>
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
@stop