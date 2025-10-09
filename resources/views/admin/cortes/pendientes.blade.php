@extends('adminlte::page')

@section('title', 'Cortes Pendientes - SGCAF')

@section('content_header')
    <h1 class="h5 font-weight-bold mb-0">
        <i class="fas fa-clock text-warning mr-2"></i>
        Cortes Pendientes
    </h1>
    <small class="text-muted">Propiedades marcadas para corte - Esperando acción del equipo físico</small>
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
        <div class="card-header bg-warning">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                <h3 class="card-title h6 mb-2 mb-md-0 text-white">
                    <i class="fas fa-list mr-1"></i>
                    Lista de Cortes Pendientes
                </h3>
                <span class="badge badge-light">
                    {{ $propiedades->total() }} propiedades
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <!-- Filtros Responsivos -->
            <div class="p-3 border-bottom">
                <form action="{{ route('admin.cortes.pendientes') }}" method="GET">
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
                                <button class="btn btn-warning" type="submit">
                                    <i class="fas fa-search mr-1"></i> Buscar
                                </button>
                                @if(request()->anyFilled(['search', 'barrio', 'codigo_cliente']))
                                    <a href="{{ route('admin.cortes.pendientes') }}" class="btn btn-outline-danger">
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
                                    <th width="120">Monto Total</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($propiedades as $propiedad)
                                    <tr class="table-warning">
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
                                            <span class="badge badge-danger">
                                                {{ $propiedad->debts->count() }} deuda(s)
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-danger">
                                                Bs {{ number_format($propiedad->debts->sum('monto_pendiente'), 2) }}
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <!-- MARCAR COMO CORTADO -->
                                                <form action="{{ route('admin.cortes.marcar-cortado', $propiedad->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success"
                                                            title="Marcar como cortado físicamente"
                                                            onclick="return confirm('¿Confirmar corte físico de esta propiedad? Se aplicará multa automáticamente.')">
                                                        <i class="fas fa-bolt mr-1"></i> Cortar
                                                    </button>
                                                </form>

                                                <!-- CANCELAR CORTE -->
                                                <form action="{{ route('admin.properties.cancel-cut', $propiedad->id) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-secondary"
                                                            title="Cancelar corte pendiente"
                                                            onclick="return confirm('¿Cancelar corte pendiente?')">
                                                        <i class="fas fa-undo mr-1"></i> Cancelar
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
                            <div class="list-group-item border-warning border-left-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">{{ $propiedad->referencia }}</h6>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <span class="badge badge-primary">
                                                {{ $propiedad->client->codigo_cliente }}
                                            </span>
                                            <span class="badge badge-warning">Corte Pendiente</span>
                                            @if($propiedad->barrio)
                                                <span class="badge badge-info">{{ $propiedad->barrio }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-danger">
                                            {{ $propiedad->debts->count() }} deudas
                                        </span>
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
                                    <div class="small text-danger">
                                        <strong>Monto Total:</strong> 
                                        Bs {{ number_format($propiedad->debts->sum('monto_pendiente'), 2) }}
                                    </div>
                                </div>
                                
                                <div class="btn-group w-100" role="group">
                                    <form action="{{ route('admin.cortes.marcar-cortado', $propiedad->id) }}" 
                                          method="POST" class="d-inline flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm w-100"
                                                onclick="return confirm('¿Confirmar corte físico?')">
                                            <i class="fas fa-bolt mr-1"></i> Cortar
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.properties.cancel-cut', $propiedad->id) }}" 
                                          method="POST" class="d-inline flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-secondary btn-sm w-100"
                                                onclick="return confirm('¿Cancelar corte?')">
                                            <i class="fas fa-undo mr-1"></i> Cancelar
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
                    <p class="text-muted">No hay propiedades con corte pendiente en este momento.</p>
                    <small class="text-muted">Todas las propiedades están al día o ya fueron cortadas.</small>
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
    <div class="alert alert-info mt-3">
        <h6><i class="fas fa-info-circle mr-2"></i>Información Importante</h6>
        <ul class="mb-0 small">
            <li>Las propiedades en esta lista están marcadas para corte por el sistema</li>
            <li>Al marcar como "Cortado", se aplicará automáticamente una multa de reconexión</li>
            <li>Puede cancelar el corte si el cliente realiza el pago correspondiente</li>
            <li>El equipo físico debe proceder con el corte real del servicio</li>
        </ul>
    </div>
@stop

@section('css')
    <style>
        .border-left-3 {
            border-left: 3px solid #ffc107 !important;
        }
        .table-warning {
            background-color: #fff3cd !important;
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

    // Confirmación para corte
    document.addEventListener('DOMContentLoaded', function() {
        const cortarForms = document.querySelectorAll('form[action*="marcar-cortado"]');
        cortarForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!confirm('¿CONFIRMAR CORTE FÍSICO?\n\nEsta acción:\n• Marcará la propiedad como cortada\n• Aplicará multa de reconexión automáticamente\n• No se puede deshacer fácilmente')) {
                    e.preventDefault();
                }
            });
        });
    });
</script>
@stop