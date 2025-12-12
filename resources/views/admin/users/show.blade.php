@extends('layouts.admin-ultralight')

@section('title', 'Detalles del Usuario')

@section('content_header')
    <h1 class="h5 font-weight-bold">Detalles del Usuario</h1>
    <small class="text-muted">Información completa del usuario</small>
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

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Información Personal</h6>
                        <div class="btn-group">
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit mr-1"></i>Editar
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left mr-1"></i>Volver
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Nombre completo</label>
                                <p class="form-control-plaintext">{{ $user->name }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Email</label>
                                <p class="form-control-plaintext">
                                    {{ $user->email }}
                                    @if($user->email_verified_at)
                                        <span class="badge badge-success ml-2">Verificado</span>
                                    @else
                                        <span class="badge badge-warning ml-2">No verificado</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Fecha de registro</label>
                                <p class="form-control-plaintext">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold text-muted small">Última actualización</label>
                                <p class="form-control-plaintext">
                                    {{ $user->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">Roles Asignados</h6>
                </div>
                <div class="card-body">
                    @if($user->roles->count() > 0)
                        @foreach($user->roles as $role)
                            <span class="badge badge-{{ $role->name == 'admin' ? 'danger' : 'primary' }} badge-pill mb-2 mr-1" style="font-size: 1em;">
                                {{ $role->name }}
                            </span>
                        @endforeach
                    @else
                        <span class="badge badge-secondary">Sin roles asignados</span>
                    @endif
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0">Estadísticas</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-3x text-primary"></i>
                        </div>
                        <p class="mb-1">
                            <strong>ID Usuario:</strong> #{{ $user->id }}
                        </p>
                        <p class="mb-0">
                            <strong>Estado:</strong> 
                            <span class="badge badge-success">Activo</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sección de actividad reciente (puedes expandir esto más adelante) --}}
    <div class="card mt-4">
        <div class="card-header">
            <h6 class="mb-0">Información del Sistema</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 text-center">
                    <div class="border rounded p-3">
                        <i class="fas fa-receipt fa-2x text-info mb-2"></i>
                        <h5>{{ $user->pagos_count ?? 0 }}</h5>
                        <small class="text-muted">Pagos Registrados</small>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="border rounded p-3">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h5>{{ $user->created_at->diffForHumans() }}</h5>
                        <small class="text-muted">En el sistema</small>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="border rounded p-3">
                        <i class="fas fa-shield-alt fa-2x text-success mb-2"></i>
                        <h5>{{ $user->roles->count() }}</h5>
                        <small class="text-muted">Roles asignados</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .form-control-plaintext {
            padding: 0.375rem 0;
            margin-bottom: 0;
            background-color: transparent;
            border: solid transparent;
            border-width: 1px 0;
            font-weight: 500;
        }
        .border.rounded {
            border-color: #e9ecef !important;
        }
    </style>
@stop