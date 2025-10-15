@extends('adminlte::page')

@section('title', 'Pagos Registrados')

@section('content_header')
    <h1 class="h5 font-weight-bold mb-0">Pagos Registrados</h1>
    <small class="text-muted">Historial de pagos de agua - Sistema de múltiples meses</small>
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

    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                <div class="mb-2 mb-md-0">
                    <a class="btn btn-primary btn-sm mb-1" href="{{ route('admin.pagos.create') }}">
                        <i class="fas fa-plus-circle mr-1"></i>Nuevo Pago
                    </a>
                    <button class="btn btn-outline-info btn-sm mb-1" onclick="mostrarEstadisticas()">
                        <i class="fas fa-chart-bar mr-1"></i>Estadísticas
                    </button>
                </div>
                
                <!-- Filtros Responsivos -->
                <form action="{{ route('admin.pagos.index') }}" method="GET" class="w-100 w-md-auto">
                    <div class="row g-2">
                        <div class="col-12 col-sm-6 col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm" 
                                   placeholder="Buscar..." value="{{ request('search') }}">
                        </div>
                        <div class="col-6 col-sm-3 col-md-2">
                            <select name="mes" class="form-control form-control-sm">
                                <option value="">Todos los meses</option>
                                @php
                                    $meses = [];
                                    for ($i = -12; $i <= 3; $i++) {
                                        $fecha = now()->addMonths($i);
                                        $meses[$fecha->format('Y-m')] = $fecha->format('M Y');
                                    }
                                @endphp
                                @foreach($meses as $valor => $texto)
                                    <option value="{{ $valor }}" {{ request('mes') == $valor ? 'selected' : '' }}>
                                        {{ $texto }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-sm-3 col-md-2">
                            <select name="metodo" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                <option value="efectivo" {{ request('metodo') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="transferencia" {{ request('metodo') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="qr" {{ request('metodo') == 'qr' ? 'selected' : '' }}>QR</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2">
                            <input type="date" name="fecha_desde" class="form-control form-control-sm" 
                                   value="{{ request('fecha_desde') }}" placeholder="Desde">
                        </div>
                        <div class="col-12 col-sm-6 col-md-2">
                            <div class="input-group input-group-sm">
                                <input type="date" name="fecha_hasta" class="form-control form-control-sm" 
                                       value="{{ request('fecha_hasta') }}" placeholder="Hasta">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-primary" type="submit" title="Buscar">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    @if(request()->anyFilled(['search', 'mes', 'metodo', 'fecha_desde', 'fecha_hasta']))
                                        <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-danger" title="Limpiar">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Filtro adicional por código cliente -->
            <form action="{{ route('admin.pagos.index') }}" method="GET" class="mt-2">
                <div class="form-row align-items-center">
                    <div class="col-auto">
                        <label for="codigo_cliente" class="col-form-label col-form-label-sm">Filtrar por código cliente:</label>
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
                            <a href="{{ route('admin.pagos.index') }}" class="btn btn-sm btn-outline-secondary ml-1">
                                <i class="fas fa-times mr-1"></i>Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            {{-- RESUMEN DE FILTROS APLICADOS --}}
            @if(request()->anyFilled(['search', 'mes', 'metodo', 'fecha_desde', 'fecha_hasta', 'codigo_cliente']))
                <div class="alert alert-info mb-0 mx-3 mt-3">
                    <i class="fas fa-filter mr-1"></i>
                    <strong>Filtros aplicados:</strong>
                    @if(request('search'))
                        <span class="badge badge-light mr-1 mb-1">Búsqueda: "{{ request('search') }}"</span>
                    @endif
                    @if(request('codigo_cliente'))
                        <span class="badge badge-light mr-1 mb-1">Código: {{ request('codigo_cliente') }}</span>
                    @endif
                    @if(request('mes'))
                        <span class="badge badge-light mr-1 mb-1">Mes: {{ \Carbon\Carbon::parse(request('mes'))->format('F Y') }}</span>
                    @endif
                    @if(request('metodo'))
                        <span class="badge badge-light mr-1 mb-1">Método: {{ ucfirst(request('metodo')) }}</span>
                    @endif
                    @if(request('fecha_desde') || request('fecha_hasta'))
                        <span class="badge badge-light mr-1 mb-1">
                            Fecha: 
                            {{ request('fecha_desde') ? \Carbon\Carbon::parse(request('fecha_desde'))->format('d/m/Y') : 'Inicio' }}
                            -
                            {{ request('fecha_hasta') ? \Carbon\Carbon::parse(request('fecha_hasta'))->format('d/m/Y') : 'Hoy' }}
                        </span>
                    @endif
                    <a href="{{ route('admin.pagos.index') }}" class="float-right text-dark font-weight-bold">
                        <i class="fas fa-times mr-1"></i>Limpiar
                    </a>
                </div>
            @endif

            {{-- ESTADÍSTICAS RÁPIDAS --}}
            <div class="row mx-3 mt-3" id="estadisticas">
                <div class="col-6 col-sm-3 mb-3">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-success"><i class="fas fa-receipt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">Total Pagos</span>
                            <span class="info-box-number">{{ $pagos->total() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3 mb-3">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-primary"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">Ingreso Total</span>
                            <span class="info-box-number">Bs {{ number_format($pagos->sum('monto'), 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-sm-3 mb-3">
                    <div class="info-box bg-light shadow-sm">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text small">Mes Actual</span>
                            <span class="info-box-number">{{ $pagos->where('mes_pagado', now()->format('Y-m'))->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($pagos->count())
                <!-- Vista Escritorio -->
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="100">Recibo</th>
                                    <th width="120">Código</th>
                                    <th>Cliente / Propiedad</th>
                                    <th width="110">Mes Pagado</th>
                                    <th width="110">Monto</th>
                                    <th width="110">Fecha Pago</th>
                                    <th width="100">Método</th>
                                    <th width="120" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pagos as $pago)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $pago->numero_recibo }}</strong>
                                            @if($pago->comprobante)
                                                <br>
                                                <small class="text-muted">Ref: {{ $pago->comprobante }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-primary">
                                                {{ $pago->propiedad->client->codigo_cliente ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-start">
                                                <div>
                                                    <strong>{{ $pago->propiedad->client->nombre ?? 'N/A' }}</strong>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $pago->propiedad->client->ci ?? 'Sin CI' }} | 
                                                        {{ $pago->propiedad->referencia }}
                                                    </small>
                                                    <br>
                                                    <small class="text-info">
                                                        {{ $pago->propiedad->barrio ?? 'Sin barrio' }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ \Carbon\Carbon::createFromFormat('Y-m', $pago->mes_pagado)->format('M Y') }}
                                            </span>
                                            @if($pago->mes_pagado == now()->format('Y-m'))
                                                <span class="badge badge-success ml-1">Actual</span>
                                            @elseif($pago->mes_pagado > now()->format('Y-m'))
                                                <span class="badge badge-warning ml-1">Futuro</span>
                                            @elseif($pago->mes_pagado < now()->subMonth()->format('Y-m'))
                                                <span class="badge badge-secondary ml-1">Antiguo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-success">Bs {{ number_format($pago->monto, 2) }}</strong>
                                        </td>
                                        <td>
                                            <small>{{ $pago->fecha_pago->format('d/m/Y') }}</small>
                                            <br>
                                            <small class="text-muted">{{ $pago->fecha_pago->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            @if($pago->metodo == 'efectivo')
                                                <span class="badge badge-success">Efectivo</span>
                                            @elseif($pago->metodo == 'transferencia')
                                                <span class="badge badge-primary">Transferencia</span>
                                            @else
                                                <span class="badge badge-secondary">QR</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a class="btn btn-info" href="{{ route('admin.pagos.show', $pago) }}" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a class="btn btn-warning" href="{{ route('admin.pagos.print', $pago) }}" 
                                                   target="_blank" title="Imprimir recibo">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                                @if($pago->fecha_pago->greaterThanOrEqualTo(now()->subDays(30)))
                                                    <button class="btn btn-danger" 
                                                            onclick="confirmAnular({{ $pago->id }}, '{{ $pago->numero_recibo }}')"
                                                            title="Anular pago">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @else
                                                    <button class="btn btn-outline-secondary" disabled
                                                            title="No se puede anular después de 30 días">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @endif
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
                        @foreach ($pagos as $pago)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">{{ $pago->propiedad->client->nombre ?? 'N/A' }}</h6>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <span class="badge badge-primary">
                                                {{ $pago->propiedad->client->codigo_cliente ?? 'N/A' }}
                                            </span>
                                            <span class="badge badge-info">
                                                {{ \Carbon\Carbon::createFromFormat('Y-m', $pago->mes_pagado)->format('M Y') }}
                                            </span>
                                            @if($pago->metodo == 'efectivo')
                                                <span class="badge badge-success">Efectivo</span>
                                            @elseif($pago->metodo == 'transferencia')
                                                <span class="badge badge-primary">Transferencia</span>
                                            @else
                                                <span class="badge badge-secondary">QR</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <strong class="text-success">Bs {{ number_format($pago->monto, 2) }}</strong>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <div class="small">
                                        <strong>Recibo:</strong> {{ $pago->numero_recibo }}
                                    </div>
                                    <div class="small text-muted">
                                        <strong>Propiedad:</strong> {{ $pago->propiedad->referencia }}
                                    </div>
                                    @if($pago->comprobante)
                                        <div class="small text-muted">
                                            <strong>Comprobante:</strong> {{ $pago->comprobante }}
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="small text-muted mb-2">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $pago->fecha_pago->format('d/m/Y') }}
                                </div>
                                
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('admin.pagos.show', $pago) }}" 
                                       class="btn btn-outline-info btn-sm flex-fill">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                    <a href="{{ route('admin.pagos.print', $pago) }}" 
                                       target="_blank" class="btn btn-outline-warning btn-sm flex-fill">
                                        <i class="fas fa-print mr-1"></i>Imprimir
                                    </a>
                                    @if($pago->fecha_pago->greaterThanOrEqualTo(now()->subDays(30)))
                                        <button class="btn btn-outline-danger btn-sm flex-fill" 
                                                onclick="confirmAnular({{ $pago->id }}, '{{ $pago->numero_recibo }}')">
                                            <i class="fas fa-ban mr-1"></i>Anular
                                        </button>
                                    @else
                                        <button class="btn btn-outline-secondary btn-sm flex-fill" disabled>
                                            <i class="fas fa-ban mr-1"></i>Anular
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">
                        @if(request()->anyFilled(['search', 'mes', 'metodo', 'fecha_desde', 'fecha_hasta', 'codigo_cliente']))
                            No se encontraron pagos con los filtros aplicados
                        @else
                            No hay pagos registrados
                        @endif
                    </h4>
                    @if(!request()->anyFilled(['search', 'mes', 'metodo', 'fecha_desde', 'fecha_hasta', 'codigo_cliente']))
                        <a href="{{ route('admin.pagos.create') }}" class="btn btn-primary btn-sm mt-2">
                            <i class="fas fa-plus-circle mr-1"></i>Registrar Primer Pago
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($pagos->count())
            <div class="card-footer">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="text-muted small mb-2 mb-md-0">
                        Mostrando {{ $pagos->firstItem() }} - {{ $pagos->lastItem() }} de {{ $pagos->total() }} pagos
                    </div>
                    {{ $pagos->links() }}
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
        .badge {
            font-size: 0.75em;
        }
        .info-box {
            min-height: 80px;
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 0.25rem;
        }
        .info-box .info-box-icon {
            width: 60px;
            height: 60px;
            line-height: 60px;
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmAnular(pagoId, numeroRecibo) {
        Swal.fire({
            title: '¿Anular Pago?',
            html: `¿Está seguro de anular el pago: <strong>${numeroRecibo}</strong>?<br>
                   <small class="text-warning">Esta acción no se puede deshacer y afectará los reportes mensuales.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-ban mr-1"></i>Sí, anular',
            cancelButtonText: '<i class="fas fa-times mr-1"></i>Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/pagos/${pagoId}/anular`;
                form.innerHTML = `
                    @csrf
                    @method('PUT')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function mostrarEstadisticas() {
        Swal.fire({
            title: 'Estadísticas de Pagos',
            html: `
                <div class="text-left">
                    <p><strong>Total General:</strong> {{ $pagos->total() }} pagos</p>
                    <p><strong>Ingreso Total:</strong> Bs {{ number_format($pagos->sum('monto'), 2) }}</p>
                    <p><strong>Promedio por Pago:</strong> Bs {{ number_format($pagos->avg('monto'), 2) }}</p>
                    <hr>
                    <p><strong>Métodos de Pago:</strong></p>
                    <p>• Efectivo: {{ $pagos->where('metodo', 'efectivo')->count() }}</p>
                    <p>• Transferencia: {{ $pagos->where('metodo', 'transferencia')->count() }}</p>
                    <p>• QR: {{ $pagos->where('metodo', 'qr')->count() }}</p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Cerrar',
            width: '500px'
        });
    }

    // Auto-ocultar alertas
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
</script>
@stop