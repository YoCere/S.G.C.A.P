@extends('adminlte::page')

@section('title', 'Pagos Registrados')

@section('content_header')
    <h1>Pagos Registrados</h1>
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

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.pagos.create') }}">
                        <i class="fas fa-plus-circle mr-1"></i>Nuevo Pago
                    </a>
                    <button class="btn btn-outline-info btn-sm" onclick="mostrarEstadisticas()">
                        <i class="fas fa-chart-bar mr-1"></i>Estadísticas
                    </button>
                </div>
                <div class="col-md-6">
                    {{-- FILTROS AVANZADOS --}}
                    <form action="{{ route('admin.pagos.index') }}" method="GET" class="form-inline float-right">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar cliente, propiedad, CI o recibo..." 
                                   value="{{ request('search') }}">
                            
                            {{-- FILTRO POR MES --}}
                            <select name="mes" class="form-control form-control-sm ml-2" style="max-width: 150px;">
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
                            
                            {{-- FILTRO POR MÉTODO --}}
                            <select name="metodo" class="form-control form-control-sm ml-2" style="max-width: 130px;">
                                <option value="">Todos los métodos</option>
                                <option value="efectivo" {{ request('metodo') == 'efectivo' ? 'selected' : '' }}>Efectivo</option>
                                <option value="transferencia" {{ request('metodo') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                                <option value="qr" {{ request('metodo') == 'qr' ? 'selected' : '' }}>QR</option>
                            </select>

                            {{-- FILTRO POR RANGO DE FECHAS --}}
                            <input type="date" name="fecha_desde" class="form-control form-control-sm ml-2" 
                                   placeholder="Desde" value="{{ request('fecha_desde') }}" style="max-width: 140px;">
                            <input type="date" name="fecha_hasta" class="form-control form-control-sm ml-1" 
                                   placeholder="Hasta" value="{{ request('fecha_hasta') }}" style="max-width: 140px;">

                            <div class="input-group-append">
                                <button class="btn btn-outline-primary" type="submit" title="Buscar">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request()->anyFilled(['search', 'mes', 'metodo', 'fecha_desde', 'fecha_hasta']))
                                    <a href="{{ route('admin.pagos.index') }}" class="btn btn-outline-danger" title="Limpiar filtros">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            {{-- RESUMEN DE FILTROS APLICADOS --}}
            @if(request()->anyFilled(['search', 'mes', 'metodo', 'fecha_desde', 'fecha_hasta']))
                <div class="alert alert-info mb-0 mx-3 mt-3">
                    <i class="fas fa-filter mr-1"></i>
                    <strong>Filtros aplicados:</strong>
                    @if(request('search'))
                        <span class="badge badge-light mr-2">Búsqueda: "{{ request('search') }}"</span>
                    @endif
                    @if(request('mes'))
                        <span class="badge badge-light mr-2">Mes: {{ \Carbon\Carbon::parse(request('mes'))->format('F Y') }}</span>
                    @endif
                    @if(request('metodo'))
                        <span class="badge badge-light mr-2">Método: {{ ucfirst(request('metodo')) }}</span>
                    @endif
                    @if(request('fecha_desde') || request('fecha_hasta'))
                        <span class="badge badge-light">
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
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-success"><i class="fas fa-receipt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Pagos</span>
                            <span class="info-box-number">{{ $pagos->total() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-primary"><i class="fas fa-money-bill-wave"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Ingreso Total</span>
                            <span class="info-box-number">Bs {{ number_format($pagos->sum('monto'), 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-warning"><i class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Mes Actual</span>
                            <span class="info-box-number">{{ $pagos->where('mes_pagado', now()->format('Y-m'))->count() }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="info-box bg-light">
                        <span class="info-box-icon bg-info"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Clientes Únicos</span>
                            <span class="info-box-number">{{ $pagos->pluck('cliente_id')->unique()->count() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            @if($pagos->count())
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="100">Recibo</th>
                                <th>Cliente / Propiedad</th>
                                <th>Mes Pagado</th>
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
                                        <div class="d-flex align-items-start">
                                            <div>
                                                <strong>{{ $pago->cliente->nombre }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $pago->cliente->ci ?? 'Sin CI' }} | 
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
                                            {{-- VER DETALLES --}}
                                            <a class="btn btn-info" href="{{ route('admin.pagos.show', $pago) }}" 
                                               title="Ver detalles del pago">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            {{-- IMPRIMIR RECIBO --}}
                                            <a class="btn btn-warning" href="{{ route('admin.pagos.print', $pago) }}" 
                                               target="_blank" title="Imprimir recibo">
                                                <i class="fas fa-print"></i>
                                            </a>

                                            {{-- ANULAR PAGO --}}
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
            @else
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">
                        @if(request()->anyFilled(['search', 'mes', 'metodo', 'fecha_desde', 'fecha_hasta']))
                            No se encontraron pagos con los filtros aplicados
                        @else
                            No hay pagos registrados
                        @endif
                    </h4>
                    @if(!request()->anyFilled(['search', 'mes', 'metodo', 'fecha_desde', 'fecha_hasta']))
                        <a href="{{ route('admin.pagos.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus-circle mr-1"></i>Registrar Primer Pago
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($pagos->count())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
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
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 0.25rem;
        }
        .mes-lote {
            background: #e3f2fd;
            border-left: 3px solid #2196f3;
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
                    <p><strong>Clientes Únicos:</strong> {{ $pagos->pluck('cliente_id')->unique()->count() }}</p>
                    <hr>
                    <p><strong>Métodos de Pago:</strong></p>
                    <p>• Efectivo: {{ $pagos->where('metodo', 'efectivo')->count() }}</p>
                    <p>• Transferencia: {{ $pagos->where('metodo', 'transferencia')->count() }}</p>
                    <p>• QR: {{ $pagos->where('metodo', 'qr')->count() }}</p>
                </div>
            `,
            icon: 'info',
            confirmButtonText: 'Cerrar'
        });
    }

    // Auto-ocultar alertas
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
</script>
@stop