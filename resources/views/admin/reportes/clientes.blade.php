@extends('adminlte::page')

@section('title', 'Reporte de Clientes - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>👥 Reporte de Clientes</h1>
        <div>
            <button onclick="window.print()" class="btn btn-secondary btn-sm no-print">
                <i class="fas fa-print mr-1"></i> Imprimir/PDF
            </button>
            <a href="{{ route('admin.reportes.index') }}" class="btn btn-default btn-sm no-print">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </div>
    <p class="text-muted">Listado completo de clientes - {{ now()->format('d/m/Y H:i') }}</p>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card no-print mb-4">
        <div class="card-header">
            <h3 class="card-title">🔍 Filtros de Búsqueda</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reportes.clientes') }}" class="row">
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
                        <label for="estado">Estado Cliente:</label>
                        <select name="estado" id="estado" class="form-control form-control-sm">
                            <option value="">Todos los estados</option>
                            <option value="activo" {{ $filtroEstado == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ $filtroEstado == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group w-100">
                        <button type="submit" class="btn btn-primary btn-sm w-100 mb-1">
                            <i class="fas fa-filter mr-1"></i> Filtrar
                        </button>
                        <a href="{{ route('admin.reportes.clientes') }}" class="btn btn-default btn-sm w-100">
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
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $clientes->count() }}</h3>
                    <p>Total Clientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $clientes->where('estado_cliente', 'activo')->count() }}</h3>
                    <p>Clientes Activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $clientes->where('estado_cliente', 'inactivo')->count() }}</h3>
                    <p>Clientes Inactivos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-user-times"></i>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $clientes->sum('total_propiedades') }}</h3>
                    <p>Total Propiedades</p>
                </div>
                <div class="icon">
                    <i class="fas fa-home"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Lista de Clientes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                📋 Lista de Clientes
                <small class="text-muted">({{ $clientes->count() }} registros)</small>
            </h3>
        </div>
        <div class="card-body p-0">
            @if($clientes->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm table-hover table-print mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="12%">Código</th>
                            <th width="25%">Nombre Cliente</th>
                            <th width="15%">CI</th>
                            <th width="15%">Teléfono</th>
                            <th width="13%" class="text-center">Barrio</th>
                            <th width="10%" class="text-center">Propiedades</th>
                            <th width="10%" class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientes as $cliente)
                        <tr>
                            <td>
                                <strong class="text-primary">{{ $cliente['codigo'] }}</strong>
                            </td>
                            <td>
                                <div class="font-weight-bold">{{ $cliente['nombre'] }}</div>
                                <small class="text-muted">{{ $cliente['fecha_registro'] }}</small>
                            </td>
                            <td>
                                <span class="text-muted">{{ $cliente['ci'] ?: 'No registrado' }}</span>
                            </td>
                            <td>
                                <span class="text-muted">{{ $cliente['telefono'] ?: 'No registrado' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $cliente['barrio_principal'] }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-info">{{ $cliente['total_propiedades'] }}</span>
                            </td>
                            <td class="text-center">
                                @if($cliente['estado_cliente'] == 'activo')
                                    <span class="badge badge-success">ACTIVO</span>
                                @else
                                    <span class="badge badge-warning">INACTIVO</span>
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
                <h4>No se encontraron clientes</h4>
                <p class="text-muted">No hay clientes que coincidan con los filtros aplicados.</p>
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
                        <li><strong>Total clientes:</strong> {{ $clientes->count() }}</li>
                        <li><strong>Clientes activos:</strong> {{ $clientes->where('estado_cliente', 'activo')->count() }}</li>
                        <li><strong>Clientes inactivos:</strong> {{ $clientes->where('estado_cliente', 'inactivo')->count() }}</li>
                        <li><strong>Filtro barrio:</strong> {{ $filtroBarrio ?: 'Todos' }}</li>
                        <li><strong>Filtro estado:</strong> {{ $filtroEstado ?: 'Todos' }}</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6>🎯 Leyenda de Estados:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge badge-success">ACTIVO</span>
                        <span class="badge badge-warning">INACTIVO</span>
                        <span class="badge badge-info">PROPIEDADES</span>
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
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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