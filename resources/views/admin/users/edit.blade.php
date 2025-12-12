@extends('layouts.admin-ultralight')

@section('title', 'Editar Usuario')

@section('content_header')
    <h1 class="h5 font-weight-bold">Editar Usuario</h1>
    <small class="text-muted">Actualice la información del usuario</small>
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

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Por favor corrige los siguientes errores:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Editando: {{ $user->name }}</h6>
                <div class="btn-group">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-info btn-sm">
                        <i class="fas fa-eye mr-1"></i>Ver
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left mr-1"></i>Volver
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="font-weight-bold">Nombre completo *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $user->name) }}" 
                                   placeholder="Ingrese el nombre completo" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="font-weight-bold">Email *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $user->email) }}" 
                                   placeholder="usuario@ejemplo.com" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="font-weight-bold">Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" 
                                   placeholder="Dejar en blanco para mantener la actual">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle mr-1"></i>
                                Mínimo 8 caracteres. Solo complete si desea cambiar la contraseña.
                            </small>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation" class="font-weight-bold">Confirmar Contraseña</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" 
                                   placeholder="Repita la nueva contraseña">
                        </div>
                    </div>
                </div>

                {{-- ✅ SECCIÓN DE ROLES CORREGIDA --}}
                <div class="form-group">
                    <label class="font-weight-bold">Roles del Sistema *</label>
                    <div class="row">
                        @foreach($roles as $role)
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="role_{{ $role->id }}" name="roles[]" 
                                           value="{{ $role->id }}"
                                           {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="role_{{ $role->id }}">
                                        <span class="badge badge-{{ $role->name == 'Admin' ? 'danger' : 'primary' }}">
                                            {{ $role->name }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('roles')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">
                        <i class="fas fa-info-circle mr-1"></i>
                        Seleccione uno o más roles para el usuario
                    </small>
                </div>

                {{-- Información de auditoría --}}
                <div class="row mt-4 pt-3 border-top">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="fas fa-calendar-plus mr-1"></i>
                            Creado: {{ $user->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                    <div class="col-md-6 text-md-right">
                        <small class="text-muted">
                            <i class="fas fa-calendar-check mr-1"></i>
                            Actualizado: {{ $user->updated_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Actualizar Usuario
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .custom-control-label .badge {
            font-size: 0.85em;
            padding: 0.4em 0.8em;
        }
        .border-top {
            border-color: #e9ecef !important;
        }
    </style>
@stop