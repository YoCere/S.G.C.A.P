@extends('adminlte::page')

@section('title', 'Reporte de Morosidad')

@section('content_header')
    <h1 class="h5 font-weight-bold">Reporte de Morosidad</h1>
    <small class="text-muted">Clientes y propiedades con deudas pendientes</small>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="mb-0">Filtros del Reporte</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.reportes.morosidad') }}" method="GET" class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="estado" class="font-weight-bold">Estado de Propiedad</label>
                        <select name="estado" id="estado" class="form-control">
                            <option value="todos" {{ request('estado') == 'todos' ? 'selected' : '' }}>Todos los estados</option>
                            <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activas</option>
                            <option value="corte_pendiente" {{ request('estado') == 'corte_pendiente' ? 'selected' : '' }}>Corte Pendiente</option>
                            <option value="cortado" {{ request('estado') == 'cortado' ? 'selected' : '' }}>Cortadas</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="meses" class="font-weight-bold">Mínimo meses en mora</label>
                        <select name="meses" id="meses" class="form-control">
                            <option value="1" {{ request('meses') == 1 ? 'selected' : '' }}>1+ mes</option>
                            <option value="2" {{ request('meses') == 2 ? 'selected' : '' }}>2+ meses</option>
                            <option value="3" {{ request('meses') == 3 ? 'selected' : '' }}>3+ meses</option>
                            <option value="6" {{ request('meses') == 6 ? 'selected' : '' }}>6+ meses</option>
                            <option value="12" {{ request('meses') == 12 ? 'selected' : '' }}>12+ meses</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <div class="form-group w-100">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fas fa-filter mr-1"></i>Aplicar Filtros
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Clientes Morosos</span>
                    <span class="info-box-number">{{ $estadisticas['total_clientes_moros'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-home"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Propiedades Morosas</span>
                    <span class="info-box-number">{{ $estadisticas['total_propiedades_moras'] }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-danger">
                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Deuda Total</span>
                    <span class="info-box-number">Bs. {{ number_format($estadisticas['deuda_total'], 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-secondary">
                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Promedio Mora</span>
                    <span class="info-box-number">{{ number_format($estadisticas['promedio_meses_mora'], 1) }} meses</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Detalle de Morosidad</h6>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print mr-1"></i>Imprimir
                    </button>
                    <a href="{{ route('admin.reportes.morosidad', array_merge(request()->all(), ['export' => 'pdf'])) }}" 
                       class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-file-pdf mr-1"></i>PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            @if($propiedades->count())
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="120">Código</th>
                                <th>Cliente</th>
                                <th>Propiedad</th>
                                <th width="100" class="text-center">Meses Mora</th>
                                <th width="100" class="text-center">Deudas</th>
                                <th width="120" class="text-center">Deuda Total</th>
                                <th width="120" class="text-center">Último Pago</th>
                                <th width="100" class="text-center">Estado</th>
                                <th width="80" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($propiedades as $item)
    <tr>
        <td>
            <span class="badge badge-primary font-weight-bold">
                {{ $item['cliente']->codigo_cliente }}
            </span>
        </td>
        <td>
            <strong>{{ $item['cliente']->nombre }}</strong>
            <br>
            <small class="text-muted">CI: {{ $item['cliente']->ci }}</small>
        </td>
        <td>
            {{ $item['propiedad']->referencia }}
            <br>
            <small class="text-muted">{{ $item['propiedad']->barrio }}</small>
        </td>
        <td class="text-center">
            <span class="badge badge-{{ $item['meses_mora'] >= 6 ? 'danger' : 'warning' }} badge-pill">
                {{ $item['meses_mora'] }} meses
            </span>
        </td>
        <td class="text-center">
            <small class="text-muted">{{ $item['cantidad_deudas'] }} deudas</small>
        </td>
        <td class="text-center font-weight-bold text-danger">
            Bs. {{ number_format($item['total_deuda'], 2) }}
        </td>
        <td class="text-center">
            <small class="text-muted">{{ $item['ultimo_mes_pagado'] }}</small>
        </td>
        <td class="text-center">
            @php
                $estadoBadge = [
                    'activo' => 'success',
                    'corte_pendiente' => 'warning', 
                    'cortado' => 'danger'
                ][$item['estado_actual']] ?? 'secondary';
            @endphp
            <span class="badge badge-{{ $estadoBadge }}">
                {{ ucfirst(str_replace('_', ' ', $item['estado_actual'])) }}
            </span>
        </td>
        <td class="text-center">
            <a href="{{ route('admin.properties.show', $item['propiedad']->id) }}" 
               class="btn btn-info btn-sm" title="Ver detalles">
                <i class="fas fa-eye"></i>
            </a>
        </td>
    </tr>
@endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4 class="text-muted">No hay propiedades morosas con los filtros aplicados</h4>
                    <p class="text-muted">Todos los clientes están al día con sus pagos</p>
                </div>
            @endif
        </div>

        @if($propiedades->count())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $propiedades->count() }} propiedades morosas
                    </div>
                    <div class="small text-muted">
                        Generado el: {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop
{{-- SECCIÓN DEBUG CORREGIDA --}}
@if(env('APP_DEBUG'))
<div class="card mb-3 border-danger">
    <div class="card-header bg-danger text-white">
        <h6 class="mb-0">🔧 Debug Info</h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Propiedades con deudas en DB:</strong><br>
                @php
                    // ✅ CORREGIDO: Usar el modelo desde el controlador
                    $debugCount = count($propiedades);
                @endphp
                {{ $debugCount }} propiedades encontradas
            </div>
            <div class="col-md-6">
                <strong>Filtros aplicados:</strong><br>
                Estado: {{ $filtroEstado }} | Meses: {{ $filtroMeses }}+
            </div>
        </div>
        
        @if($propiedades->count())
            <div class="mt-3">
                <strong>Primeras 3 propiedades encontradas:</strong>
                <ul>
                    @foreach($propiedades->take(3) as $item)
                        <li>
                            {{ $item['cliente']->nombre }} - 
                            {{ $item['propiedad']->referencia }} - 
                            {{ $item['meses_mora'] }} meses - 
                            Bs. {{ number_format($item['total_deuda'], 2) }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @else
            <div class="mt-3">
                <strong>⚠️ No se encontraron propiedades morosas</strong><br>
                <small class="text-muted">
                    Revisa los filtros o verifica que existan deudas pendientes en la base de datos.
                </small>
            </div>
        @endif
    </div>
</div>
@endif
{{-- FIN SECCIÓN DEBUG --}}
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
            font-size: 0.875rem;
        }
        .info-box .info-box-number {
            display: block;
            margin-top: 0.25rem;
            font-weight: 700;
            font-size: 1.5rem;
        }
    </style>
@stop

@section('js')
    <script>
        // Auto-reload para actualizar datos periódicamente
        setTimeout(function() {
            window.location.reload();
        }, 300000); // 5 minutos

        // Tooltips
        $(function () {
            $('[title]').tooltip();
        });
    </script>
@stop