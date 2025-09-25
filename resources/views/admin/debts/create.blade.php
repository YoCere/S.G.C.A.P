@extends('adminlte::page')

@section('title', 'Registrar Deuda')

@section('content_header')
    <h1>Registrar nueva deuda</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.debts.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="propiedad_id">Propiedad</label>
                    <select name="propiedad_id" class="form-control @error('propiedad_id') is-invalid @enderror" required>
                        <option value="">Seleccione propiedad</option>
                        @foreach ($propiedades as $propiedad)
                            <option value="{{ $propiedad->id }}" {{ old('propiedad_id') == $propiedad->id ? 'selected' : '' }}>
                                {{ $propiedad->referencia }} - {{ $propiedad->cliente->nombre }}
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
                    <input type="number" step="0.01" min="0" name="monto_pendiente"
                           value="{{ old('monto_pendiente') }}"
                           class="form-control @error('monto_pendiente') is-invalid @enderror" required>
                    @error('monto_pendiente')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="fecha_emision">Fecha de emisión</label>
                    <input type="date" name="fecha_emision" value="{{ old('fecha_emision', date('Y-m-d')) }}"
                           class="form-control @error('fecha_emision') is-invalid @enderror" required>
                    @error('fecha_emision')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="fecha_vencimiento">Fecha de vencimiento</label>
                    <input type="date" name="fecha_vencimiento" value="{{ old('fecha_vencimiento') }}"
                           class="form-control @error('fecha_vencimiento') is-invalid @enderror">
                    @error('fecha_vencimiento')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group form-check">
                    <input type="checkbox" name="pagada_adelantada" class="form-check-input" value="1"
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
document.getElementById('tarifa_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const precio = selected.getAttribute('data-precio');
    if(precio) {
        document.querySelector('input[name="monto_pendiente"]').value = precio;
    }
});
</script>
@stop
