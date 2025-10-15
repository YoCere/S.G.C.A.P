@extends('adminlte::page')

@section('title', 'Cliente: ' . $client->nombre)

@section('content_header')
    <h1>Cliente: {{ $client->nombre }}</h1>
    <small class="text-muted">Detalles completos del cliente y sus propiedades</small>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('info') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información Personal</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th width="140">Código Cliente:</th> {{-- ✅ NUEVO --}}
                            <td>
                                <span class="badge badge-primary font-weight-bold">
                                    {{ $client->codigo_cliente }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Nombre:</th>
                            <td>{{ $client->nombre }}</td>
                        </tr>
                        <tr>
                            <th>CI/NIT:</th>
                            <td>{{ $client->ci ?? 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <th>Teléfono:</th>
                            <td>{{ $client->telefono ?? 'No registrado' }}</td>
                        </tr>
                        <tr>
                            <th>Estado Cuenta:</th>
                            <td>
                                <span class="badge badge-{{ $client->estado_cuenta == 'activo' ? 'success' : 'warning' }}">
                                    {{ ucfirst($client->estado_cuenta) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Fecha Registro:</th>
                            <td>{{ $client->fecha_registro->format('d/m/Y') }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer">
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.clients.edit', $client) }}" class="btn btn-primary">
                            <i class="fas fa-edit mr-1"></i>Editar
                        </a>
                        <a href="{{ route('admin.properties.create') }}?cliente_id={{ $client->id }}" 
                           class="btn btn-success">
                            <i class="fas fa-plus mr-1"></i>Agregar Propiedad
                        </a>
                        <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Resumen Financiero</h3>
                </div>
                <div class="card-body">
                    @php
                        $totalDeudas = 0;
                        $propiedadesConDeuda = 0;
                        
                        if($client->properties) {
                            foreach($client->properties as $propiedad) {
                                if($propiedad->debts) {
                                    $deudasPendientes = $propiedad->debts->where('estado', 'pendiente')
                                        ->where('monto_pendiente', '>', 0); // ✅ SOLO DEUDAS CON MONTO > 0
                                    $totalDeudas += $deudasPendientes->sum('monto_pendiente');
                                    $propiedadesConDeuda += $deudasPendientes->count() > 0 ? 1 : 0;
                                }
                            }
                        }
                    @endphp

                    <div class="text-center">
                        <div class="mb-3">
                            <h4 class="text-{{ $totalDeudas > 0 ? 'danger' : 'success' }}">
                                Bs {{ number_format($totalDeudas, 2) }}
                            </h4>
                            <small class="text-muted">Total Deudas Pendientes</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <h5>{{ $client->properties ? $client->properties->count() : 0 }}</h5>
                                <small class="text-muted">Propiedades</small>
                            </div>
                            <div class="col-6">
                                <h5 class="text-warning">{{ $propiedadesConDeuda }}</h5>
                                <small class="text-muted">Con Deudas</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ✅ NUEVO: ACCIONES RÁPIDAS --}}
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title">Acciones Rápidas</h3>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.debts.index') }}?cliente_id={{ $client->id }}" 
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-file-invoice-dollar mr-1"></i>Ver Todas las Deudas
                        </a>
                        <a href="{{ route('admin.pagos.index') }}?cliente_id={{ $client->id }}" 
                           class="btn btn-info btn-sm">
                            <i class="fas fa-history mr-1"></i>Ver Historial de Pagos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- PROPIEDADES DEL CLIENTE --}}
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Propiedades del Cliente</h3>
            <span class="badge badge-primary">
                {{ $client->properties ? $client->properties->count() : 0 }} propiedades
            </span>
        </div>
        <div class="card-body">
            @if($client->properties && $client->properties->count() > 0)
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Referencia</th>
                                <th>Dirección</th>
                                <th>Barrio</th>
                                <th>Tarifa</th>
                                <th>Estado</th>
                                <th>Deudas Pendientes</th>
                                <th width="180">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($client->properties as $propiedad)
                                @php
                                    $deudasPendientes = $propiedad->debts ? $propiedad->debts->where('estado', 'pendiente') : collect([]);
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $propiedad->referencia }}</strong>
                                    </td>
                                    <td>{{ $propiedad->direccion ?? 'No especificada' }}</td>
                                    <td>
                                        @if($propiedad->barrio)
                                            <span class="badge badge-info">{{ $propiedad->barrio }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $propiedad->tariff->nombre ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">Bs {{ number_format($propiedad->tariff->precio_mensual ?? 0, 2) }}</small>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $propiedad->estado == 'activo' ? 'success' : ($propiedad->estado == 'cortado' ? 'danger' : 'secondary') }}">
                                            {{ ucfirst($propiedad->estado) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($deudasPendientes->count() > 0)
                                            <span class="text-danger font-weight-bold">
                                                Bs {{ number_format($deudasPendientes->sum('monto_pendiente'), 2) }}
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $deudasPendientes->count() }} deuda(s)</small>
                                        @else
                                            <span class="text-success">Al día</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.properties.show', $propiedad) }}" 
                                               class="btn btn-info" title="Ver propiedad">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.debts.index') }}?propiedad_id={{ $propiedad->id }}" 
                                               class="btn btn-warning" title="Ver deudas">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </a>
                                            {{-- BOTÓN DE PAGO RÁPIDO --}}
                                            <a href="{{ route('admin.pagos.create') }}?propiedad_id={{ $propiedad->id }}" 
                                               class="btn btn-success" title="Pagar deuda">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-home fa-2x text-muted mb-3"></i>
                    <p class="text-muted">El cliente no tiene propiedades registradas.</p>
                    <a href="{{ route('admin.properties.create') }}?cliente_id={{ $client->id }}" 
                       class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i>Agregar Primera Propiedad
                    </a>
                </div>
            @endif
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
        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
        }
    </style>
@stop

@section('js')
<script>
    // Auto-ocultar alertas después de 5 segundos
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
</script>
@stop