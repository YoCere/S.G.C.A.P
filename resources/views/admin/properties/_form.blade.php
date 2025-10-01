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
         placeholder="Ej: Casa color azul con portón negro">
  @error('referencia') <span class="text-danger">{{ $message }}</span> @enderror
</div>

{{-- ✅ NUEVO: CAMPO BARRIO --}}
<div class="form-group">
  <label>Barrio</label>
  <select name="barrio" class="form-control">
    <option value="">— Seleccione barrio —</option>
    <option value="Centro" @selected(old('barrio', $property->barrio ?? null) == 'Centro')>Centro</option>
    <option value="Aroma" @selected(old('barrio', $property->barrio ?? null) == 'Aroma')>Aroma</option>
    <option value="Los Valles" @selected(old('barrio', $property->barrio ?? null) == 'Los Valles')>Los Valles</option>
    <option value="Caipitandy" @selected(old('barrio', $property->barrio ?? null) == 'Caipitandy')>Caipitandy</option>
    <option value="Primavera" @selected(old('barrio', $property->barrio ?? null) == 'Primavera')>Primavera</option>
    <option value="Arboleda" @selected(old('barrio', $property->barrio ?? null) == 'Arboleda')>Arboleda</option>
  </select>
  @error('barrio') <span class="text-danger">{{ $message }}</span> @enderror
</div>

<div class="form-row">
  <div class="form-group col-md-6">
    <label>Latitud</label>
    <input type="number" step="0.00000001" name="latitud" class="form-control"
           value="{{ old('latitud', $property->latitud ?? '') }}"
           placeholder="Ej: -21.93500000">
    @error('latitud') <span class="text-danger">{{ $message }}</span> @enderror
    <small class="form-text text-muted">Coordenadas de tu comunidad: -21.935 a -21.930</small>
  </div>
  <div class="form-group col-md-6">
    <label>Longitud</label>
    <input type="number" step="0.00000001" name="longitud" class="form-control"
           value="{{ old('longitud', $property->longitud ?? '') }}"
           placeholder="Ej: -63.63700000">
    @error('longitud') <span class="text-danger">{{ $message }}</span> @enderror
    <small class="form-text text-muted">Coordenadas de tu comunidad: -63.637 a -63.632</small>
  </div>
</div>

<div class="form-group">
  <label>Estado</label>
  <select name="estado" class="form-control" required>
    @foreach (['activo','inactivo','cortado'] as $op)
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