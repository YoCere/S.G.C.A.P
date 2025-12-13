@extends('layouts.admin-ultralight')

@section('title', 'Nueva Tarifa')

@section('content_header')
    <h1>Crear nueva tarifa</h1>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.tariffs.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input 
                        type="text"
                        name="nombre"
                        id="nombre"
                        class="form-control @error('nombre') is-invalid @enderror"
                        placeholder="Ej: NORMAL, ADULTO MAYOR, PLAN 40"
                        value="{{ old('nombre') }}"
                        required
                        pattern="[A-Za-z0-9ÁÉÍÓÚáéíóúÑñ ]+">

                    @error('nombre')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="precio_mensual">Precio mensual (Bs)</label>
                    <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="precio_mensual"
                    id="precio_mensual"
                    placeholder="Ej: 40.00"
                >

                    @error('precio_mensual')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción (opcional)</label>
                    <textarea
                        name="descripcion"
                        id="descripcion"
                        class="form-control"
                        rows="3"
                        placeholder="Notas sobre la tarifa (opcional)"
                    >{{ old('descripcion') }}</textarea>
                    @error('descripcion')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    Crear tarifa
                </button>
                <a href="{{ route('admin.tariffs.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener("DOMContentLoaded", () => {

    // 🔵 NOMBRE DE TARIFA
    const nombre = document.getElementById("nombre");
    nombre.addEventListener("input", function () {
        this.value = this.value
            .replace(/[^a-zA-Z0-9ÁÉÍÓÚáéíóúÑñ ]/g, "") // solo letras/números
            .replace(/\s{2,}/g, " ")                 // sin espacios dobles
            .toUpperCase();                          // a mayúsculas
    });

});
</script>
@stop
