@extends('adminlte::page')

@section('title', 'Gestión de Multas - SGCAF')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-2">
            <i class="fas fa-exclamation-triangle text-warning"></i>
            Gestión de Multas
        </h1>
        <a href="{{ route('admin.multas.create') }}" class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Nueva Multa
        </a>
    </div>
@stop

@section('content')
    <!-- Filtros -->
    <div class="card card-outline card-warning">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i>
                Filtros de Búsqueda
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.multas.index') }}" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-control form-control-sm">
                        <option value="">Todos los estados</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="pagada" {{ request('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                        <option value="anulada" {{ request('estado') == 'anulada' ? 'selected' : '' }}>Anulada</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo de Multa</label>
                    <select name="tipo" id="tipo" class="form-control form-control-sm">
                        <option value="">Todos los tipos</option>
                        @foreach(App\Models\Fine::obtenerTiposMulta() as $key => $tipo)
                            <option value="{{ $key }}" {{ request('tipo') == $key ? 'selected' : '' }}>
                                {{ $tipo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="activa" class="form-label">Estado Activo</label>
                    <select name="activa" id="activa" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        <option value="1" {{ request('activa') === '1' ? 'selected' : '' }}>Activas</option>
                        <option value="0" {{ request('activa') === '0' ? 'selected' : '' }}>Archivadas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="search" class="form-label">Búsqueda</label>
                    <input type="text" name="search" id="search" class="form-control form-control-sm" 
                           placeholder="Nombre, descripción..." value="{{ request('search') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <a href="{{ route('admin.multas.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-undo"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-gradient-warning">
                <span class="info-box-icon"><i class="fas fa-exclamation-triangle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Multas</span>
                    <span class="info-box-number">{{ $multas->total() }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-gradient-danger">
                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pendientes</span>
                    <span class="info-box-number">
                        {{ $multas->where('estado', 'pendiente')->count() }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-gradient-success">
                <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pagadas</span>
                    <span class="info-box-number">
                        {{ $multas->where('estado', 'pagada')->count() }}
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="info-box bg-gradient-secondary">
                <span class="info-box-icon"><i class="fas fa-archive"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Archivadas</span>
                    <span class="info-box-number">
                        {{ $multas->where('activa', false)->count() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de Multas -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lista de Multas</h3>
            <div class="card-tools">
                <span class="badge badge-light">
                    Mostrando {{ $multas->count() }} de {{ $multas->total() }} registros
                </span>
            </div>
        </div>
        <div class="card-body p-0">
            @if($multas->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>ID</th>
                                <th>Propiedad</th>
                                <th>Tipo</th>
                                <th>Nombre</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($multas as $multa)
                                <tr class="{{ !$multa->activa ? 'table-secondary' : '' }}">
                                    <td>
                                        <strong>#{{ $multa->id }}</strong>
                                        @if($multa->aplicada_automaticamente)
                                            <br><small class="text-muted"><i class="fas fa-robot"></i> Automática</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($multa->propiedad)
                                            <strong>{{ $multa->propiedad->referencia }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $multa->propiedad->cliente->nombre ?? 'N/A' }}</small>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $multa->nombre_tipo }}</span>
                                    </td>
                                    <td>
                                        {{ Str::limit($multa->nombre, 30) }}
                                        @if($multa->descripcion)
                                            <br>
                                            <small class="text-muted">{{ Str::limit($multa->descripcion, 40) }}</small>
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
                                        {{ $multa->fecha_aplicacion->format('d/m/Y') }}
                                        <br>
                                        <small class="text-muted">{{ $multa->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('admin.multas.show', $multa) }}" 
                                               class="btn btn-info" title="Ver detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($multa->activa && $multa->estado == 'pendiente')
                                                <a href="{{ route('admin.multas.edit', $multa) }}" 
                                                   class="btn btn-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($multa->activa && $multa->estado == 'pendiente')
                                                <form action="{{ route('admin.multas.marcar-pagada', $multa) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success" 
                                                            title="Marcar como pagada"
                                                            onclick="return confirm('¿Marcar esta multa como pagada?')">
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
                                                            title="Archivar multa"
                                                            onclick="return confirm('¿Archivar esta multa?')">
                                                        <i class="fas fa-archive"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.multas.restaurar', $multa) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary" 
                                                            title="Restaurar multa"
                                                            onclick="return confirm('¿Restaurar esta multa?')">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            @if($multa->activa && $multa->estado == 'pendiente')
                                                <form action="{{ route('admin.multas.anular', $multa) }}" 
                                                      method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger" 
                                                            title="Anular multa"
                                                            onclick="return confirm('¿ANULAR esta multa? Esta acción no se puede deshacer.')">
                                                        <i class="fas fa-ban"></i>
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
            @else
                <div class="text-center py-5">
                    <i class="fas fa-exclamation-circle fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No se encontraron multas</h4>
                    <p class="text-muted">No hay multas que coincidan con los criterios de búsqueda.</p>
                    <a href="{{ route('admin.multas.create') }}" class="btn btn-warning">
                        <i class="fas fa-plus"></i> Crear Primera Multa
                    </a>
                </div>
            @endif
        </div>
        <div class="card-footer clearfix">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted">
                    Mostrando {{ $multas->firstItem() ?? 0 }} a {{ $multas->lastItem() ?? 0 }} 
                    de {{ $multas->total() }} registros
                </div>
                <div>
                    {{ $multas->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-responsive {
            min-height: 400px;
        }
        .info-box {
            cursor: default;
        }
        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
@stop

@section('js')
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
        });
    </script>
@stop