@extends('layouts.admin-ultralight')
@section('title', 'Detalles de Multa - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>
            <i class="fas fa-eye text-info"></i>
            Detalles de Multa #{{ $multa->id }}
        </h1>
        <div>
            @if($multa->activa && $multa->estado == 'pendiente')
                <a href="{{ route('admin.multas.edit', $multa) }}" class="btn btn-warning btn-sm">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endif
            <a href="{{ route('admin.multas.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver al Listado
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <!-- Información Principal -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i>
                        Información General de la Multa
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="40%">ID de Multa:</th>
                                    <td>
                                        <strong>#{{ $multa->id }}</strong>
                                        @if($multa->aplicada_automaticamente)
                                            <span class="badge badge-info ml-2">
                                                <i class="fas fa-robot"></i> Automática
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        <span class="badge badge-{{ $multa->color_estado }}">
                                            {{ ucfirst($multa->estado) }}
                                        </span>
                                        @if(!$multa->activa)
                                            <span class="badge badge-secondary ml-2">Archivada</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tipo de Multa:</th>
                                    <td>
                                        <strong>{{ $multa->nombre_tipo }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Nombre:</th>
                                    <td>{{ $multa->nombre }}</td>
                                </tr>
                                <tr>
                                    <th>Monto:</th>
                                    <td>
                                        <h4 class="text-success font-weight-bold">
                                            Bs. {{ number_format($multa->monto, 2) }}
                                        </h4>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="40%">Fecha Aplicación:</th>
                                    <td>{{ $multa->fecha_aplicacion->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Creada por:</th>
                                    <td>{{ $multa->usuario->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Fecha Creación:</th>
                                    <td>{{ $multa->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <th>Última Actualización:</th>
                                    <td>{{ $multa->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                @if($multa->deleted_at)
                                    <tr>
                                        <th>Fecha Archivado:</th>
                                        <td>{{ $multa->deleted_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="form-group mt-3">
                        <label><strong>Descripción Detallada:</strong></label>
                        <div class="border p-3 bg-light rounded">
                            {!! nl2br(e($multa->descripcion)) !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Propiedad -->
            <div class="card card-outline card-primary mt-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-home"></i>
                        Información de la Propiedad
                    </h3>
                </div>
                <div class="card-body">
                    @if($multa->propiedad)
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered table-sm">
                                    <tr>
                                        <th width="40%">Referencia:</th>
                                        <td>
                                            <strong>{{ $multa->propiedad->referencia }}</strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Cliente:</th>
                                        <td>{{ $multa->propiedad->client->nombre ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Cédula:</th>
                                        <td>{{ $multa->propiedad->client->ci ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Teléfono:</th>
                                        <td>{{ $multa->propiedad->client->telefono ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-bordered table-sm">
                                    <tr>
                                        <th width="40%">Barrio:</th>
                                        <td>{{ $multa->propiedad->barrio ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Estado Propiedad:</th>
                                        <td>
                                            <span class="badge badge-{{ $multa->propiedad->estado == 'activo' ? 'success' : 'danger' }}">
                                                {{ ucfirst($multa->propiedad->estado) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Tarifa:</th>
                                        <td>
                                            {{ $multa->propiedad->tariff->nombre ?? 'N/A' }}
                                            - Bs. {{ number_format($multa->propiedad->tariff->precio_mensual ?? 0, 2) }}
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning text-center">
                            <i class="fas fa-exclamation-triangle"></i>
                            No hay información de propiedad asociada a esta multa.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Información de Deuda (si existe) -->
            @if($multa->deuda)
            <div class="card card-outline card-warning mt-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Deuda Asociada
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="40%">ID Deuda:</th>
                                    <td>#{{ $multa->deuda->id }}</td>
                                </tr>
                                <tr>
                                    <th>Monto Pendiente:</th>
                                    <td>
                                        <strong class="text-danger">
                                            Bs. {{ number_format($multa->deuda->monto_pendiente, 2) }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fecha Emisión:</th>
                                    <td>{{ $multa->deuda->fecha_emision->format('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="40%">Estado Deuda:</th>
                                    <td>
                                        <span class="badge badge-{{ $multa->deuda->estado == 'pendiente' ? 'warning' : 'success' }}">
                                            {{ ucfirst($multa->deuda->estado) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Fecha Vencimiento:</th>
                                    <td>
                                        @if($multa->deuda->fecha_vencimiento)
                                            {{ $multa->deuda->fecha_vencimiento->format('d/m/Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Panel de Acciones -->
        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i>
                        Acciones Disponibles
                    </h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <!-- Acción según estado -->
                        @if($multa->estado == 'pendiente')
                            <form action="{{ route('admin.multas.marcar-pagada', $multa) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg btn-block" 
                                        onclick="return confirm('¿Marcar esta multa como pagada?')">
                                    <i class="fas fa-check-circle"></i> Marcar como Pagada
                                </button>
                            </form>
                            
                            <a href="{{ route('admin.multas.edit', $multa) }}" 
                               class="btn btn-warning btn-lg btn-block">
                                <i class="fas fa-edit"></i> Editar Multa
                            </a>
                            
                            <form action="{{ route('admin.multas.anular', $multa) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-lg btn-block" 
                                        onclick="return confirm('¿ANULAR esta multa? Esta acción no se puede deshacer.')">
                                    <i class="fas fa-ban"></i> Anular Multa
                                </button>
                            </form>
                        @elseif($multa->estado == 'pagada')
                            <div class="alert alert-success text-center">
                                <i class="fas fa-check-circle fa-2x"></i><br>
                                <strong>Multa Pagada</strong><br>
                                <small>Esta multa ya ha sido marcada como pagada</small>
                            </div>
                        @elseif($multa->estado == 'anulada')
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-ban fa-2x"></i><br>
                                <strong>Multa Anulada</strong><br>
                                <small>Esta multa ha sido anulada</small>
                            </div>
                        @endif

                        <!-- Archivado/Restauración -->
                        @if($multa->activa)
                            <form action="{{ route('admin.multas.destroy', $multa) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary btn-block" 
                                        onclick="return confirm('¿Archivar esta multa?')">
                                    <i class="fas fa-archive"></i> Archivar Multa
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.multas.restaurar', $multa) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-block" 
                                        onclick="return confirm('¿Restaurar esta multa?')">
                                    <i class="fas fa-undo"></i> Restaurar Multa
                                </button>
                            </form>
                        @endif

                        <!-- Volver al listado -->
                        <a href="{{ route('admin.multas.index') }}" class="btn btn-outline-secondary btn-block">
                            <i class="fas fa-list"></i> Volver al Listado
                        </a>
                    </div>
                </div>
            </div>

            <!-- Información Rápida -->
            <div class="card card-outline card-info mt-4">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        Resumen de Estados
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <strong>Creada:</strong><br>
                            {{ $multa->created_at->format('d/m/Y H:i') }}
                        </li>
                        <li class="mb-2">
                            <strong>Última modificación:</strong><br>
                            {{ $multa->updated_at->format('d/m/Y H:i') }}
                        </li>
                        @if($multa->deleted_at)
                        <li class="mb-2">
                            <strong>Archivada:</strong><br>
                            {{ $multa->deleted_at->format('d/m/Y H:i') }}
                        </li>
                        @endif
                        @if($multa->aplicada_automaticamente)
                        <li class="mb-2">
                            <strong class="text-info">Aplicada automáticamente</strong>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-outline {
            border-top: 3px solid;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .btn-block {
            margin-bottom: 10px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Confirmación para acciones importantes
            $('form[action*="anular"]').on('submit', function(e) {
                if (!confirm('¿ESTÁ ABSOLUTAMENTE SEGURO de anular esta multa? Esta acción es irreversible.')) {
                    e.preventDefault();
                }
            });
        });
    </script>
@stop