@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <h1 class="h5 font-weight-bold">Gestión de Clientes</h1>
    <small class="text-muted">Administre la información personal de los clientes</small>
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

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <strong>{{ session('warning') }}</strong>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                <div class="d-flex flex-wrap gap-2 mb-2 mb-md-0">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.clients.create') }}">
                        <i class="fas fa-plus-circle mr-1"></i>Nuevo Cliente
                    </a>
                    
                    {{-- ✅ FILTROS DE ESTADO --}}
                    <div class="btn-group btn-group-sm">
                        <a href="{{ route('admin.clients.index', ['estado' => 'activos']) }}" 
                           class="btn btn-{{ (!request('estado') && !request('search') && !request('codigo_cliente')) || request('estado') == 'activos' ? 'primary' : 'outline-primary' }}">
                            Activos
                        </a>
                        <a href="{{ route('admin.clients.index', ['estado' => 'inactivos']) }}" 
                           class="btn btn-{{ request('estado') == 'inactivos' ? 'warning' : 'outline-warning' }}">
                            Inactivos
                        </a>
                        <a href="{{ route('admin.clients.index', ['estado' => 'todos']) }}" 
                           class="btn btn-{{ request('estado') == 'todos' ? 'secondary' : 'outline-secondary' }}">
                            Todos
                        </a>
                    </div>
                </div>
                
                {{-- BÚSQUEDA PRINCIPAL --}}
                <form action="{{ route('admin.clients.index') }}" method="GET" class="w-100 w-md-auto">
                    <input type="hidden" name="estado" value="{{ request('estado') }}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Buscar por nombre, CI o código..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search') || request('codigo_cliente') || request('estado'))
                                <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-danger">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
            
            {{-- FILTRO POR CÓDIGO --}}
            <form action="{{ route('admin.clients.index') }}" method="GET" class="mt-2">
                <input type="hidden" name="estado" value="{{ request('estado') }}">
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
                            <a href="{{ route('admin.clients.index', ['estado' => request('estado')]) }}" class="btn btn-sm btn-outline-secondary ml-1">
                                <i class="fas fa-times mr-1"></i>Limpiar
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body p-0">
            @if($clients->count())
                {{-- VISTA DE ESCRITORIO --}}
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="80">ID</th>
                                    <th width="120">Código</th>
                                    <th>Nombre</th>
                                    <th>CI/NIT</th>
                                    <th>Estado</th>
                                    <th>Teléfono</th>
                                    <th width="120" class="text-center">Propiedades</th>
                                    <th width="180" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($clients as $client)
                                    <tr>
                                        <td class="text-muted">#{{ $client->id }}</td>
                                        <td>
                                            <span class="badge badge-primary font-weight-bold">
                                                {{ $client->codigo_cliente }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $client->nombre }}</strong>
                                            <br>
                                            <a href="{{ route('admin.clients.show', $client) }}" 
                                               class="small text-info" title="Ver detalles del cliente">
                                                <i class="fas fa-eye mr-1"></i>Ver detalles
                                            </a>
                                        </td>
                                        <td>
                                            @if($client->ci)
                                                <code>{{ $client->ci }}</code>
                                            @else
                                                <span class="text-muted small">No registrado</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $client->estado_color }} badge-pill">
                                                {{ $client->estado_legible }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($client->telefono)
                                                <i class="fas fa-phone mr-1 text-muted"></i>{{ $client->telefono }}
                                            @else
                                                <span class="text-muted small">No registrado</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($client->properties->count() > 0)
                                                <span class="badge badge-info badge-pill">
                                                    {{ $client->properties->count() }}
                                                </span>
                                                <br>
                                                <small class="text-muted">propiedades</small>
                                            @else
                                                <span class="badge badge-secondary">Sin propiedades</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a class="btn btn-info" href="{{ route('admin.clients.show', $client) }}" 
                                                   title="Ver detalles del cliente">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a class="btn btn-primary" href="{{ route('admin.clients.edit', $client) }}" 
                                                   title="Editar cliente">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                {{-- ✅ BOTÓN DE ESTADO --}}
                                                @if($client->estaActivo())
                                                    <button class="btn btn-warning" 
                                                            onclick="confirmDeactivate({{ $client->id }}, '{{ $client->nombre }}', {{ $client->properties->count() }})"
                                                            title="Marcar como inactivo">
                                                        <i class="fas fa-user-slash"></i>
                                                    </button>
                                                @else
                                                    <form action="{{ route('admin.clients.activate', $client) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <button class="btn btn-success" 
                                                                type="submit"
                                                                title="Activar cliente"
                                                                onclick="return confirm('¿Está seguro de activar al cliente: {{ $client->nombre }}?')">
                                                            <i class="fas fa-user-check"></i>
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

                {{-- VISTA MÓVIL --}}
                <div class="d-block d-md-none">
                    <div class="list-group list-group-flush">
                        @foreach ($clients as $client)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">{{ $client->nombre }}</h6>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <span class="badge badge-primary">
                                                {{ $client->codigo_cliente }}
                                            </span>
                                            @if($client->ci)
                                                <small class="text-muted">CI: {{ $client->ci }}</small>
                                            @endif
                                            <span class="badge badge-{{ $client->estado_color }}">
                                                {{ $client->estado_legible }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <span class="badge badge-{{ $client->properties->count() > 0 ? 'info' : 'secondary' }} badge-pill">
                                            {{ $client->properties->count() }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    @if($client->telefono)
                                        <small class="text-muted">
                                            <i class="fas fa-phone mr-1"></i>{{ $client->telefono }}
                                        </small>
                                    @else
                                        <small class="text-muted">Sin teléfono</small>
                                    @endif
                                </div>
                                
                                <div class="btn-group w-100" role="group">
                                    <a class="btn btn-outline-info btn-sm flex-fill" 
                                       href="{{ route('admin.clients.show', $client) }}">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                    <a class="btn btn-outline-primary btn-sm flex-fill" 
                                       href="{{ route('admin.clients.edit', $client) }}">
                                        <i class="fas fa-edit mr-1"></i>Editar
                                    </a>
                                    
                                    {{-- ✅ BOTÓN DE ESTADO MÓVIL --}}
                                    @if($client->estaActivo())
                                        <button class="btn btn-outline-warning btn-sm flex-fill" 
                                                onclick="confirmDeactivate({{ $client->id }}, '{{ $client->nombre }}', {{ $client->properties->count() }})">
                                            <i class="fas fa-user-slash mr-1"></i>Inactivar
                                        </button>
                                    @else
                                        <form action="{{ route('admin.clients.activate', $client) }}" method="POST" class="d-inline flex-fill">
                                            @csrf
                                            @method('PUT')
                                            <button class="btn btn-outline-success btn-sm w-100" 
                                                    type="submit"
                                                    onclick="return confirm('¿Activar cliente {{ $client->nombre }}?')">
                                                <i class="fas fa-user-check mr-1"></i>Activar
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
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">
                        @if(request('search') || request('codigo_cliente'))
                            No se encontraron clientes para "{{ request('search') ?: request('codigo_cliente') }}"
                        @elseif(request('estado') == 'inactivos')
                            No hay clientes inactivos
                        @else
                            No hay clientes registrados
                        @endif
                    </h4>
                    @if(!request('search') && !request('codigo_cliente') && !request('estado'))
                        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus-circle mr-1"></i>Registrar Primer Cliente
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($clients->count())
            <div class="card-footer">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="text-muted small mb-2 mb-md-0">
                        Mostrando {{ $clients->firstItem() }} - {{ $clients->lastItem() }} de {{ $clients->total() }} clientes
                        @if(request('estado') == 'activos')
                            <span class="badge badge-success ml-2">Activos</span>
                        @elseif(request('estado') == 'inactivos')
                            <span class="badge badge-warning ml-2">Inactivos</span>
                        @elseif(request('estado') == 'todos')
                            <span class="badge badge-secondary ml-2">Todos</span>
                        @endif
                    </div>
                    <div>
                        {{ $clients->links() }}
                    </div>
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
        .badge-pill {
            padding: 0.4em 0.6em;
        }
        /* Mejoras para móvil */
        @media (max-width: 768px) {
            .list-group-item {
                padding: 1rem 0.75rem;
            }
            .btn-group .btn {
                font-size: 0.8rem;
            }
            .gap-2 > * {
                margin-right: 0.5rem;
            }
            .gap-2 > *:last-child {
                margin-right: 0;
            }
        }
        /* Utilidades para espaciado */
        .gap-1 > * { margin-right: 0.25rem; }
        .gap-1 > *:last-child { margin-right: 0; }
        .gap-2 > * { margin-right: 0.5rem; }
        .gap-2 > *:last-child { margin-right: 0; }
        
        /* Estilos para botones de estado */
        .btn-group-sm > .btn {
            padding: 0.25rem 0.5rem;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDeactivate(clientId, clientName, propertiesCount) {
        let propertiesText = '';
        let iconType = 'warning';
        
        if (propertiesCount > 0) {
            propertiesText = `<br><div class="alert alert-info text-left small mt-2 mb-0">
                <i class="fas fa-info-circle mr-1"></i>
                <strong>Información:</strong> Este cliente tiene <strong>${propertiesCount}</strong> propiedad(es) asociada(s).
                <br><small>El cliente será marcado como inactivo pero las propiedades se mantendrán en el sistema.</small>
            </div>`;
            iconType = 'info';
        }

        Swal.fire({
            title: '¿Marcar Cliente como Inactivo?',
            html: `¿Está seguro de marcar como inactivo al cliente: <strong>"${clientName}"</strong>?<br>
                  <small class="text-muted">El cliente no podrá realizar nuevas operaciones pero sus datos se preservarán.</small>${propertiesText}`,
            icon: iconType,
            showCancelButton: true,
            confirmButtonColor: '#f39c12',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-user-slash mr-1"></i>Sí, inactivar',
            cancelButtonText: '<i class="fas fa-times mr-1"></i>Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                // ✅ MODIFICADO: Permitir inactivar incluso con propiedades
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/clients/${clientId}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Auto-ocultar alertas
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
</script>
@stop