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
    @error('tarifa_id') @enderror
  </div>
</div>

<div class="form-group">
  <label>Referencia</label>
  <input type="text" name="referencia" class="form-control" required
         value="{{ old('referencia', $property->referencia ?? '') }}"
         placeholder="Ej: Casa color azul con portón negro">
  @error('referencia') <span class="text-danger">{{ $message }}</span> @enderror
</div>

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

{{-- ✅ MAPA INTERACTIVO SIMPLIFICADO --}}
<div class="form-group">
  <label>Seleccionar ubicación en el mapa</label>
  <div class="alert alert-info">
    <small>
      <i class="fas fa-info-circle"></i> 
      Haz clic en el mapa para establecer las coordenadas. Puedes arrastrar el marcador para ajustar.
    </small>
  </div>
  
  {{-- Mapa --}}
  <div id="locationMap" style="height: 300px; width: 100%; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;"></div>
  
  {{-- Botones de acción SIMPLIFICADOS --}}
  <div class="btn-group mb-3" role="group">
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetToCommunityCenter()">
      <i class="fas fa-home"></i> Centrar mapa
    </button>
    <button type="button" class="btn btn-outline-warning btn-sm" onclick="clearLocation()">
      <i class="fas fa-times"></i> Limpiar
    </button>
  </div>
</div>

<div class="form-row">
  <div class="form-group col-md-6">
    <label>Latitud</label>
    <div class="input-group">
      <input type="number" step="0.00000001" name="latitud" id="latitud" class="form-control"
             value="{{ old('latitud', $property->latitud ?? '') }}"
             placeholder="Se autocompletará con el mapa" readonly>
      <div class="input-group-append">
        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
      </div>
    </div>
    @error('latitud') <span class="text-danger">{{ $message }}</span> @enderror
  </div>
  
  <div class="form-group col-md-6">
    <label>Longitud</label>
    <div class="input-group">
      <input type="number" step="0.00000001" name="longitud" id="longitud" class="form-control"
             value="{{ old('longitud', $property->longitud ?? '') }}"
             placeholder="Se autocompletará con el mapa" readonly>
      <div class="input-group-append">
        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
      </div>
    </div>
    @error('longitud') <span class="text-danger">{{ $message }}</span> @enderror
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

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
#locationMap { cursor: crosshair; }
.leaflet-popup-content { font-size: 14px; }
.coordinates-input { background-color: #f8f9fa; }
</style>
@endpush

@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
// ✅ VARIABLES GLOBALES SIMPLIFICADAS
let map = null;
let marker = null;
const COMMUNITY_CENTER = [-21.9325, -63.6345];

// ✅ INICIALIZACIÓN ROBUSTA
function initializeMap() {
    // Solo inicializar si no existe
    if (map) return;
    
    map = L.map('locationMap').setView(COMMUNITY_CENTER, 16);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // ✅ EVENTO DE CLIC ÚNICO Y ROBUSTO
    map.on('click', function(e) {
        handleMapClick(e.latlng);
    });

    // ✅ CARGAR COORDENADAS EXISTENTES AL INICIAR
    loadExistingCoordinates();
}

// ✅ MANEJADOR PRINCIPAL DE CLIC EN MAPA
function handleMapClick(latlng) {
    // Siempre crear nuevo marcador (la función se encarga de limpiar el anterior)
    createMarker(latlng);
    updateCoordinateFields(latlng.lat, latlng.lng);
}

// ✅ CREAR MARCADOR (SIEMPRE LIMPIA EL ANTERIOR)
function createMarker(latlng) {
    // Limpiar marcador existente
    if (marker) {
        map.removeLayer(marker);
    }
    
    // Crear nuevo marcador
    marker = L.marker(latlng, {
        draggable: true
    }).addTo(map);

    // ✅ ACTUALIZAR COORDENADAS AL ARRASTRAR
    marker.on('dragend', function(e) {
        const newPos = marker.getLatLng();
        updateCoordinateFields(newPos.lat, newPos.lng);
        updateMarkerPopup(newPos);
    });

    updateMarkerPopup(latlng);
}

// ✅ ACTUALIZAR POPUP DEL MARCADOR
function updateMarkerPopup(latlng) {
    if (marker) {
        marker.bindPopup(
            `📍 Ubicación seleccionada<br>
            <strong>Lat:</strong> ${latlng.lat.toFixed(6)}<br>
            <strong>Lng:</strong> ${latlng.lng.toFixed(6)}`
        ).openPopup();
    }
}

// ✅ ACTUALIZAR CAMPOS DE COORDENADAS
function updateCoordinateFields(lat, lng) {
    document.getElementById('latitud').value = lat.toFixed(8);
    document.getElementById('longitud').value = lng.toFixed(8);
}

// ✅ CARGAR COORDENADAS EXISTENTES (SOLO AL INICIAR)
function loadExistingCoordinates() {
    const lat = document.getElementById('latitud').value;
    const lng = document.getElementById('longitud').value;
    
    if (lat && lng) {
        const latLng = [parseFloat(lat), parseFloat(lng)];
        createMarker(latLng);
        map.setView(latLng, 16);
    }
}

// ✅ REINICIAR AL CENTRO DE LA COMUNIDAD
function resetToCommunityCenter() {
    map.setView(COMMUNITY_CENTER, 16);
    // No crear marcador aquí - solo centrar
}

// ✅ LIMPIAR UBICACIÓN COMPLETAMENTE
function clearLocation() {
    if (marker) {
        map.removeLayer(marker);
        marker = null;
    }
    document.getElementById('latitud').value = '';
    document.getElementById('longitud').value = '';
    resetToCommunityCenter();
}

// ✅ INICIALIZAR CUANDO EL DOCUMENTO ESTÉ LISTO
document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
});
</script>
@endpush