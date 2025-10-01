@extends('adminlte::page')

@section('title', 'Registrar Deuda')

@section('content_header')
    <h1>Registrar nueva deuda</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.debts.store') }}" method="POST" id="debtForm">
                @csrf

                <div class="form-group">
                    <label for="propiedad_id">Propiedad</label>
                    <select name="propiedad_id" id="propiedad_id" class="form-control @error('propiedad_id') is-invalid @enderror" required>
                        <option value="">Seleccione propiedad</option>
                        @foreach ($propiedades as $propiedad)
                            <option value="{{ $propiedad->id }}" {{ old('propiedad_id') == $propiedad->id ? 'selected' : '' }}>
                                {{ $propiedad->referencia }} - {{ $propiedad->client->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('propiedad_id')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="tarifa_id">Tarifa</label>
                    <select name="tarifa_id" id="tarifa_id" class="form-control @error('tarifa_id') is-invalid @enderror" required>
                        <option value="">Seleccione tarifa</option>
                        @foreach ($tarifas as $tarifa)
                            <option value="{{ $tarifa->id }}" data-precio="{{ $tarifa->precio_mensual }}" {{ old('tarifa_id') == $tarifa->id ? 'selected' : '' }}>
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
                    <input type="number" step="0.01" min="0" name="monto_pendiente" id="monto_pendiente"
                           value="{{ old('monto_pendiente') }}"
                           class="form-control @error('monto_pendiente') is-invalid @enderror" required readonly>
                    @error('monto_pendiente')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                    <small class="form-text text-muted">El monto se establece automáticamente según la tarifa seleccionada</small>
                </div>

                <div class="form-group">
                    <label for="fecha_emision">Fecha de emisión</label>
                    <input type="date" name="fecha_emision" id="fecha_emision" 
                           value="{{ old('fecha_emision', date('Y-m-d')) }}"
                           class="form-control @error('fecha_emision') is-invalid @enderror" required>
                    @error('fecha_emision')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="fecha_vencimiento">Fecha de vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento"
                           value="{{ old('fecha_vencimiento') }}"
                           class="form-control @error('fecha_vencimiento') is-invalid @enderror" readonly>
                    @error('fecha_vencimiento')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                    <small class="form-text text-muted">Se calcula automáticamente (15 días después de la emisión)</small>
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                        <option value="pendiente" {{ old('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="pagada" {{ old('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                    </select>
                    @error('estado')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror>
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" name="pagada_adelantada" class="form-check-input" id="pagada_adelantada" value="1"
                           {{ old('pagada_adelantada') ? 'checked' : '' }}>
                    <label class="form-check-label" for="pagada_adelantada">Pagada adelantada</label>
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('admin.debts.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tarifaSelect = document.getElementById('tarifa_id');
    const montoInput = document.getElementById('monto_pendiente');
    const fechaEmisionInput = document.getElementById('fecha_emision');
    const fechaVencimientoInput = document.getElementById('fecha_vencimiento');
    const estadoSelect = document.getElementById('estado');

    // Auto-completar monto según tarifa seleccionada
    tarifaSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        const precio = selected.getAttribute('data-precio');
        if(precio) {
            montoInput.value = precio;
        } else {
            montoInput.value = '';
        }
    });

    // Calcular fecha de vencimiento automáticamente (15 días después)
    fechaEmisionInput.addEventListener('change', function() {
        if (this.value) {
            const fecha = new Date(this.value);
            fecha.setDate(fecha.getDate() + 15);
            fechaVencimientoInput.value = fecha.toISOString().split('T')[0];
        }
    });

    // Validación adicional antes de enviar el formulario
    document.getElementById('debtForm').addEventListener('submit', function(e) {
        const fechaEmision = new Date(fechaEmisionInput.value);
        const fechaVencimiento = new Date(fechaVencimientoInput.value);
        
        // Validar que fecha vencimiento > fecha emisión
        if (fechaVencimiento <= fechaEmision) {
            e.preventDefault();
            alert('Error: La fecha de vencimiento debe ser posterior a la fecha de emisión.');
            return false;
        }

        // Validar que si está pagada, no puede ser adelantada
        if (estadoSelect.value === 'pagada' && document.getElementById('pagada_adelantada').checked) {
            e.preventDefault();
            alert('Error: Una deuda pagada no puede marcarse como adelantada.');
            return false;
        }
    });

    // Calcular vencimiento al cargar la página si hay fecha de emisión
    if (fechaEmisionInput.value) {
        const fecha = new Date(fechaEmisionInput.value);
        fecha.setDate(fecha.getDate() + 15);
        fechaVencimientoInput.value = fecha.toISOString().split('T')[0];
    }
});
</script>
@stop