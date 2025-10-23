@extends('adminlte::page')

@section('title', 'Gestión de Roles')

@section('content_header')
    <a class="btn btn-secondary float-right" href="{{ route('admin.roles.create') }}">Nuevo Rol</a>
    <h1>Lista de Roles</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>ROL</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th width="150px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>
                                    <strong>{{ $role->name }}</strong>
                                </td>
                                <td>
                                    @if(in_array($role->name, ['Admin', 'Secretaria', 'Operador']))
                                        <span class="badge badge-success">Sistema</span>
                                    @else
                                        <span class="badge badge-secondary">Personalizado</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $role->activo ? 'success' : 'warning' }}">
                                        {{ $role->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        @can('admin.users.edit')
                                            <a href="{{ route('admin.roles.edit', $role) }}" 
                                               class="btn btn-warning" 
                                               title="Editar Rol">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan
                                        
                                        @can('admin.users.destroy')
                                            @if(!in_array($role->name, ['Admin', 'Secretaria', 'Operador']))
                                                @if($role->activo)
                                                    <form action="{{ route('admin.roles.desactivate', $role) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('¿Estás seguro de desactivar este rol?');">
                                                        @csrf
                                                        @method('PUT') {{-- ✅ CAMBIADO: PUT en lugar de DELETE --}}
                                                        <button type="submit" class="btn btn-danger" title="Desactivar Rol">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('admin.roles.activate', $role) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('¿Estás seguro de activar este rol?');">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" class="btn btn-success" title="Activar Rol">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <button class="btn btn-secondary" disabled title="Rol del sistema">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay roles registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table td {
            vertical-align: middle;
        }
        .btn-group .btn {
            border-radius: 0.25rem;
            margin-right: 2px;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.alert').delay(5000).fadeOut(300);
        });
    </script>
@stop