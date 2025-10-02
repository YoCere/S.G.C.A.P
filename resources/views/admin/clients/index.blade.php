@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <h1>Gestión de Clientes</h1>
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

    <div class="card">
        <div class="card-header">
            <div class="row">
                <div class="col-md-6">
                    <a class="btn btn-primary btn-sm" href="{{ route('admin.clients.create') }}">
                        <i class="fas fa-plus-circle mr-1"></i>Nuevo Cliente
                    </a>
                </div>
                <div class="col-md-6">
                    <form action="{{ route('admin.clients.index') }}" method="GET" class="form-inline float-right">
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Buscar por nombre o CI..." value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                                @if(request('search'))
                                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-danger">
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
            @if($clients->count())
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th width="80">ID</th>
                                <th>Nombre</th>
                                <th>CI/NIT</th>
                                <th>Teléfono</th>
                                <th width="120">Propiedades</th>
                                <th width="150" class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($clients as $client)
                                <tr>
                                    <td class="text-muted">#{{ $client->id }}</td>
                                    <td>
                                        <strong>{{ $client->nombre }}</strong>
                                        <br>
                                        <a href="{{ route('admin.debts.index') }}?cliente_id={{ $client->id }}" 
                                           class="small text-info" title="Ver deudas del cliente">
                                            <i class="fas fa-file-invoice-dollar mr-1"></i>Ver deudas
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
                                        @if($client->telefono)
                                            <i class="fas fa-phone mr-1 text-muted"></i>{{ $client->telefono }}
                                        @else
                                            <span class="text-muted small">No registrado</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {{-- ✅ CORREGIDO: Usar properties en lugar de propiedades_count --}}
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
                                            <button class="btn btn-danger" 
                                                    onclick="confirmDelete({{ $client->id }}, '{{ $client->nombre }}', {{ $client->properties->count() }})"
                                                    title="Eliminar cliente">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">
                        @if(request('search'))
                            No se encontraron clientes para "{{ request('search') }}"
                        @else
                            No hay clientes registrados
                        @endif
                    </h4>
                    @if(!request('search'))
                        <a href="{{ route('admin.clients.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-plus-circle mr-1"></i>Registrar Primer Cliente
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($clients->count())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Mostrando {{ $clients->firstItem() }} - {{ $clients->lastItem() }} de {{ $clients->total() }} clientes
                    </div>
                    {{ $clients->links() }}
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
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(clientId, clientName, propertiesCount) {
        let propertiesText = '';
        if (propertiesCount > 0) {
            propertiesText = `<br><div class="alert alert-warning text-left small mt-2 mb-0">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Advertencia:</strong> Este cliente tiene <strong>${propertiesCount}</strong> propiedad(es) asociada(s).
                Debe eliminar primero las propiedades para poder eliminar el cliente.
            </div>`;
        }

        Swal.fire({
            title: '¿Eliminar Cliente?',
            html: `¿Está seguro de eliminar al cliente: <strong>"${clientName}"</strong>?${propertiesText}`,
            icon: propertiesCount > 0 ? 'error' : 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i>Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times mr-1"></i>Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && propertiesCount === 0) {
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