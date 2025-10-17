@extends('adminlte::page')

@section('title', 'Usuarios del Sistema')

@section('content_header')
    <h1 class="h5 font-weight-bold">Gestión de Usuarios</h1>
    <small class="text-muted">Administre los usuarios y roles del sistema</small>
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
            {{-- BOTÓN NUEVO USUARIO --}}
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-2">
                <a class="btn btn-primary btn-sm mb-2 mb-md-0" href="{{ route('admin.users.create') }}">
                    <i class="fas fa-user-plus mr-1"></i>Nuevo Usuario
                </a>
                
                {{-- BÚSQUEDA PRINCIPAL --}}
                <form action="{{ route('admin.users.index') }}" method="GET" class="w-100 w-md-auto">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Buscar por nombre o email..." value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                            @if(request('search'))
                                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-danger">
                                    <i class="fas fa-times"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card-body p-0">
            @if($users->count())
                {{-- VISTA DE ESCRITORIO --}}
                <div class="d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th width="80">ID</th>
                                    <th>Usuario</th>
                                    <th>Email</th>
                                    <th>Roles</th>
                                    <th width="150" class="text-center">Registro</th>
                                    <th width="150" class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="text-muted">#{{ $user->id }}</td>
                                        <td>
                                            <strong>{{ $user->name }}</strong>
                                            <br>
                                            <a href="{{ route('admin.users.show', $user) }}" 
                                               class="small text-info" title="Ver detalles del usuario">
                                                <i class="fas fa-eye mr-1"></i>Ver detalles
                                            </a>
                                        </td>
                                        <td>
                                            <code>{{ $user->email }}</code>
                                            @if($user->email_verified_at)
                                                <span class="badge badge-success badge-pill ml-1" title="Email verificado">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->roles->count() > 0)
                                                @foreach($user->roles as $role)
                                                    <span class="badge badge-info mr-1">
                                                        {{ $role->name }}
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="badge badge-secondary">Sin roles</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <small class="text-muted">
                                                {{ $user->created_at->format('d/m/Y') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a class="btn btn-info" href="{{ route('admin.users.show', $user) }}" 
                                                   title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a class="btn btn-primary" href="{{ route('admin.users.edit', $user) }}" 
                                                   title="Editar usuario">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-danger" 
                                                        onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}', {{ $user->pagos_count ?? 0 }})"
                                                        title="Eliminar usuario">
                                                    <i class="fas fa-trash"></i>
                                                </button>
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
                        @foreach ($users as $user)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1 font-weight-bold">{{ $user->name }}</h6>
                                        <div class="d-flex flex-wrap gap-2 mb-2">
                                            <small class="text-muted">{{ $user->email }}</small>
                                            @if($user->email_verified_at)
                                                <span class="badge badge-success badge-pill" title="Email verificado">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <small class="text-muted">
                                            {{ $user->created_at->format('d/m/Y') }}
                                        </small>
                                    </div>
                                </div>
                                
                                <div class="mb-2">
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            <span class="badge badge-info mr-1">
                                                {{ $role->name }}
                                            </span>
                                        @endforeach
                                    @else
                                        <span class="badge badge-secondary">Sin roles</span>
                                    @endif
                                </div>
                                
                                <div class="btn-group w-100" role="group">
                                    <a class="btn btn-outline-info btn-sm flex-fill" 
                                       href="{{ route('admin.users.show', $user) }}">
                                        <i class="fas fa-eye mr-1"></i>Ver
                                    </a>
                                    <a class="btn btn-outline-primary btn-sm flex-fill" 
                                       href="{{ route('admin.users.edit', $user) }}">
                                        <i class="fas fa-edit mr-1"></i>Editar
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm flex-fill" 
                                            onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}', {{ $user->pagos_count ?? 0 }})">
                                        <i class="fas fa-trash mr-1"></i>Eliminar
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">
                        @if(request('search'))
                            No se encontraron usuarios para "{{ request('search') }}"
                        @else
                            No hay usuarios registrados
                        @endif
                    </h4>
                    @if(!request('search'))
                        <a href="{{ route('admin.users.create') }}" class="btn btn-primary mt-2">
                            <i class="fas fa-user-plus mr-1"></i>Registrar Primer Usuario
                        </a>
                    @endif
                </div>
            @endif
        </div>

        @if($users->count())
            <div class="card-footer">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                    <div class="text-muted small mb-2 mb-md-0">
                        Mostrando {{ $users->firstItem() }} - {{ $users->lastItem() }} de {{ $users->total() }} usuarios
                    </div>
                    <div>
                        {{ $users->links() }}
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
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(userId, userName, pagosCount) {
        let pagosText = '';
        if (pagosCount > 0) {
            pagosText = `<br><div class="alert alert-warning text-left small mt-2 mb-0">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                <strong>Advertencia:</strong> Este usuario tiene <strong>${pagosCount}</strong> pago(s) registrado(s).
                No se puede eliminar un usuario con registros de pagos.
            </div>`;
        }

        Swal.fire({
            title: '¿Eliminar Usuario?',
            html: `¿Está seguro de eliminar al usuario: <strong>"${userName}"</strong>?${pagosText}`,
            icon: pagosCount > 0 ? 'error' : 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i>Sí, eliminar',
            cancelButtonText: '<i class="fas fa-times mr-1"></i>Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed && pagosCount === 0) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/users/${userId}`;
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