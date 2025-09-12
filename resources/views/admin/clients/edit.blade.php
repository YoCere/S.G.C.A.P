@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Editar clientes</h1>
@stop

@section('content')
@if (session('info'))
<div class="alert alert-success">
    <strong>{{ session('info') }}</strong>
</div>

@endif

<div class="card">
<div class="card-body">
    <form action="{{ route('admin.clients.update', $client) }}" method="POST">
        @csrf
        @method('put')
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input 
                type="text" 
                name="nombre" 
                id="nombre"
                class="form-control"
                placeholder="Ingrese el nombre del cliente"
                value="{{ old('nombre', $client->nombre) }}"
            >
    
            @error('nombre')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
    
        <div class="form-group">
            <label for="ci">CI</label>
            <input 
                type="text" 
                name="ci" 
                id="ci"
                class="form-control"
                placeholder="Ingrese el CI del cliente"
                value="{{ old('ci', $client->ci) }}"
            >
    
            @error('ci')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group">
            <label for="telefono">Telefono</label>
            <input 
                type="text" 
                name="telefono" 
                id="telefono"
                class="form-control"
                placeholder="Ingrese el telefono del cliente"
                value="{{ old('telefono', $client->telefono) }}"
            >
            @error('telefono')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="referencia">Referencia</label>
            <input 
                type="text" 
                name="referencia" 
                id="referencia"
                class="form-control"
                placeholder="Ingrese una referencia de la casa del cliente"
                value="{{ old('referencia', $client->referencia) }}"
            >
            @error('referencia')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>


        <button type="submit" class="btn btn-primary">
            Actualizar c    liente
        </button>
    </form>
    
    </div>
@stop



