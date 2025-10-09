@extends('adminlte::page')

@section('title', 'Propiedades Cortadas - SGCAF')

@section('content_header')
    <h1 class="h5 font-weight-bold mb-0">
        <i class="fas fa-ban text-danger mr-2"></i>
        Propiedades Cortadas
    </h1>
    <small class="text-muted">Propiedades con servicio suspendido - Esperando pago y reconexión</small>
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

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>{{ session('error') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-danger">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <h3 class="card-title h6 mb-2 mb-md-0 text-white">
                    <i class="fas fa-list mr-1"></i>
                    Lista de Propiedades Cortadas
                </h3>
                <span class="badge badge-light">
                    {{ $propiedades->total() }} propiedades
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <!-- Filtros Responsivos -->
            <div class="p-3 border-bottom">
                <form action="{{ route('admin.cortes.cortadas') }}" method="GET">
                    <div class="row g-2">
                        <div class="col-12 col-sm-6 col-md-4">
                            <input type="text" name="search" class="form-control form-control-sm" 
                                   placeholder="Buscar propiedad, cliente..." value="{{ request('search') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <input type="text" name="codigo_cliente" class="form-control form-control-sm" 
                                   placeholder="Código cliente" value="{{ request('codigo_cliente') }}">
                        </div>
                        <div class="col-12 col-sm-6 col-md-3">
                            <select name="barrio" class="form-control form-control-sm">
                                <option value="">Todos los barrios</option>
                                @foreach($barrios as $barrio)
                                    <option value="{{ $barrio }}" {{ request('barrio') == $barrio ? 'selected' : '' }}>
                                        {{ $barrio }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6 col-md-2">
                            <div class="input-group input-group-sm">
                                <button class="btn btn-danger" type="submit">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                                @if(request()->anyFilled(['search', 'barrio', 'codigo_cliente']))
                                    <a href="{{ route('admin.cortes.cortadas') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            @if($propiedades->count())
                <!-- Vista Escritorio -->
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="100">Código</th>
                                    <th>Cliente / Propiedad</th>
                                    <th width="120">Barrio</th>
                                    <th width="120">Deudas</th>
                                    <th width="120">Multas</th>
                                    <th width="120">Total a Pagar</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($propiedades as $propiedad)
                                    @php
                                        $totalDeudas = $propiedad->debts->sum('monto_pendiente');
                                        $totalMultas = $propiedad->multas->where('estado', 'pendiente')->sum('monto');
                                        $totalPagar = $totalDeudas + $totalMultas;
                                    @endphp
                                    <tr class="table-danger">
                                        <td>
                                            <span class="badge badge-primary">
                                                {{ $propiedad->client->codigo_cliente }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $propiedad->referencia }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $propiedad->client->nombre }}</small>
                                            <br>
                                            <small class="text-info">
                                                <i class="fas fa-id-card mr-1"></i>{{ $propiedad->client->ci ?? 'Sin CI' }}
                                            </small>
                                        </td>
                                        <td>
                                            @if($propiedad->barrio)
                                                <span class="badge badge-info">{{ $propiedad->barrio }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">
                                                {{ $propiedad->debts->count() }} deuda(s)
                                            </span>
                                            <br>
                                            <small class="text-danger">Bs {{ number_format($totalDeudas, 2) }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger">
                                                {{ $propiedad->multas->where('estado', 'pendiente')->count() }} multa(s)
                                            </span>
                                            <br>
                                            <small class="text-danger">Bs {{ number_format($totalMultas, 2) }}</small>
                                        </td>
                                        <td>
                                            <strong class="text-danger">
                                                Bs {{ number_format($totalPagar, 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- PAGAR DEUDA -->
                                                <a href="{{ route('admin.pagos.create') }}?propiedad_id={{ $propiedad->id }}" 
                                                   class="btn btn-success"
                                                   title="Registrar pago para reconexión">
                                                    <i class="fas fa-money-bill-wave mr-1"></i> Pagar
                                                </a>

                                                <!-- RESTAURAR SERVICIO -->
                                                <form action="{{ route('admin.cortes.restaurar-servicio', $propiedad->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary"
                                                            title="Restaurar servicio (solo si no tiene deudas/multas)"
                                                            onclick="return confirm('¿Restaurar servicio? Solo si no tiene deudas pendientes.')">
                                                        <i class="fas fa-plug mr-1"></i> Reconectar
                                                    </button>
                                                </form>
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
                        @foreach($propiedades as $propiedad)
                            @php
                                $totalDeudas = $propiedad->debts->sum('monto_pendiente');
                                $totalMultas = $propiedad->multas->where('estado', 'pendiente')->sum('monto');
                                $totalPagar = $totalDeudas + $totalMultas;
                            @endphp
                            <div class="list-group-item border-danger border-left-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">{{ $propiedad->referencia }}</h6>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <span class="badge badge-primary">
                                                {{ $propiedad->client->codigo_cliente }}
                                            </span>
                                            <span class="badge badge-danger">Cortada</span>
                                            @if($propiedad->barrio)
                                                <span class="badge badge-info">{{ $propiedad->barrio }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <strong class="text-danger">Bs {{ number_format($totalPagar, 2) }}</strong>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <div class="small">
                                        <strong>Cliente:</strong> {{ $propiedad->client->nombre }}
                                    </div>
                                    <div class="small text-muted">
                                        <strong>CI:</strong> {{ $propiedad->client->ci ?? 'No registrado' }}
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    <div class="small">
                                        <span class="badge badge-warning mr-2">
                                            {{ $propiedad->debts->count() }} deudas
                                        </span>
                                        <span class="badge badge-danger">
                                            {{ $propiedad->multas->where('estado', 'pendiente')->count() }} multas
                                        </span>
                                    </div>
                                    <div class="small text-muted">
                                        <strong>Desglose:</strong> 
                                        Deudas: Bs {{ number_format($totalDeudas, 2) }} + 
                                        Multas: Bs {{ number_format($totalMultas, 2) }}
                                    </div>
                                </div>
                                
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('admin.pagos.create') }}?propiedad_id={{ $propiedad->id }}" 
                                       class="btn btn-success btn-sm flex-fill">
                                        <i class="fas fa-money-bill-wave mr-1"></i> Pagar
                                    </a>
                                    <form action="{{ route('admin.cortes.restaurar-servicio', $propiedad->id) }}" 
                                          method="POST" class="d-inline flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm w-100"
                                                onclick="return confirm('¿Restaurar servicio?')">
                                            <i class="fas fa-plug mr-1"></i> Reconectar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4 class="text-success">¡Excelente!</h4>
                    <p class="text-muted">No hay propiedades cortadas en este momento.</p>
                    <small class="text-muted">Todas las propiedades tienen servicio activo.</small>
                </div>
            @endif
        </div>

        @if($propiedades->count())
            <div class="card-footer">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="text-muted small mb-2 mb-md-0">
                        Mostrando {{ $propiedades->firstItem() }} - {{ $propiedades->lastItem() }} 
                        de {{ $propiedades->total() }} propiedades
                    </div>
                    {{ $propiedades->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Información Importante -->
    <div class="alert alert-warning mt-3">
        <h6><i class="fas fa-exclamation-triangle mr-2"></i>Información Importante</h6>
        <ul class="mb-0 small">
            <li>Las propiedades en esta lista tienen el servicio suspendido físicamente</li>
            <li>Para reconectar el servicio, el cliente debe pagar TODAS las deudas y multas pendientes</li>
            <li>Use el botón "Pagar" para registrar los pagos correspondientes</li>
            <li>El botón "Reconectar" solo funciona cuando no hay deudas/multas pendientes</li>
            <li>Después de la reconexión, el equipo físico debe restaurar el servicio</li>
        </ul>
    </div>
@stop

@section('css')
    <style>
        .border-left-3 {
            border-left: 3px solid #dc3545 !important;
        }
        .table-danger {
            background-color: #f8d7da !important;
        }
        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
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

    // Confirmación para reconexión
    document.addEventListener('DOMContentLoaded', function() {
        const reconectarForms = document.querySelectorAll('form[action*="restaurar-servicio"]');
        reconectarForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('¿CONFIRMAR RECONEXIÓN?\n\nVerifique que:\n• No hay deudas pendientes\n• No hay multas pendientes\n• El pago está registrado\n\n¿Continuar?')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@stop