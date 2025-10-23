@extends('adminlte::page')

@section('title', 'Crear Nuevo Rol')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Crear Nuevo Rol</h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Nombre del Rol *</label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}"
                                   placeholder="Ingrese el nombre del rol"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="h3">Lista de Permisos *</label>
                    @error('permissions')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    
                    <div class="row">
                        @foreach($permissions as $permission)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" 
                                           name="permissions[]" 
                                           value="{{ $permission->id }}"
                                           class="form-check-input"
                                           id="permission_{{ $permission->id }}"
                                           {{ in_array($permission->id, old('permissions', [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="permission_{{ $permission->id }}">
                                        <strong>{{ $permission->name }}</strong>
                                        @if($permission->description)
                                            <br><small class="text-muted">{{ $permission->description }}</small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Crear Rol
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop