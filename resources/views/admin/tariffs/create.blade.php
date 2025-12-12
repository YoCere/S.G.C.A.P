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
                        class="form-control"
                        placeholder="Ej: Normal, Adulto mayor"
                        value="{{ old('nombre') }}"
                        required
                    >
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
                        class="form-control"
                        placeholder="Ej: 40.00"
                        value="{{ old('precio_mensual') }}"
                        required
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
