@extends('adminlte::page')

@section('title', 'Recibo: ' . $pago->numero_recibo)

@section('content_header')
    <h1>Recibo: {{ $pago->numero_recibo }}</h1>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success">
            {{ session('info') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información del Pago</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="140">N° Recibo:</th>
                            <td><strong class="text-primary">{{ $pago->numero_recibo }}</strong></td>
                        </tr>
                        <tr>
                            <th>Cliente:</th>
                            <td>{{ $pago->cliente->nombre }}</td>
                        </tr>
                        <tr>
                            <th>CI/NIT:</th>
                            <td>{{ $pago->cliente->ci ?? 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <th>Propiedad:</th>
                            <td>
                                {{ $pago->propiedad->referencia }}
                                @if($pago->propiedad->barrio)
                                    <br><small class="text-muted">{{ $pago->propiedad->barrio }}</small>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalles del Pago</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="140">Mes Pagado:</th>
                            <td>
                                <span class="badge badge-info">
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $pago->mes_pagado)->format('F Y') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Monto:</th>
                            <td><strong class="text-success">Bs {{ number_format($pago->monto, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th>Fecha de Pago:</th>
                            <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Método:</th>
                            <td>
                                @if($pago->metodo == 'efectivo')
                                    <span class="badge badge-success">Efectivo</span>
                                @elseif($pago->metodo == 'transferencia')
                                    <span class="badge badge-primary">Transferencia</span>
                                @else
                                    <span class="badge badge-secondary">QR</span>
                                @endif
                            </td>
                        </tr>
                        @if($pago->comprobante)
                        <tr>
                            <th>Comprobante:</th>
                            <td><code>{{ $pago->comprobante }}</code></td>
                        </tr>
                        @endif
                        <tr>
                            <th>Registrado por:</th>
                            <td>{{ $pago->registradoPor->name }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($pago->observaciones)
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Observaciones</h3>
        </div>
        <div class="card-body">
            <p class="mb-0">{{ $pago->observaciones }}</p>
        </div>
    </div>
    @endif

    {{-- BOTONES DE ACCIÓN --}}
    <div class="mt-4">
        <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i>Volver al Listado
        </a>
        <a href="{{ route('admin.pagos.print', $pago) }}" target="_blank" class="btn btn-warning">
            <i class="fas fa-print mr-1"></i>Imprimir Recibo
        </a>
        
        @if($pago->fecha_pago->greaterThanOrEqualTo(now()->subDays(30)))
            <form action="{{ route('admin.pagos.anular', $pago) }}" method="POST" class="d-inline">
                @csrf @method('PUT')
                <button type="submit" class="btn btn-danger" 
                        onclick="return confirm('¿Está seguro de anular este pago?')">
                    <i class="fas fa-ban mr-1"></i>Anular Pago
                </button>
            </form>
        @endif
    </div>
@stop

@section('css')
    <style>
        .table th {
            background-color: #f8f9fa;
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }
    </style>
@stop