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
                    <label for="propiedad_id">Propiedad *</label>
                    <select name="propiedad_id" id="propiedad_id" class="form-control @error('propiedad_id') is-invalid @enderror" required>
                        <option value="">Seleccione propiedad</option>
                        @foreach ($propiedades as $propiedad)
                            <option value="{{ $propiedad->id }}" 
                                    data-tarifa-id="{{ $propiedad->tarifa_id }}"
                                    data-tarifa-nombre="{{ $propiedad->tariff->nombre }}"
                                    data-tarifa-precio="{{ $propiedad->tariff->precio_mensual }}"
                                    data-tarifa-activa="{{ $propiedad->tariff->activo }}"
                                    {{ old('propiedad_id') == $propiedad->id ? 'selected' : '' }}>
                                {{ $propiedad->referencia }} - {{ $propiedad->client->nombre }}
                                @if(!$propiedad->tariff->activo)
                                    <span class="text-warning"> (Tarifa Inactiva)</span>
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('propiedad_id')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                {{-- ✅ TARIFA AUTOMÁTICA (solo lectura) --}}
                <div class="form-group">
                    <label for="tarifa_info">Tarifa Aplicada</label>
                    <div id="tarifa_info" class="form-control-plaintext p-2 border rounded bg-light">
                        <em class="text-muted">Seleccione una propiedad para ver la tarifa</em>
                    </div>
                    <input type="hidden" name="tarifa_id" id="tarifa_id" value="">
                    <small class="form-text text-muted">
                        La tarifa se asigna automáticamente según la propiedad seleccionada
                    </small>
                </div>

                <div class="form-group">
                    <label for="monto_pendiente">Monto pendiente (Bs) *</label>
                    <input type="number" step="0.01" min="0" name="monto_pendiente" id="monto_pendiente"
                           value="{{ old('monto_pendiente') }}"
                           class="form-control @error('monto_pendiente') is-invalid @enderror" required readonly>
                    @error('monto_pendiente')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror>
                    <small class="form-text text-muted">Calculado automáticamente según la tarifa de la propiedad</small>
                </div>

                <div class="form-group">
                    <label for="fecha_emision">Fecha de emisión *</label>
                    <input type="date" name="fecha_emision" id="fecha_emision" 
                           value="{{ old('fecha_emision', date('Y-m-d')) }}"
                           class="form-control @error('fecha_emision') is-invalid @enderror" required>
                    @error('fecha_emision')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror>
                </div>

                <div class="form-group">
                    <label for="fecha_vencimiento">Fecha de vencimiento</label>
                    <input type="date" name="fecha_vencimiento" id="fecha_vencimiento"
                           value="{{ old('fecha_vencimiento') }}"
                           class="form-control @error('fecha_vencimiento') is-invalid @enderror">
                    @error('fecha_vencimiento')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror>
                    <small class="form-text text-muted">Opcional. Se calcula automáticamente (15 días después) si se deja vacío</small>
                </div>

                <div class="form-group">
                    <label for="estado">Estado *</label>
                    <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                        <option value="pendiente" {{ old('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="pagada" {{ old('estado') == 'pagada' ? 'selected' : '' }}>Pagada</option>
                    </select>
                    @error('estado')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror>
                </div>

                <div class="alert alert-warning" id="tarifaInactivaAlert" style="display: none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Advertencia:</strong> La propiedad seleccionada tiene una tarifa inactiva. 
                    Puede generar la deuda, pero revise si necesita actualizar la tarifa de la propiedad.
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">Guardar Deuda</button>
                <a href="{{ route('admin.debts.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const propiedadSelect = document.getElementById('propiedad_id');
    const tarifaInfo = document.getElementById('tarifa_info');
    const tarifaIdInput = document.getElementById('tarifa_id');
    const montoInput = document.getElementById('monto_pendiente');
    const fechaEmisionInput = document.getElementById('fecha_emision');
    const fechaVencimientoInput = document.getElementById('fecha_vencimiento');
    const tarifaInactivaAlert = document.getElementById('tarifaInactivaAlert');
    const submitBtn = document.getElementById('submitBtn');

    // Actualizar tarifa y monto cuando se selecciona propiedad
    propiedadSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        
        if (selected.value) {
            const tarifaId = selected.getAttribute('data-tarifa-id');
            const tarifaNombre = selected.getAttribute('data-tarifa-nombre');
            const tarifaPrecio = selected.getAttribute('data-tarifa-precio');
            const tarifaActiva = selected.getAttribute('data-tarifa-activa') === '1';

            // Actualizar información de tarifa
            tarifaInfo.innerHTML = `
                <strong>${tarifaNombre}</strong> - 
                <span class="text-success">Bs ${parseFloat(tarifaPrecio).toFixed(2)}</span>
                ${!tarifaActiva ? '<span class="badge badge-warning ml-2">INACTIVA</span>' : ''}
            `;
            
            // Actualizar campos hidden e inputs
            tarifaIdInput.value = tarifaId;
            montoInput.value = tarifaPrecio;

            // Mostrar/ocultar alerta de tarifa inactiva
            if (!tarifaActiva) {
                tarifaInactivaAlert.style.display = 'block';
            } else {
                tarifaInactivaAlert.style.display = 'none';
            }
        } else {
            // Resetear si no hay propiedad seleccionada
            tarifaInfo.innerHTML = '<em class="text-muted">Seleccione una propiedad para ver la tarifa</em>';
            tarifaIdInput.value = '';
            montoInput.value = '';
            tarifaInactivaAlert.style.display = 'none';
        }
    });

    // Calcular fecha de vencimiento automáticamente
    fechaEmisionInput.addEventListener('change', function() {
        if (this.value && !fechaVencimientoInput.value) {
            const fecha = new Date(this.value);
            fecha.setDate(fecha.getDate() + 15);
            fechaVencimientoInput.value = fecha.toISOString().split('T')[0];
        }
    });

    // Validación antes de enviar
    document.getElementById('debtForm').addEventListener('submit', function(e) {
        if (!propiedadSelect.value) {
            e.preventDefault();
            alert('Error: Debe seleccionar una propiedad.');
            return false;
        }

        const fechaEmision = new Date(fechaEmisionInput.value);
        const fechaVencimiento = fechaVencimientoInput.value ? new Date(fechaVencimientoInput.value) : null;
        
        if (fechaVencimiento && fechaVencimiento <= fechaEmision) {
            e.preventDefault();
            alert('Error: La fecha de vencimiento debe ser posterior a la fecha de emisión.');
            return false;
        }
    });

    // Disparar change event al cargar si ya hay propiedad seleccionada
    if (propiedadSelect.value) {
        propiedadSelect.dispatchEvent(new Event('change'));
    }
});
</script>
@stop