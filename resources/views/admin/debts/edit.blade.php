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

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.debts.update', $debt) }}" method="POST" id="debtForm">
                @csrf
                @method('PUT')

                @if($debt->estado === 'pagada')
                    {{-- CAMPOS HIDDEN PARA DEUDAS PAGADAS --}}
                    <input type="hidden" name="propiedad_id" value="{{ $debt->propiedad_id }}">
                    <input type="hidden" name="tarifa_id" value="{{ $debt->tarifa_id }}">
                    <input type="hidden" name="monto_pendiente" value="{{ $debt->monto_pendiente }}">
                    <input type="hidden" name="fecha_emision" value="{{ $debt->fecha_emision->format('Y-m-d') }}">
                    <input type="hidden" name="fecha_vencimiento" value="{{ $debt->fecha_vencimiento ? $debt->fecha_vencimiento->format('Y-m-d') : '' }}">
                    <input type="hidden" name="estado" value="pagada">
                @endif

                <div class="form-group">
                    <label for="propiedad_id">Propiedad</label>
                    <select name="propiedad_id" id="propiedad_id" class="form-control" readonly disabled>
                        <!-- SOLO mostrar la propiedad actual, NO editable -->
                        @foreach ($propiedades as $propiedad)
                            @if($propiedad->id == $debt->propiedad_id)
                                <option value="{{ $propiedad->id }}" selected>
                                    {{ $propiedad->referencia }} - {{ $propiedad->client->nombre }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <input type="hidden" name="propiedad_id" value="{{ $debt->propiedad_id }}">
                    <small class="form-text text-muted">No se puede cambiar la propiedad de una deuda existente</small>
                </div>
                <div class="form-group">
                    <label for="tarifa_id">Tarifa</label>
                    <select name="tarifa_id" id="tarifa_id" class="form-control @error('tarifa_id') is-invalid @enderror" 
                        {{ $debt->estado === 'pagada' ? 'disabled' : 'required' }}>
                        <option value="">Seleccione tarifa</option>
                        @foreach ($tarifas as $tarifa)
                            <option value="{{ $tarifa->id }}" data-precio="{{ $tarifa->precio_mensual }}"
                                {{ old('tarifa_id', $debt->tarifa_id) == $tarifa->id ? 'selected' : '' }}>
                                {{ $tarifa->nombre }} - {{ number_format($tarifa->precio_mensual, 2) }} Bs
                            </option>
                        @endforeach
                    </select>
                    @error('tarifa_id')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="monto_pendiente">Monto pendiente (Bs)</label>
                    <input type="number" step="0.01" value="{{ $tarifaSeleccionada->precio_mensual ?? $debt->monto_pendiente }}" 
                           class="form-control" readonly>
                    <small class="form-text text-muted">Calculado automáticamente según la tarifa</small>
                </div>
                

                <div class="form-group">
                    <label for="fecha_emision">Fecha de emisión</label>
                    <input type="date" name="fecha_emision" id="fecha_emision" 
                           value="{{ old('fecha_emision', $debt->fecha_emision->format('Y-m-d')) }}"
                           class="form-control @error('fecha_emision') is-invalid @enderror"
                           {{ $debt->estado === 'pagada' ? 'readonly' : '' }}>
                    @error('fecha_emision')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
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
                    <label for="estado">Estado</label>
                    @if($debt->estado === 'pagada')
                        <input type="text" class="form-control" value="Pagada" readonly>
                        <small class="form-text text-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Esta deuda está pagada. No se puede cambiar el estado.
                        </small>
                    @else
                        <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                            <option value="pendiente" {{ old('estado', $debt->estado) == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="vencida" {{ old('estado', $debt->estado) == 'vencida' ? 'selected' : '' }}>Vencida</option>
                            <option value="pagada" {{ old('estado', $debt->estado) == 'pagada' ? 'selected' : '' }}>Pagada</option>
                        </select>
                        @error('estado')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    @endif
                </div>

                @if($debt->estado !== 'pagada')
                    <div class="form-group form-check">
                        <input type="checkbox" name="pagada_adelantada" class="form-check-input" id="pagada_adelantada" value="1"
                               {{ old('pagada_adelantada', $debt->pagada_adelantada) ? 'checked' : '' }} disabled>
                        <label class="form-check-label" for="pagada_adelantada" style="color: #6c757d;">
                            <i class="fas fa-info-circle"></i> Pagada adelantada (automático)
                        </label>
                        <small class="form-text text-muted">Se marca automáticamente cuando una deuda se paga antes de su fecha de emisión</small>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary" {{ $debt->estado === 'pagada' ? 'disabled' : '' }}>
                    {{ $debt->estado === 'pagada' ? 'Deuda Pagada (No editable)' : 'Actualizar' }}
                </button>
                <a href="{{ route('admin.debts.index') }}" class="btn btn-secondary">Volver al Listado</a>
            </form>
        </div>
    </div>
@stop

@section('js')
@if($debt->estado !== 'pagada')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tarifaSelect = document.getElementById('tarifa_id');
    const montoInput = document.getElementById('monto_pendiente');

    // Auto-completar monto según tarifa seleccionada
    tarifaSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const precio = selected.getAttribute('data-precio');
        if(precio) {
            montoInput.value = precio;
        }
    });

    // Validación adicional antes de enviar el formulario
    document.getElementById('debtForm').addEventListener('submit', function(e) {
        const fechaEmision = new Date(document.getElementById('fecha_emision').value);
        const fechaVencimiento = new Date(document.getElementById('fecha_vencimiento').value);
        
        // Validar que fecha vencimiento > fecha emisión
        if (fechaVencimiento && fechaVencimiento <= fechaEmision) {
            e.preventDefault();
            alert('Error: La fecha de vencimiento debe ser posterior a la fecha de emisión.');
            return false;
        }
    });
});
</script>
@endif
@stop