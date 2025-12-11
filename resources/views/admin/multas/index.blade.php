@extends('adminlte::page')

@section('title', 'Gestión de Multas - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="h5 font-weight-bold mb-0">
            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
            Gestión de Multas
        </h1>
        <a href="{{ route('admin.multas.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus mr-1"></i> Nueva Multa
        </a>
    </div>
    <small class="text-muted">Administre las multas aplicadas a propiedades</small>
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

    <!-- Filtros Responsivos -->
    <div class="card card-outline card-warning mb-4">
        <div class="card-header">
            <h3 class="card-title h6 mb-0">
                <i class="fas fa-filter mr-1"></i>
                Filtros de Búsqueda
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.multas.index') }}" method="GET">
                <div class="row g-2">
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="estado" class="form-label small">Estado</label>
                        <select name="estado" id="estado" class="form-control form-control-sm">
                            <option value="">Todos los estados</option>
                            <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                            <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="tipo" class="form-label small">Tipo</label>
                        <select name="tipo" id="tipo" class="form-control form-control-sm">
                            <option value="">Todos los tipos</option>
                            @foreach(App\Models\Fine::obtenerTiposMulta() as $key => $tipo)
                                <option value="{{ $key }}" {{ request('tipo') == $key ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="activa" class="form-label small">Estado Activo</label>
                        <select name="activa" id="activa" class="form-control form-control-sm">
                            <option value="">Todos</option>
                            <option value="1" {{ request('activa') === '1' ? 'selected' : '' }}>Activas</option>
                            <option value="0" {{ request('activa') === '0' ? 'selected' : '' }}>Archivadas</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <label for="codigo_cliente" class="form-label small">Código Cliente</label>
                        <input type="text" name="codigo_cliente" id="codigo_cliente" 
                               class="form-control form-control-sm" 
                               placeholder="Ej: 48372" value="{{ request('codigo_cliente') }}">
                    </div>
                    <div class="col-12">
                        <label for="search" class="form-label small">Búsqueda General</label>
                        <input type="text" name="search" id="search" class="form-control form-control-sm" 
                               placeholder="Nombre, descripción, propiedad..." value="{{ request('search') }}">
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-warning btn-sm">
                            <i class="fas fa-search mr-1"></i> Buscar
                        </button>
                        <a href="{{ route('admin.multas.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-undo mr-1"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- Añade esto en la tarjeta de filtros o crea una nueva sección --}}
<div class="card card-outline card-info mb-3">
    <div class="card-header">
        <h3 class="card-title h6 mb-0">
            <i class="fas fa-cog mr-1"></i>
            Configuración de Multas por Mora
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        @php
            $configMora = \App\Models\ConfigMultaMora::first();
        @endphp
        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-info-circle mr-1"></i>
                        Configuración Actual
                    </h6>
                    <hr class="my-2">
                    <div class="row">
                        <div class="col-6">
                            <strong>Meses de gracia:</strong><br>
                            <span class="badge badge-primary">{{ $configMora->meses_gracia ?? 3 }}</span>
                        </div>
                        <div class="col-6">
                            <strong>Porcentaje multa:</strong><br>
                            <span class="badge badge-warning">{{ $configMora->porcentaje_multa ?? 10 }}%</span>
                        </div>
                    </div>
                    @if($configMora)
                        <div class="mt-2">
                            <strong>Estado:</strong>
                            <span class="badge badge-{{ $configMora->activo ? 'success' : 'secondary' }}">
                                {{ $configMora->activo ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-light">
                    <h6 class="alert-heading">
                        <i class="fas fa-calculator mr-1"></i>
                        Calcular Ejemplo
                    </h6>
                    <form id="calculoMultaForm">
                        <div class="form-group">
                            <label class="small">Monto Base</label>
                            <input type="number" class="form-control form-control-sm" 
                                   id="montoBase" placeholder="Bs 0.00" step="0.01" value="100">
                        </div>
                        <div class="form-group">
                            <label class="small">Meses de Atraso</label>
                            <input type="number" class="form-control form-control-sm" 
                                   id="mesesAtraso" placeholder="Ej: 4" min="0" value="4">
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-info w-100" 
                                onclick="calcularMultaMora()">
                            <i class="fas fa-calculator mr-1"></i> Calcular Multa
                        </button>
                    </form>
                    <div id="resultadoCalculo" class="mt-3" style="display:none;">
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="small">Multa aplicable:</span>
                            <strong class="text-danger" id="multaCalculada">Bs 0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="small">Total a pagar:</span>
                            <strong class="text-success" id="totalCalculado">Bs 0.00</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <a href="{{ route('admin.config-multas-mora.edit') }}" class="btn btn-info btn-sm">
            <i class="fas fa-edit mr-1"></i> Editar Configuración
        </a>
    </div>
    </div>
    <!-- Estadísticas Responsivas -->
    <div class="row mb-4">
        <div class="col-6 col-sm-3 mb-3">
            <div class="info-box bg-gradient-warning shadow-sm">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text small">Total Multas</span>
                    <span class="info-box-number">{{ $multas->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3 mb-3">
            <div class="info-box bg-gradient-danger shadow-sm">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text small">Pendientes</span>
                    <span class="info-box-number">
                        {{ $multas->where('estado', 'pendiente')->count() }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3 mb-3">
            <div class="info-box bg-gradient-success shadow-sm">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text small">Pagadas</span>
                    <span class="info-box-number">
                        {{ $multas->where('estado', 'pagada')->count() }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-6 col-sm-3 mb-3">
            <div class="info-box bg-gradient-secondary shadow-sm">
                <span class="info-box-icon"><i class="fas fa-archive"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text small">Archivadas</span>
                    <span class="info-box-number">
                        {{ $multas->where('activa', false)->count() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Multas - Responsiva -->
    <div class="card shadow-sm">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <h3 class="card-title h6 mb-2 mb-md-0">Lista de Multas</h3>
            <span class="badge badge-light">
                {{ $multas->count() }} de {{ $multas->total() }} registros
            </span>
        </div>
        
        <div class="card-body p-0">
            @if($multas->count() > 0)
                <!-- Vista Escritorio -->
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="80">ID</th>
                                    <th width="120">Código</th>
                                    <th>Cliente / Propiedad</th>
                                    <th>Tipo</th>
                                    <th>Nombre</th>
                                    <th width="110">Monto</th>
                                    <th width="100">Estado</th>
                                    <th width="120">Fecha</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($multas as $multa)
                                    <tr class="{{ !$multa->activa ? 'table-secondary' : '' }}">
                                        <td>
                                            <strong>#{{ $multa->id }}</strong>
                                            @if($multa->aplicada_automaticamente)
                                                <br><small class="text-muted"><i class="fas fa-robot"></i> Auto</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($multa->propiedad && $multa->propiedad->client)
                                                <span class="badge badge-primary">
                                                    {{ $multa->propiedad->client->codigo_cliente }}
                                                </span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($multa->propiedad)
                                                <strong>{{ $multa->propiedad->referencia }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    {{ $multa->propiedad->client->nombre ?? 'N/A' }}
                                                </small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $multa->nombre_tipo }}</span>
                                        </td>
                                        <td>
                                            {{ Str::limit($multa->nombre, 25) }}
                                            @if($multa->descripcion)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($multa->descripcion, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-success">Bs. {{ number_format($multa->monto, 2) }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $multa->color_estado }}">
                                                {{ ucfirst($multa->estado) }}
                                            </span>
                                            @if(!$multa->activa)
                                                <br>
                                                <span class="badge badge-secondary">Archivada</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $multa->fecha_aplicacion->format('d/m/Y') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.multas.show', $multa) }}" 
                                                   class="btn btn-info" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($multa->activa && $multa->estado == 'pendiente')
                                                    <a href="{{ route('admin.multas.edit', $multa) }}" 
                                                       class="btn btn-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('admin.multas.marcar-pagada', $multa) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-success" 
                                                                title="Marcar como pagada">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($multa->activa)
                                                    <form action="{{ route('admin.multas.destroy', $multa) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-secondary" 
                                                                title="Archivar">
                                                            <i class="fas fa-archive"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.multas.restaurar', $multa) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary" 
                                                                title="Restaurar">
                                                            <i class="fas fa-undo"></i>
                                                        </button>
                                                    </form>
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
                        @foreach($multas as $multa)
                            <div class="list-group-item {{ !$multa->activa ? 'bg-light' : '' }}">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">
                                            #{{ $multa->id }} - {{ Str::limit($multa->nombre, 20) }}
                                        </h6>
                                        <div class="d-flex flex-wrap gap-1 mb-2">
                                            <span class="badge badge-{{ $multa->color_estado }}">
                                                {{ ucfirst($multa->estado) }}
                                            </span>
                                            <span class="badge badge-info">{{ $multa->nombre_tipo }}</span>
                                            @if(!$multa->activa)
                                                <span class="badge badge-secondary">Archivada</span>
                                            @endif
                                            @if($multa->aplicada_automaticamente)
                                                <span class="badge badge-dark"><i class="fas fa-robot"></i></span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <strong class="text-success">Bs. {{ number_format($multa->monto, 2) }}</strong>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    @if($multa->propiedad)
                                        <div class="small">
                                            <strong>Propiedad:</strong> {{ $multa->propiedad->referencia }}
                                        </div>
                                        <div class="small text-muted">
                                            <strong>Cliente:</strong> {{ $multa->propiedad->client->nombre ?? 'N/A' }}
                                        </div>
                                        <div class="small">
                                            <span class="badge badge-primary">
                                                Código: {{ $multa->propiedad->client->codigo_cliente ?? 'N/A' }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="small text-muted mb-2">
                                    <i class="fas fa-calendar mr-1"></i>
                                    {{ $multa->fecha_aplicacion->format('d/m/Y') }}
                                </div>
                                
                                @if($multa->descripcion)
                                    <div class="small text-muted mb-2">
                                        {{ Str::limit($multa->descripcion, 50) }}
                                    </div>
                                @endif
                                
                                <div class="btn-group w-100" role="group">
                                    <a href="{{ route('admin.multas.show', $multa) }}" 
                                       class="btn btn-outline-info btn-sm flex-fill">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                    @if($multa->activa && $multa->estado == 'pendiente')
                                        <a href="{{ route('admin.multas.edit', $multa) }}" 
                                           class="btn btn-outline-warning btn-sm flex-fill">
                                            <i class="fas fa-edit mr-1"></i>Editar
                                        </a>
                                        <form action="{{ route('admin.multas.marcar-pagada', $multa) }}" 
                                              method="POST" class="d-inline flex-fill">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                                <i class="fas fa-check mr-1"></i>Pagar
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
                    <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No se encontraron multas</h4>
                    <p class="text-muted mb-3">No hay multas que coincidan con los criterios de búsqueda.</p>
                    <a href="{{ route('admin.multas.create') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus mr-1"></i> Crear Primera Multa
                    </a>
                </div>
            @endif
        </div>
        
        @if($multas->count() > 0)
            <div class="card-footer">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="text-muted small mb-2 mb-md-0">
                        Mostrando {{ $multas->firstItem() ?? 0 }} a {{ $multas->lastItem() ?? 0 }} 
                        de {{ $multas->total() }} registros
                    </div>
                    <div>
                        {{ $multas->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('css')
    <style>
        .info-box {
            min-height: 80px;
        }
        .info-box .info-box-icon {
            width: 60px;
            height: 60px;
            line-height: 60px;
        }
        .info-box .info-box-content {
            padding: 10px;
        }
        .table-responsive {
            min-height: 400px;
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Auto-submit form cuando cambien los selects de filtro
        $('#estado, #tipo, #activa').change(function() {
            $(this).closest('form').submit();
        });

        // Confirmación para acciones importantes
        $('form[action*="anular"]').on('submit', function(e) {
            if (!confirm('¿ESTÁ SEGURO de anular esta multa? Esta acción no se puede deshacer.')) {
                e.preventDefault();
            }
        });

        // Confirmación para archivar
        $('form[action*="destroy"]').on('submit', function(e) {
            if (!confirm('¿Archivar esta multa? Podrá restaurarla después.')) {
                e.preventDefault();
            }
        });
    });

    // Auto-ocultar alertas
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
</script>
@stop