{{-- resources/views/admin/properties/_form.blade.php --}}
@csrf
<div class="form-row">
  <div class="form-group col-md-6">
    <label>Cliente</label>
    <select name="cliente_id" class="form-control" required>
      <option value="">— Seleccione —</option>
      @foreach ($clients as $c)
        <option value="{{ $c->id }}" @selected(old('cliente_id', $property->cliente_id ?? null) == $c->id)>
          {{ $c->nombre }} @if($c->ci) (CI: {{ $c->ci }}) @endif
        </option>
      @endforeach
    </select>
    @error('cliente_id') <span class="text-danger">{{ $message }}</span> @enderror
  </div>

  <div class="form-group col-md-6">
    <label>Tarifa</label>
    <select name="tarifa_id" class="form-control" required>
      <option value="">— Seleccione —</option>
      @foreach ($tariffs as $t)
        <option value="{{ $t->id }}" @selected(old('tarifa_id', $property->tarifa_id ?? null) == $t->id)>
          {{ $t->nombre }} (Bs {{ number_format($t->precio_mensual,2) }})
        </option>
      @endforeach
    </select>
    @error('tarifa_id') <span class="text-danger">{{ $message }}</span> @enderror
  </div>
</div>

<div class="form-group">
  <label>Referencia</label>
  <input type="text" name="referencia" class="form-control" required
         value="{{ old('referencia', $property->referencia ?? '') }}"
         placeholder="Ej: Barrio Centro, Casa #123">
  @error('referencia') <span class="text-danger">{{ $message }}</span> @enderror
</div>

<div class="form-row">
  <div class="form-group col-md-6">
    <label>Latitud</label>
    <input type="number" step="0.00000001" name="latitud" class="form-control"
           value="{{ old('latitud', $property->latitud ?? '') }}">
    @error('latitud') <span class="text-danger">{{ $message }}</span> @enderror
  </div>
  <div class="form-group col-md-6">
    <label>Longitud</label>
    <input type="number" step="0.00000001" name="longitud" class="form-control"
           value="{{ old('longitud', $property->longitud ?? '') }}">
    @error('longitud') <span class="text-danger">{{ $message }}</span> @enderror
  </div>
</div>

<div class="form-group">
  <label>Estado</label>
  <select name="estado" class="form-control" required>
    @foreach (['activo','inactivo'] as $op)
      <option value="{{ $op }}" @selected(old('estado', $property->estado ?? 'activo') == $op)>
        {{ ucfirst($op) }}
      </option>
    @endforeach
  </select>
  @error('estado') <span class="text-danger">{{ $message }}</span> @enderror
</div>

<button class="btn btn-primary">
  <i class="fas fa-save mr-1"></i> Guardar
</button>
<a href="{{ route('admin.properties.index') }}" class="btn btn-secondary">Cancelar</a>
