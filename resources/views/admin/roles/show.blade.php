@extends('layouts.admin-ultralight')

@section('title', 'Detalles del Rol: ' . $role->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detalles del Rol: <strong>{{ $role->name }}</strong></h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Información del Rol</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="120px">Nombre:</th>
                            <td>{{ $role->name }}</td>
                        </tr>
                        <tr>
                            <th>Descripción:</th>
                            <td>{{ $role->description ?? 'Sin descripción' }}</td>
                        </tr>
                        <tr>
                            <th>Tipo:</th>
                            <td>
                                @if(in_array($role->name, ['Admin', 'Secretaria', 'Operador']))
                                    <span class="badge badge-success">Rol del Sistema</span>
                                @else
                                    <span class="badge badge-secondary">Rol Personalizado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Usuarios:</th>
                            <td>
                                <span class="badge badge-info">{{ $role->users->count() }} usuarios</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Permisos:</th>
                            <td>
                                <span class="badge badge-primary">{{ $role->permissions->count() }} permisos</span>
                            </td>
                        </tr>
                        <tr>
                            <th>Creado:</th>
                            <td>
                                <small class="text-muted">{{ $role->created_at->format('d/m/Y H:i') }}</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Actualizado:</th>
                            <td>
                                <small class="text-muted">{{ $role->updated_at->format('d/m/Y H:i') }}</small>
                            </td>
                        </tr>
                    </table>

                    <div class="mt-3">
                        @can('admin.users.edit')
                            @if(!in_array($role->name, ['Admin', 'Secretaria', 'Operador']))
                                <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit mr-1"></i> Editar Rol
                                </a>
                            @endif
                        @endcan
                        
                        @can('admin.users.destroy')
                            @if(!in_array($role->name, ['Admin', 'Secretaria', 'Operador']) && $role->users->count() == 0)
                                <form action="{{ route('admin.roles.destroy', $role) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('¿Está seguro de eliminar este rol?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash mr-1"></i> Eliminar
                                    </button>
                                </form>
                            @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Permisos Asignados</h5>
                </div>
                <div class="card-body">
                    @if($role->permissions->count() > 0)
                        <div class="row">
                            @php
                                $groupedPermissions = $role->permissions->groupBy(function($permission) {
                                    $parts = explode('.', $permission->name);
                                    return count($parts) >= 2 ? $parts[1] : 'general';
                                });
                            @endphp
                            
                            @foreach($groupedPermissions as $module => $modulePermissions)
                                <div class="col-md-6 mb-3">
                                    <div class="card border">
                                        <div class="card-header py-2 bg-light">
                                            <h6 class="mb-0">
                                                <strong>{{ ucfirst($module) }}</strong>
                                                <small class="text-muted">({{ $modulePermissions->count() }})</small>
                                            </h6>
                                        </div>
                                        <div class="card-body p-2" style="max-height: 200px; overflow-y: auto;">
                                            @foreach($modulePermissions as $permission)
                                                <span class="badge badge-success badge-sm mb-1 mr-1">
                                                    {{ $permission->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning text-center mb-0">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Este rol no tiene permisos asignados
                        </div>
                    @endif
                </div>
            </div>

            @if($role->users->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="card-title">Usuarios con este Rol</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($role->users as $user)
                                    <tr>
                                        <td>{{ $user->id }}</td>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            <span class="badge badge-{{ $user->activo ? 'success' : 'warning' }}">
                                                {{ $user->activo ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge-sm {
            font-size: 0.7rem;
            padding: 0.25em 0.4em;
        }
        .table-borderless th {
            font-weight: 600;
        }
    </style>
@stop