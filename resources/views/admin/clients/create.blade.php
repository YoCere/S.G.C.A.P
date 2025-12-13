@extends('layouts.admin-ultralight')
@section('title', 'Registrar Cliente')

@section('content_header')
    <h1>Registrar Nuevo Cliente</h1>
    <small class="text-muted">Complete la información personal del cliente</small>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Errores encontrados:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Formulario de Registro</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.clients.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre Completo *</label>
                            <input type="text" name="nombre" id="nombre"
                            class="form-control @error('nombre') is-invalid @enderror"
                            placeholder="Ingrese el nombre completo"
                            value="{{ old('nombre') }}" 
                            required 
                            pattern="[A-ZÁÉÍÓÚÑ ]+">
                            @error('nombre')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="ci">CI/NIT *</label>
                            <input type="text" name="ci" id="ci"
                            class="form-control @error('ci') is-invalid @enderror"
                            placeholder="Ej: 6543210-1B"
                            value="{{ old('ci') }}"
                            required
                            pattern="[0-9]{7,8}(-[0-9A-Z]{1,2})?">                     
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
                            <input type="text" name="telefono" id="telefono"
                            maxlength="8"
                            pattern="[0-9]{8}"
                            class="form-control @error('telefono') is-invalid @enderror"
                            placeholder="Ingrese el teléfono"
                            value="{{ old('telefono') }}">
                            @error('telefono')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        {{-- ✅ NUEVO: CAMPO DE CÓDIGO CLIENTE (generado automáticamente) --}}
                        <div class="form-group">
                            <label for="codigo_cliente">Código Cliente</label>
                            <div class="input-group">
                                <input type="text" name="codigo_cliente" id="codigo_cliente" 
                                       class="form-control bg-light" 
                                       value="{{ old('codigo_cliente', 'Se generará automáticamente') }}" 
                                       readonly>
                                <div class="input-group-append">
                                    <span class="input-group-text text-success">
                                        <i class="fas fa-key"></i>
                                    </span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                El código de cliente se genera automáticamente de forma aleatoria
                            </small>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <h6><i class="fas fa-info-circle mr-2"></i>Información importante</h6>
                    <ul class="mb-0 small">
                        <li>Los campos marcados con * son obligatorios</li>
                        <li>El código de cliente es único y se genera automáticamente</li>
                        <li>Después de crear el cliente, podrá agregar sus propiedades</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i>Registrar Cliente
                    </button>
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop

@section('css')
    <style>
        .bg-light {
            background-color: #f8f9fa !important;
        }
    </style>
@stop

@section('js')
<script>
    document.addEventListener("DOMContentLoaded", () => {
    
        // 🔵 Convertir nombre a MAYÚSCULAS automáticamente
        const nombre = document.getElementById("nombre");
        nombre.addEventListener("input", function () {
            // Solo letras y espacios
            this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúñÑ ]/g, "");
            // Convertir a mayúsculas
            this.value = this.value.toUpperCase();
        });
    
        // 🔵 Validación de CI
        const ci = document.getElementById("ci");
        ci.addEventListener("input", function () {
            this.value = this.value.toUpperCase();
            this.value = this.value.replace(/[^0-9A-Z\-]/g, "");
        });
    
        // 🔵 Validación de teléfono (solo 8 dígitos)
        const telefono = document.getElementById("telefono");
        telefono.addEventListener("input", function () {
            this.value = this.value.replace(/[^0-9]/g, ""); // Solo números
            this.value = this.value.substring(0, 8);        // Máximo 8 dígitos
        });
    });
</script>
@stop