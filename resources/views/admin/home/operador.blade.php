@extends('layouts.admin-ultralight')

@section('title', 'Dashboard Operador - SGCAF')

@section('content_header')
    <h1>🛠️ Panel de Trabajos - Operador</h1>
@stop

@section('content')
    <div class="row">
        <!-- Tarjeta de Trabajos Pendientes -->
        <div class="col-md-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $trabajosPendientes->count() }}</h3>
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

        <!-- Tarjeta de Propiedades Cortadas -->
        <div class="col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>{{ $propiedadesCortadas }}</h3>
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

    <!-- Lista de Trabajos Pendientes -->
    <div class="card mt-4">
        <div class="card-header bg-warning">
            <h3 class="card-title">📋 Mis Trabajos Pendientes</h3>
        </div>
        <div class="card-body p-0">
            @if($trabajosPendientes->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Dirección</th>
                            <th>Tipo de Trabajo</th>
                            <th>Fecha</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trabajosPendientes as $trabajo)
                        <tr>
                            <td>
                                <strong>{{ $trabajo['cliente'] }}</strong>
                                <br>
                                <small class="text-muted">Cód: {{ $trabajo['codigo'] }}</small>
                            </td>
                            <td>
                                {{ $trabajo['direccion'] }}
                                <br>
                                <small class="text-muted">{{ $trabajo['barrio'] }}</small>
                            </td>
                            <td>
                                <span class="badge badge-{{ $trabajo['color'] }}">
                                    <i class="fas {{ $trabajo['icono'] }} mr-1"></i>
                                    {{ $trabajo['tipo_trabajo'] }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $trabajo['fecha_solicitud'] }}</small>
                            </td>
                            <td>
                                <a href="{{ route('admin.cortes.pendientes') }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-play-circle"></i> Ejecutar
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4>¡Excelente trabajo!</h4>
                <p class="text-muted">No tienes trabajos pendientes en este momento.</p>
            </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .small-box {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
@stop