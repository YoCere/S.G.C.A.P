@extends('adminlte::page')

@section('title', 'Editar Deuda')

@section('content_header')
    <h1>Editar deuda #{{ $debt->id }}</h1>
@stop

@section('content')
    @if (session('info'))
        <div class="alert alert-success">
            <strong>{{ session('info') }}</strong>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            <strong>{{ session('error') }}</strong>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.debts.update', $debt) }}" method="POST" id="debtForm">
                @csrf
                @method('PUT')

                {{-- ✅ INFORMACIÓN NO EDITABLE --}}
                <div class="form-group">
                    <label>Propiedad</label>
                    <div class="form-control-plaintext p-2 border rounded bg-light">
                        <strong>{{ $debt->propiedad->referencia }}</strong> - 
                        {{ $debt->propiedad->client->nombre }}
                    </div>
                    <small class="form-text text-muted">No se puede cambiar la propiedad</small>
                </div>

                <div class="form-group">
                    <label>Tarifa Aplicada</label>
                    <div class="form-control-plaintext p-2 border rounded bg-light">
                        <strong>{{ $debt->propiedad->tariff->nombre }}</strong> - 
                        <span class="text-success">Bs {{ number_format($debt->tarifa->precio_mensual, 2) }}</span>
                        @if(!$debt->tarifa->activo)
                            <span class="badge badge-warning ml-2">TARIFA INACTIVA</span>
                        @endif
                    </div>
                    <small class="form-text text-muted">
                        La tarifa está bloqueada para mantener la integridad histórica
                    </small>
                </div>

                <div class="form-group">
                    <label>Monto (Bs)</label>
                    <div class="form-control-plaintext p-2 border rounded bg-light">
                        <strong class="text-success">Bs {{ number_format($debt->monto_pendiente, 2) }}</strong>
                    </div>
                </div>

                {{-- ✅ SOLO CAMPOS EDITABLES --}}
                <div class="form-group">
                    <label for="fecha_emision">Fecha de emisión *</label>
                    <input type="date" name="fecha_emision" id="fecha_emision" 
                           value="{{ old('fecha_emision', $debt->fecha_emision->format('Y-m-d')) }}"
                           class="form-control @error('fecha_emision') is-invalid @enderror"
                           {{ $debt->estado === 'pagada' ? 'readonly' : 'required' }}>
                    @error('fecha_emision')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror>
                </div>

                <div class="form-group">
                    <label for="fecha_vencimiento">Fecha de vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento"
                           value="{{ old('fecha_vencimiento', $debt->fecha_vencimiento ? $debt->fecha_vencimiento->format('Y-m-d') : '') }}"
                           class="form-control @error('fecha_vencimiento') is-invalid @enderror"
                           {{ $debt->estado === 'pagada' ? 'readonly' : '' }}>
                    @error('fecha_vencimiento')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror>
                </div>

                <div class="form-group">
                    <label for="estado">Estado *</label>
                    @if($debt->estado === 'pagada')
                        <div class="form-control-plaintext p-2 border rounded bg-light">
                            <span class="badge badge-success">PAGADA</span>
                        </div>
                        <small class="form-text text-warning">
                            <i class="fas fa-lock"></i> No se puede cambiar el estado de una deuda pagada
                        </small>
                    @else
                        <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                            <option value="pendiente" {{ old('estado', $debt->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="vencida" {{ old('estado', $debt->estado) == 'vencida' ? 'selected' : '' }}>Vencida</option>
                            <option value="pagada" {{ old('estado', $debt->estado) == 'pagada' ? 'selected' : '' }}>Pagada</option>
                        </select>
                        @error('estado')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror>
                    @endif
                </div>

                @if($debt->estado !== 'pagada')
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Nota:</strong> La propiedad y tarifa están bloqueadas para mantener 
                        la integridad de los registros históricos.
                    </div>
                @endif

                <button type="submit" class="btn btn-primary" {{ $debt->estado === 'pagada' ? 'disabled' : '' }}>
                    {{ $debt->estado === 'pagada' ? 'Deuda Pagada (No editable)' : 'Actualizar Deuda' }}
                </button>
                <a href="{{ route('admin.debts.index') }}" class="btn btn-secondary">Volver al Listado</a>
            </form>
        </div>
    </div>
@stop