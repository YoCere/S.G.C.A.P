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
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.roles.update', $role) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name">Nombre del Rol *</label>
                            <input type="text" 
                                   name="name" 
                                   id="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $role->name) }}"
                                   placeholder="Ej: Supervisor, Auditor, etc."
                                   required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="description">Descripción</label>
                            <textarea name="description" 
                                      id="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="1"
                                      placeholder="Breve descripción del rol">{{ old('description', $role->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Permisos *</label>
                    @error('permissions')
                        <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                    
                    <div class="row">
                        @foreach($permissions as $module => $modulePermissions)
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <strong>{{ ucfirst($module) }}</strong>
                                            <small class="text-muted">({{ $modulePermissions->count() }} permisos)</small>
                                        </h6>
                                    </div>
                                    <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                        @foreach($modulePermissions as $permission)
                                            <div class="form-check mb-2">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission->id }}"
                                                       class="form-check-input permission-checkbox"
                                                       id="permission_{{ $permission->id }}"
                                                       {{ in_array($permission->id, $rolePermissions) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="permission_{{ $permission->id }}">
                                                    <small>{{ $permission->name }}</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="card-footer py-2">
                                        <div class="form-check">
                                            <input type="checkbox" 
                                                   class="form-check-input select-all-module"
                                                   data-module="{{ $module }}"
                                                   {{ count(array_intersect($modulePermissions->pluck('id')->toArray(), $rolePermissions)) == $modulePermissions->count() ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                <small>Seleccionar todo</small>
                                            </label>
                                        </div>
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
        .card-header h6 {
            font-size: 0.9rem;
        }
        .form-check-label small {
            font-size: 0.8rem;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Seleccionar todos los permisos de un módulo
            $('.select-all-module').on('change', function() {
                const module = $(this).data('module');
                const isChecked = $(this).is(':checked');
                
                $(this).closest('.card').find('.permission-checkbox').prop('checked', isChecked);
            });

            // Verificar si todos los permisos de un módulo están seleccionados
            $('.permission-checkbox').on('change', function() {
                const card = $(this).closest('.card');
                const totalCheckboxes = card.find('.permission-checkbox').length;
                const checkedCheckboxes = card.find('.permission-checkbox:checked').length;
                
                const selectAllCheckbox = card.find('.select-all-module');
                selectAllCheckbox.prop('checked', totalCheckboxes === checkedCheckboxes);
            });
        });
    </script>
@stop