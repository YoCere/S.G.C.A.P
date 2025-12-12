@extends('layouts.admin-ultralight')

@section('title', 'Propiedades Cortadas - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-start flex-column flex-md-row">
        <div>
            <h1 class="h5 font-weight-bold mb-0">
                <i class="fas fa-ban text-danger mr-2"></i>
                Propiedades Cortadas
            </h1>
            <small class="text-muted">Propiedades con servicio suspendido - {{ now()->format('d/m/Y') }}</small>
        </div>
        <div class="mt-2 mt-md-0">
            <a href="{{ route('admin.cortes.pendientes') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-tools mr-1"></i>
                Volver a Trabajos
            </a>
        </div>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>{{ session('success') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-danger text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title h6 mb-0">
                    <i class="fas fa-list mr-1"></i>
                    Lista de Propiedades Cortadas
                </h3>
                <span class="badge badge-light">
                    {{ $propiedades->total() }} propiedades
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            @if($propiedades->count())
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="100">Código</th>
                            <th>Cliente / Propiedad</th>
                            <th width="120">Barrio</th>
                            <th width="120">Deudas</th>
                            <th width="120">Monto</th>
                            <th width="120">Fecha Corte</th>
                            <th width="150" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($propiedades as $propiedad)
                        <tr>
                            <td>
                                <span class="badge badge-primary">
                                    {{ $propiedad->client->codigo_cliente ?? 'N/A' }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $propiedad->referencia }}</strong>
                                <br>
                                <small class="text-muted">{{ $propiedad->client->nombre ?? 'Cliente no asignado' }}</small>
                            </td>
                            <td>
                                @if($propiedad->barrio)
                                    <span class="badge badge-info">{{ $propiedad->barrio }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-danger">
                                    {{ $propiedad->debts->where('estado', 'pendiente')->count() }} deuda(s)
                                </span>
                            </td>
                            <td>
                                <strong class="text-danger">
                                    Bs {{ number_format($propiedad->debts->where('estado', 'pendiente')->sum('monto_pendiente'), 2) }}
                                </strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $propiedad->updated_at->format('d/m/Y') }}
                                </small>
                            </td>
                            <td class="text-center">
                                @can('admin.properties.request-reconnection')
                                <form action="{{ route('admin.properties.request-reconnection', $propiedad->id) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-success btn-sm"
                                            onclick="return confirm('¿Solicitar reconexión para esta propiedad?')">
                                        <i class="fas fa-plug mr-1"></i>
                                        Solicitar Reconexión
                                    </button>
                                </form>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4 class="text-success">¡No hay propiedades cortadas!</h4>
                <p class="text-muted">Todas las propiedades tienen servicio activo.</p>
            </div>
            @endif
        </div>

        @if($propiedades->count())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $propiedades->firstItem() }} - {{ $propiedades->lastItem() }} 
                        de {{ $propiedades->total() }} propiedades
                    </div>
                    {{ $propiedades->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Información -->
    <div class="alert alert-warning mt-3">
        <h6><i class="fas fa-info-circle mr-2"></i>Información sobre Propiedades Cortadas</h6>
        <ul class="mb-0 small">
            <li>Las propiedades aparecen aquí cuando el servicio ha sido cortado físicamente</li>
            <li>Use "Solicitar Reconexión" para programar la reactivación del servicio</li>
            <li>La reconexión requiere que el cliente haya pagado todas las deudas pendientes</li>
            <li>El operador ejecutará la reconexión cuando aparezca en "Trabajos Pendientes"</li>
        </ul>
    </div>
@stop