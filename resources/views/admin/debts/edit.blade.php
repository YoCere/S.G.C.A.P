@extends('adminlte::page')

@section('title', 'Editar Deuda')

@section('content_header')
    <h1>Editar deuda #{{ $debt->id }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.debts.update', $debt) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="propiedad_id">Propiedad</label>
                    <select name="propiedad_id" class="form-control" required {{ $debt->estado === 'pagada' ? 'disabled' : '' }}>
                        @foreach ($propiedades as $propiedad)
                            <option value="{{ $propiedad->id }}" {{ $debt->propiedad_id == $propiedad->id ? 'selected' : '' }}>
                                {{ $propiedad->referencia }} - {{ $propiedad->cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="tarifa_id">Tarifa</label>
                    <select name="tarifa_id" id="tarifa_id" class="form-control" required {{ $debt->estado === 'pagada' ? 'disabled' : '' }}>
                        @foreach ($tarifas as $tarifa)
                            <option value="{{ $tarifa->id }}" data-precio="{{ $tarifa->precio_mensual }}"
                                {{ $debt->tarifa_id == $tarifa->id ? 'selected' : '' }}>
                                {{ $tarifa->nombre }} - {{ number_format($tarifa->precio_mensual, 2) }} Bs
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="monto_pendiente">Monto pendiente (Bs)</label>
                    <input type="number" step="0.01" min="0" name="monto_pendiente"
                           value="{{ $debt->monto_pendiente }}"
                           class="form-control"
                           {{ $debt->estado === 'pagada' ? 'readonly' : '' }}>
                </div>

                <div class="form-group">
                    <label for="fecha_emision">Fecha de emisión</label>
                    <input type="date" name="fecha_emision" value="{{ $debt->fecha_emision->format('Y-m-d') }}"
                           class="form-control"
                           {{ $debt->estado === 'pagada' ? 'readonly' : '' }}>
                </div>

                <div class="form-group">
                    <label for="fecha_vencimiento">Fecha de vencimiento</label>
                    <input type="date" name="fecha_vencimiento"
                           value="{{ $debt->fecha_vencimiento ? $debt->fecha_vencimiento->format('Y-m-d') : '' }}"
                           class="form-control"
                           {{ $debt->estado === 'pagada' ? 'readonly' : '' }}>
                </div>

                <div class="form-group">
                    <label>Estado</label>
                    <input type="text" class="form-control" value="{{ ucfirst($debt->estado) }}" readonly>
                </div>

                @if($debt->estado !== 'pagada')
                    <div class="form-group form-check">
                        <input type="checkbox" name="pagada_adelantada" class="form-check-input" value="1"
                               {{ $debt->pagada_adelantada ? 'checked' : '' }}>
                        <label class="form-check-label" for="pagada_adelantada">Pagada adelantada</label>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary" {{ $debt->estado === 'pagada' ? 'disabled' : '' }}>Actualizar</button>
                <a href="{{ route('admin.debts.index') }}" class="btn btn-secondary">Volver</a>
            </form>
        </div>
    </div>
@stop

@section('js')
@if($debt->estado !== 'pagada')
<script>
document.getElementById('tarifa_id').addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    const precio = selected.getAttribute('data-precio');
    if(precio) {
        document.querySelector('input[name="monto_pendiente"]').value = precio;
    }
});
</script>
@endif
@stop
