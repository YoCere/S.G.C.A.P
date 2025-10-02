@extends('adminlte::page')

@section('title', 'Deudas')

@section('content_header')
    <h1>Deudas</h1>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success">{{ session('info') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        {{-- ✅ HEADER CON FILTROS --}}
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <a href="{{ route('admin.debts.create') }}" class="btn btn-primary">
                        Nueva Deuda
                    </a>
                </div>
                <div class="col-md-6">
                    <div class="form-inline float-right">
                        {{-- Filtro por estado --}}
                        <select class="form-control form-control-sm mr-2" onchange="window.location.href = this.value">
                            <option value="{{ route('admin.debts.index') }}">Todos los estados</option>
                            <option value="{{ route('admin.debts.index') }}?estado=pendiente" 
                                    {{ request('estado') == 'pendiente' ? 'selected' : '' }}>
                                Pendientes
                            </option>
                            <option value="{{ route('admin.debts.index') }}?estado=pagada" 
                                    {{ request('estado') == 'pagada' ? 'selected' : '' }}>
                                Pagadas
                            </option>
                            <option value="{{ route('admin.debts.index') }}?estado=anulada" 
                                    {{ request('estado') == 'anulada' ? 'selected' : '' }}>
                                Anuladas
                            </option>
                        </select>
                        
                        {{-- Filtro por mes --}}
                        <input type="month" class="form-control form-control-sm" 
                               value="{{ request('mes') }}"
                               onchange="window.location.href = '{{ route('admin.debts.index') }}?mes=' + this.value"
                               title="Filtrar por mes">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            @if(request('estado') || request('mes'))
                <div class="alert alert-info mb-3">
                    <i class="fas fa-filter mr-1"></i>
                    Filtros aplicados:
                    @if(request('estado'))
                        <span class="badge badge-light mr-2">Estado: {{ request('estado') }}</span>
                    @endif
                    @if(request('mes'))
                        <span class="badge badge-light">Mes: {{ \Carbon\Carbon::parse(request('mes'))->format('m/Y') }}</span>
                    @endif
                    <a href="{{ route('admin.debts.index') }}" class="float-right text-dark">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            @endif

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Propiedad</th>
                        <th>Monto</th>
                        <th>Emisión</th>
                        <th>Vencimiento</th>
                        <th>Estado</th>
                        <th width="200">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($debts as $debt)
                    <tr>
                        <td>{{ $debt->id }}</td>
                        <td>
                            <strong>{{ $debt->propiedad->referencia }}</strong>
                            <br>
                            <small class="text-muted">{{ $debt->propiedad->client->nombre }}</small>
                        </td>
                        <td>Bs {{ number_format($debt->monto_pendiente, 2) }}</td>
                        <td>{{ $debt->fecha_emision->format('d/m/Y') }}</td>
                        <td>
                            @if($debt->fecha_vencimiento)
                                {{-- ✅ INDICADOR VISUAL DE VENCIMIENTO --}}
                                <span class="{{ $debt->fecha_vencimiento->isPast() && $debt->estado == 'pendiente' ? 'text-danger font-weight-bold' : '' }}">
                                    {{ $debt->fecha_vencimiento->format('d/m/Y') }}
                                </span>
                                @if($debt->fecha_vencimiento->isPast() && $debt->estado == 'pendiente')
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
                            {{-- VER --}}
                            <a href="{{ route('admin.debts.show', $debt) }}" class="btn btn-info btn-sm">
                                Ver
                            </a>

                            {{-- MARCAR COMO PAGADA --}}
                            @if($debt->estado == 'pendiente')
                                <form action="{{ route('admin.debts.mark-as-paid', $debt) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-success btn-sm" 
                                            onclick="return confirm('¿Marcar esta deuda como pagada?')">
                                        Pagar
                                    </button>
                                </form>
                            @endif

                            {{-- ANULAR --}}
                            @if($debt->estado == 'pendiente')
                                <form action="{{ route('admin.debts.annul', $debt) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-warning btn-sm" 
                                            onclick="return confirm('¿Anular esta deuda?')">
                                        Anular
                                    </button>
                                </form>
                            @endif

                            {{-- ELIMINAR --}}
                            @if($debt->estado == 'pendiente')
                                <form action="{{ route('admin.debts.destroy', $debt) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm" 
                                            onclick="return confirm('¿Eliminar esta deuda?')">
                                        Eliminar
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $debts->links() }}
        </div>
    </div>
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