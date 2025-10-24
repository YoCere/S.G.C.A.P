@extends('adminlte::page')

@section('title', 'Gestión de Deudas')

@section('content_header')
    <h1 class="h5 font-weight-bold mb-0">Gestión de Deudas</h1>
    <small class="text-muted">Administre las deudas pendientes y pagadas del sistema</small>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success alert-dismissible fade show">
            <strong>{{ session('info') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>{{ session('error') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                <div class="mb-2 mb-md-0">
                    <a href="{{ route('admin.debts.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Nueva Deuda
                    </a>
                </div>
                
                <!-- Filtros Responsivos -->
                <form action="{{ route('admin.debts.index') }}" method="GET" class="w-100 w-md-auto">
                    <div class="row g-2">
                        <div class="col-12 col-sm-6 col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm" 
                                   placeholder="Buscar cliente, propiedad..." value="{{ request('search') }}">
                        </div>
                        <div class="col-6 col-sm-3 col-md-3">
                            <select name="estado" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">Todos los estados</option>
                                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendientes</option>
                                <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagadas</option>
                                <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anuladas</option>
                            </select>
                        </div>
                        <div class="col-6 col-sm-3 col-md-3">
                            <input type="month" name="mes" class="form-control form-control-sm" 
                                   value="{{ request('mes') }}"
                                   onchange="this.form.submit()">
                        </div>
                        <div class="col-12 col-sm-6 col-md-2">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request()->anyFilled(['search', 'estado', 'mes', 'codigo_cliente']))
                                    <a href="{{ route('admin.debts.index') }}" class="btn btn-outline-danger">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Filtro adicional por código cliente -->
            <form action="{{ route('admin.debts.index') }}" method="GET" class="mt-2">
                <div class="form-row align-items-center">
                    <div class="col-auto">
                        <label for="codigo_cliente" class="col-form-label col-form-label-sm">Filtrar por código:</label>
                    </div>
                    <div class="col-auto">
                        <input type="text" name="codigo_cliente" class="form-control form-control-sm" 
                               placeholder="Ej: 48372" value="{{ request('codigo_cliente') }}"
                               style="width: 120px;">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-filter mr-1"></i>Filtrar
                        </button>
                        @if(request('codigo_cliente'))
                            <a href="{{ route('admin.debts.index') }}" class="btn btn-sm btn-outline-secondary ml-1">
                                <i class="fas fa-times mr-1"></i>Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            {{-- RESUMEN DE FILTROS APLICADOS --}}
            @if(request()->anyFilled(['search', 'estado', 'mes', 'codigo_cliente']))
                <div class="alert alert-info mb-0 mx-3 mt-3">
                    <i class="fas fa-filter mr-1"></i>
                    <strong>Filtros aplicados:</strong>
                    @if(request('search'))
                        <span class="badge badge-light mr-1 mb-1">Búsqueda: "{{ request('search') }}"</span>
                    @endif
                    @if(request('codigo_cliente'))
                        <span class="badge badge-light mr-1 mb-1">Código: {{ request('codigo_cliente') }}</span>
                    @endif
                    @if(request('estado'))
                        <span class="badge badge-light mr-1 mb-1">Estado: {{ ucfirst(request('estado')) }}</span>
                    @endif
                    @if(request('mes'))
                        <span class="badge badge-light mr-1 mb-1">Mes: {{ \Carbon\Carbon::parse(request('mes'))->format('m/Y') }}</span>
                    @endif
                    <a href="{{ route('admin.debts.index') }}" class="float-right text-dark font-weight-bold">
                        <i class="fas fa-times mr-1"></i>Limpiar
                    </a>
                </div>
            @endif

            {{-- ESTADÍSTICAS RÁPIDAS --}}
            @php
                $totalDeudas = $debts->total();
                $deudasPendientes = $debts->where('estado', 'pendiente')->count();
                $deudasPagadas = $debts->where('estado', 'pagada')->count();
                $totalMontoPendiente = $debts->where('estado', 'pendiente')->sum('monto_pendiente');
            @endphp

            <div class="row mx-3 mt-3">
                <div class="col-6 col-sm-3 mb-3">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-warning"><i class="fas fa-file-invoice-dollar"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">Total Deudas</span>
                            <span class="info-box-number">{{ $totalDeudas }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3 mb-3">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-danger"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">Pendientes</span>
                            <span class="info-box-number">{{ $deudasPendientes }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3 mb-3">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">Pagadas</span>
                            <span class="info-box-number">{{ $deudasPagadas }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3 mb-3">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-info"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">Monto Pendiente</span>
                            <span class="info-box-number">Bs {{ number_format($totalMontoPendiente, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($debts->count())
                <!-- Vista Escritorio -->
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="80">ID</th>
                                    <th width="120">Código</th>
                                    <th>Cliente / Propiedad</th>
                                    <th width="120">Monto</th>
                                    <th width="120">Emisión</th>
                                    <th width="120">Vencimiento</th>
                                    <th width="100">Estado</th>
                                    <th width="180" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($debts as $debt)
                                <tr>
                                    <td>
                                        <strong>#{{ $debt->id }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-primary">
                                            {{ $debt->propiedad->client->codigo_cliente }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $debt->propiedad->referencia }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $debt->propiedad->client->nombre }}</small>
                                        <br>
                                        <small class="text-info">{{ $debt->propiedad->barrio ?? 'Sin barrio' }}</small>
                                    </td>
                                    <td>
                                        <strong class="text-danger">Bs {{ number_format($debt->monto_pendiente, 2) }}</strong>
                                    </td>
                                    <td>
                                        <small>{{ $debt->fecha_emision->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        @if($debt->fecha_vencimiento)
                                            @php
                                                $isVencida = $debt->fecha_vencimiento->isPast() && $debt->estado == 'pendiente';
                                            @endphp
                                            <span class="{{ $isVencida ? 'text-danger font-weight-bold' : '' }}">
                                                {{ $debt->fecha_vencimiento->format('d/m/Y') }}
                                            </span>
                                            @if($isVencida)
                                                <br><small class="badge badge-danger">VENCIDA</small>
                                            @endif
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($debt->estado == 'anulada')
                                            <span class="badge badge-secondary">Anulada</span>
                                        @elseif($debt->estado == 'pagada')
                                            <span class="badge badge-success">Pagada</span>
                                            @if($debt->pagada_adelantada)
                                                <br><small class="badge badge-info">Adelantada</small>
                                            @endif
                                        @else
                                            <span class="badge badge-warning">Pendiente</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <!-- VER -->
                                            <a href="{{ route('admin.debts.show', $debt) }}" class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <!-- MARCAR COMO PAGADA -->
                                            @if($debt->estado == 'pendiente')
                                                <form action="{{ route('admin.debts.mark-as-paid', $debt) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-success" 
                                                            title="Marcar como pagada"
                                                            onclick="return confirm('¿Marcar esta deuda como pagada?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- ANULAR -->
                                            @if($debt->estado == 'pendiente')
                                                <form action="{{ route('admin.debts.annul', $debt) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button class="btn btn-warning" 
                                                            title="Anular deuda"
                                                            onclick="return confirm('¿Anular esta deuda?')">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- ELIMINAR -->
                                            @can('admin.debts.destroy')
                                            @if($debt->estado == 'pendiente')
                                                <form action="{{ route('admin.debts.destroy', $debt) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger" 
                                                            title="Eliminar deuda"
                                                            onclick="return confirm('¿Eliminar esta deuda?')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Vista Móvil -->
                <div class="d-block d-md-none">
                    <div class="list-group list-group-flush">
                        @foreach ($debts as $debt)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">
                                            {{ $debt->propiedad->referencia }}
                                        </h6>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <span class="badge badge-primary">
                                                {{ $debt->propiedad->client->codigo_cliente }}
                                            </span>
                                            @if($debt->estado == 'anulada')
                                                <span class="badge badge-secondary">Anulada</span>
                                            @elseif($debt->estado == 'pagada')
                                                <span class="badge badge-success">Pagada</span>
                                            @if($debt->pagada_adelantada)
                                                <span class="badge badge-info">Adelantada</span>
                                            @endif
                                            @else
                                                <span class="badge badge-warning">Pendiente</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <strong class="text-danger">Bs {{ number_format($debt->monto_pendiente, 2) }}</strong>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <div class="small">
                                        <strong>Cliente:</strong> {{ $debt->propiedad->client->nombre }}
                                    </div>
                                    <div class="small text-muted">
                                        <strong>Barrio:</strong> {{ $debt->propiedad->barrio ?? 'Sin barrio' }}
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <div class="small">
                                        <strong>Emisión:</strong> {{ $debt->fecha_emision->format('d/m/Y') }}
                                    </div>
                                    @if($debt->fecha_vencimiento)
                                        <div class="small {{ $debt->fecha_vencimiento->isPast() && $debt->estado == 'pendiente' ? 'text-danger font-weight-bold' : '' }}">
                                            <strong>Vencimiento:</strong> {{ $debt->fecha_vencimiento->format('d/m/Y') }}
                                            @if($debt->fecha_vencimiento->isPast() && $debt->estado == 'pendiente')
                                                <span class="badge badge-danger ml-1">VENCIDA</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('admin.debts.show', $debt) }}" 
                                       class="btn btn-outline-info btn-sm flex-fill">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                    @if($debt->estado == 'pendiente')
                                        <form action="{{ route('admin.debts.mark-as-paid', $debt) }}" 
                                              method="POST" class="d-inline flex-fill">
                                            @csrf
                                            <button class="btn btn-outline-success btn-sm w-100"
                                                    onclick="return confirm('¿Marcar como pagada?')">
                                                <i class="fas fa-check mr-1"></i>Pagar
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.debts.annul', $debt) }}" 
                                              method="POST" class="d-inline flex-fill">
                                            @csrf
                                            <button class="btn btn-outline-warning btn-sm w-100"
                                                    onclick="return confirm('¿Anular deuda?')">
                                                <i class="fas fa-ban mr-1"></i>Anular
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">
                        @if(request()->anyFilled(['search', 'estado', 'mes', 'codigo_cliente']))
                            No se encontraron deudas con los filtros aplicados
                        @else
                            No hay deudas registradas
                        @endif
                    </h4>
                    @if(!request()->anyFilled(['search', 'estado', 'mes', 'codigo_cliente']))
                        <a href="{{ route('admin.debts.create') }}" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus mr-1"></i>Registrar Primera Deuda
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($debts->count())
            <div class="card-footer">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="text-muted small mb-2 mb-md-0">
                        Mostrando {{ $debts->firstItem() }} - {{ $debts->lastItem() }} de {{ $debts->total() }} deudas
                    </div>
                    {{ $debts->links() }}
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .table td {
            vertical-align: middle;
        }
        .info-box {
            min-height: 80px;
        }
        .info-box .info-box-icon {
            width: 60px;
            height: 60px;
            line-height: 60px;
        }
        .btn-group-sm > .btn {
            padding: 0.25rem 0.4rem;
        }
        @media (max-width: 768px) {
            .list-group-item {
                padding: 1rem 0.75rem;
            }
            .btn-group .btn {
                font-size: 0.75rem;
            }
            .gap-1 > * {
                margin-right: 0.25rem;
            }
            .gap-1 > *:last-child {
                margin-right: 0;
            }
        }
    </style>
@stop

@section('js')
<script>
    // Auto-ocultar alertas
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);

    // Confirmación para eliminación
    document.addEventListener('DOMContentLoaded', function() {
        const deleteForms = document.querySelectorAll('form[action*="destroy"]');
        deleteForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('¿Está seguro de eliminar esta deuda? Esta acción no se puede deshacer.')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@stop