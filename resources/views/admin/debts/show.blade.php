@extends('layouts.admin-ultralight')

@section('title', 'Deuda #' . $debt->id)

@section('content_header')
    <h1>Detalles de Deuda #{{ $debt->id }}</h1>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success">
            {{ session('info') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de la Propiedad</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="120">Referencia:</th>
                            <td>{{ $debt->propiedad->referencia }}</td>
                        </tr>
                        <tr>
                            <th>Cliente:</th>
                            <td>{{ $debt->propiedad->client->nombre }}</td>
                        </tr>
                        <tr>
                            <th>CI/NIT:</th>
                            <td>{{ $debt->propiedad->client->ci ?? 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <th>Dirección:</th>
                            <td>{{ $debt->propiedad->direccion }}</td>
                        </tr>
                        <tr>
                            <th>Barrio:</th>
                            <td>
                                @if($debt->propiedad->barrio)
                                    <span class="badge badge-info">{{ $debt->propiedad->barrio }}</span>
                                @else
                                    <span class="text-muted">No especificado</span>
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
                    <h3 class="card-title">Detalles de la Deuda</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="140">Tarifa:</th>
                            <td>
                                {{ $debt->propiedad->tariff->nombre }}
                                @if(!$debt->propiedad->tariff->activo)
                                    <span class="badge badge-warning ml-1">INACTIVA</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Monto:</th>
                            <td>
                                <strong class="text-success">Bs {{ number_format($debt->monto_pendiente, 2) }}</strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha Emisión:</th>
                            <td>{{ $debt->fecha_emision->format('d/m/Y') }}</td>
                        </tr>
                        <tr>
                            <th>Fecha Vencimiento:</th>
                            <td>
                                @if($debt->fecha_vencimiento)
                                    {{ $debt->fecha_vencimiento->format('d/m/Y') }}
                                    @if($debt->fecha_vencimiento->isPast() && $debt->estado === 'pendiente')
                                        <span class="badge badge-danger ml-1">VENCIDA</span>
                                    @endif
                                @else
                                    <span class="text-muted">No definida</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                @if(isset($debt->anulada) && $debt->anulada)
                                    <span class="badge badge-secondary">ANULADA</span>
                                @elseif($debt->estado === 'pagada')
                                    <span class="badge badge-success">PAGADA</span>
                                    @if($debt->pagada_adelantada)
                                        <span class="badge badge-info ml-1">ADELANTADA</span>
                                    @endif
                                @elseif($debt->estado === 'vencida')
                                    <span class="badge badge-danger">VENCIDA</span>
                                @else
                                    <span class="badge badge-warning">PENDIENTE</span>
                                @endif
                            </td>
                        </tr>
                        @if(isset($debt->anulada) && $debt->anulada)
                        <tr>
                            <th>Motivo Anulación:</th>
                            <td class="text-muted">
                                {{ $debt->motivo_anulacion ?? 'No especificado' }}
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Sección de acciones --}}
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Acciones</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    {{-- Anular deuda --}}
                    @if($debt->estado === 'pendiente' && (!isset($debt->anulada) || !$debt->anulada))
                        <form action="{{ route('admin.debts.annul', $debt) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-warning" 
                                    onclick="return confirm('¿Está seguro de anular esta deuda?')">
                                <i class="fas fa-ban mr-1"></i>Anular Deuda
                            </button>
                        </form>
                    @endif

                    {{-- Eliminar deuda --}}
                    @if($debt->estado === 'pendiente' && (!isset($debt->anulada) || !$debt->anulada))
                        <form action="{{ route('admin.debts.destroy', $debt) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger" 
                                    onclick="return confirm('¿Está seguro de eliminar esta deuda? Esta acción no se puede deshacer.')">
                                <i class="fas fa-trash mr-1"></i>Eliminar Deuda
                            </button>
                        </form>
                    @endif
                </div>
                <div class="col-md-6 text-right">
                    <a href="{{ route('admin.debts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i>Volver al Listado
                    </a>
                    
                    @if($debt->estado === 'pendiente' && (!isset($debt->anulada) || !$debt->anulada))
                        <a href="{{ route('admin.debts.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus mr-1"></i>Nueva Deuda
                        </a>
                    @endif
                </div>
            </div>

            {{-- Mensajes informativos --}}
            @if($debt->estado !== 'pendiente' || (isset($debt->anulada) && $debt->anulada))
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle mr-1"></i>
                    @if(isset($debt->anulada) && $debt->anulada)
                        Esta deuda está anulada. No se permiten modificaciones.
                    @elseif($debt->estado === 'pagada')
                        Esta deuda está pagada. No se permiten modificaciones.
                    @elseif($debt->estado === 'vencida')
                        Esta deuda está vencida. No se permiten modificaciones.
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- Información adicional --}}
    <div class="card mt-4">
        <div class="card-header">
            <h3 class="card-title">Información del Sistema</h3>
        </div>
        <div class="card-body">
            <table class="table table-sm">
                <tr>
                    <th width="140">Creado en:</th>
                    <td>{{ $debt->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <th>Última actualización:</th>
                    <td>{{ $debt->updated_at->format('d/m/Y H:i') }}</td>
                </tr>
                @if(isset($debt->anulada) && $debt->anulada && $debt->anulado_en)
                <tr>
                    <th>Anulado en:</th>
                    <td>{{ $debt->anulado_en->format('d/m/Y H:i') }}</td>
                </tr>
                @endif
            </table>
        </div>
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

@section('js')
    <script>
        // Auto-ocultar alertas después de 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
@stop