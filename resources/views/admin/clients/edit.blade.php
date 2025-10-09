@extends('adminlte::page')

@section('title', 'Editar Cliente: ' . $client->nombre)

@section('content_header')
    <h1>Editar Cliente</h1>
    <small class="text-muted">Actualice la información del cliente</small>
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

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Formulario de Edición</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.clients.update', $client) }}" method="POST">
            @csrf
            @method('put')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="nombre">Nombre Completo *</label>
                        <input 
                            type="text" 
                            name="nombre" 
                            id="nombre"
                            class="form-control @error('nombre') is-invalid @enderror"
                            placeholder="Ingrese el nombre del cliente"
                            value="{{ old('nombre', $client->nombre) }}"
                            required
                        >
                        @error('nombre')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ci">CI/NIT *</label>
                        <input 
                            type="text" 
                            name="ci" 
                            id="ci"
                            class="form-control @error('ci') is-invalid @enderror"
                            placeholder="Ingrese el CI del cliente"
                            value="{{ old('ci', $client->ci) }}"
                            required
                        >
                        @error('ci')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input 
                            type="text" 
                            name="telefono" 
                            id="telefono"
                            class="form-control @error('telefono') is-invalid @enderror"
                            placeholder="Ingrese el teléfono del cliente"
                            value="{{ old('telefono', $client->telefono) }}"
                        >
                        @error('telefono')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-6">
                    {{-- ✅ NUEVO: CAMPO DE CÓDIGO CLIENTE --}}
                    <div class="form-group">
                        <label for="codigo_cliente">Código Cliente</label>
                        <div class="input-group">
                            <input type="text" name="codigo_cliente" id="codigo_cliente"
                                   class="form-control bg-light"
                                   value="{{ $client->codigo_cliente }}" 
                                   readonly
                                   title="El código de cliente no se puede modificar">
                            <div class="input-group-append">
                                <span class="input-group-text text-success">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            El código de cliente es único y permanente. No se puede modificar.
                        </small>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning mt-3">
                <h6><i class="fas fa-exclamation-triangle mr-2"></i>Precaución</h6>
                <ul class="mb-0 small">
                    <li>El código de cliente debe ser único en el sistema</li>
                    <li>No modifique el código a menos que sea absolutamente necesario</li>
                    <li>El código se utiliza para búsquedas y referencias en todo el sistema</li>
                </ul>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>Actualizar Cliente
                </button>
                <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i>Volver al Listado
                </a>
                <a href="{{ route('admin.clients.show', $client) }}" class="btn btn-info">
                    <i class="fas fa-eye mr-1"></i>Ver Detalles
                </a>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
    <style>
        .alert ul {
            margin-bottom: 0;
        }
    </style>
@stop

@section('js')
<script>
    // Auto-ocultar alertas después de 5 segundos
    setTimeout(() => {
        $('.alert').alert('close');
    }, 5000);
</script>
@stop