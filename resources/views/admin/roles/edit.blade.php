@extends('adminlte::page')

@section('title', 'Editar Rol: ' . $role->name)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Editar Rol: <strong>{{ $role->name }}</strong></h1>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
@stop

@section('content')
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
            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="name">Nombre del Rol *</label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $role->name) }}"
                                   placeholder="Ingrese el nombre del rol"
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="h5">Permisos asignados *</label>
                    @error('permissions')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    
                    <div class="row">
                        @foreach($permissions as $module => $modulePermissions)
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0 text-uppercase">
                                            <i class="fas fa-folder mr-2"></i>{{ $module }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        @foreach($modulePermissions as $permission)
                                            <div class="form-check mb-2">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}"
                                                       class="form-check-input"
                                                       id="permission_{{ $permission->id }}"
                                                       {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    <strong>{{ $permission->name }}</strong>
                                                    @if($permission->description)
                                                        <br><small class="text-muted">{{ $permission->description }}</small>
                                                    @endif
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Actualizar Rol
                    </button>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card-header {
            padding: 0.75rem 1.25rem;
        }
        .form-check-label {
            font-size: 0.9rem;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Auto-dismiss alerts after 5 seconds
            $('.alert').delay(5000).fadeOut(300);
            
            // Select all permissions for a module
            $('.select-module').on('change', function() {
                const module = $(this).data('module');
                $(`input[name="permissions[]"][data-module="${module}"]`).prop('checked', this.checked);
            });
        });
    </script>
@stop