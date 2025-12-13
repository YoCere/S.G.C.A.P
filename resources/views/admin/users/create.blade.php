@extends('layouts.admin-ultralight')

@section('title', 'Crear Usuario')

@section('content_header')
    <h1 class="h5 font-weight-bold">Crear Nuevo Usuario</h1>
    <small class="text-muted">Registre un nuevo usuario en el sistema</small>
@stop

@section('content')
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
                <h6 class="mb-0">Información del Usuario</h6>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Volver al listado
                </a>
            </div>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name" class="font-weight-bold">Nombre completo *</label>
                            <input type="text" 
                            class="form-control @error('name') is-invalid @enderror" 
                            id="name" name="name" 
                            value="{{ old('name') }}" 
                            placeholder="Ingrese el nombre completo" 
                            required pattern="[A-ZÁÉÍÓÚÑ ]+">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="email" class="font-weight-bold">Email *</label>
                            <input type="email" 
                            class="form-control @error('email') is-invalid @enderror" 
                            id="email" name="email" 
                            value="{{ old('email') }}" 
                            placeholder="usuario@ejemplo.com" 
                            required
                            pattern="^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[A-Z]{2,}$">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password" class="font-weight-bold">Contraseña *</label>
                            <input type="password" 
                            class="form-control @error('password') is-invalid @enderror" 
                            id="password" name="password"
                            minlength="8"
                            placeholder="Mínimo 8 caracteres" 
                            required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password_confirmation" class="font-weight-bold">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" 
                                   id="password_confirmation" name="password_confirmation" 
                                   placeholder="Repita la contraseña" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="font-weight-bold">Roles del Sistema *</label>
                    <div class="row">
                        @foreach($roles as $role)
                            <div class="col-md-3 col-sm-6 mb-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="role_{{ $role->id }}" name="roles[]" 
                                           value="{{ $role->id }}"
                                           {{ in_array($role->id, old('roles', [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="role_{{ $role->id }}">
                                        <span class="badge badge-{{ $role->name == 'admin' ? 'danger' : 'primary' }}">
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

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times mr-1"></i>Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>Crear Usuario
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
    </style>
@stop

@section('js')
<script>
    document.addEventListener("DOMContentLoaded", () => {
    
        // Convertir nombre a MAYÚSCULAS y evitar números
        const name = document.getElementById("name");
        name.addEventListener("input", function () {
            this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúñÑ ]/g, ""); // Solo letras
            this.value = this.value.toUpperCase(); // A mayúsculas
        });
    
    });
    </script>
@stop