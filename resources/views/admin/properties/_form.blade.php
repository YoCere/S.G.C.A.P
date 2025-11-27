{{-- resources/views/admin/properties/_form.blade.php --}}
@csrf
<div class="form-row">
  <div class="form-group col-md-6">
    <label>Cliente</label>
    <input list="clientes-list" name="cliente_id" class="form-control" 
           placeholder="Escriba el nombre del cliente..." 
           value="{{ old('cliente_id', $property->cliente_id ?? '') }}" required>
    <datalist id="clientes-list">
      @foreach ($clients as $c)
        <option value="{{ $c->id }}">
          {{ $c->nombre }} 
          @if($c->ci) (CI: {{ $c->ci }}) @endif 
          @if($c->codigo_cliente) - Código: {{ $c->codigo_cliente }} @endif
        </option>
      @endforeach
    </datalist>
    @error('cliente_id') <span class="text-danger small">{{ $message }}</span> @enderror
  </div>
</div>

  <div class="form-group col-md-6">
    <label>Tarifa</label>
    <select name="tarifa_id" class="form-control" required>
      <option value="">— Seleccione —</option>
      @foreach ($tariffs as $t)
        <option value="{{ $t->id }}" @selected(old('tarifa_id', $property->tarifa_id ?? null) == $t->id)>
          {{ $t->nombre }} (Bs {{ number_format($t->precio_mensual,2) }})
          @if(!$t->activo) - INACTIVA @endif
        </option>
      @endforeach
    </select>
    @error('tarifa_id') <span class="text-danger small">{{ $message }}</span> @enderror
  </div>
</div>

<div class="form-group">
  <label>Referencia</label>
  <input type="text" name="referencia" class="form-control" required
         value="{{ old('referencia', $property->referencia ?? '') }}"
         placeholder="Ej: Casa color azul con portón negro">
  @error('referencia') <span class="text-danger small">{{ $message }}</span> @enderror
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
    <option value="Fatima" @selected(old('barrio', $property->barrio ?? null) == 'Fatima')>Fatima</option>
    <option value="Arboleda" @selected(old('barrio', $property->barrio ?? null) == 'Arboleda')>Arboleda</option>
  </select>
  @error('barrio') <span class="text-danger small">{{ $message }}</span> @enderror
</div>

{{-- MAPA INTERACTIVO MEJORADO --}}
<div class="form-group">
  <label>Seleccionar ubicación en el mapa</label>
  <div class="alert alert-info py-2">
    <small class="d-flex align-items-center">
      <i class="fas fa-info-circle mr-2"></i> 
      Haz clic en el mapa para establecer las coordenadas. Puedes arrastrar el marcador para ajustar.
    </small>
  </div>
  
  {{-- Mapa --}}
  <div id="locationMap" style="height: 300px; width: 100%; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px;"></div>
  
  {{-- Botones de acción --}}
  <div class="d-flex flex-wrap gap-2 mb-3">
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetToCommunityCenter()">
      <i class="fas fa-home mr-1"></i> Centrar mapa
    </button>
    <button type="button" class="btn btn-outline-warning btn-sm" onclick="clearLocation()">
      <i class="fas fa-times mr-1"></i> Limpiar ubicación
    </button>
  </div>
</div>

<div class="form-row">
  <div class="form-group col-md-6">
    <label>Latitud</label>
    <div class="input-group input-group-sm">
      <input type="number" step="0.00000001" name="latitud" id="latitud" class="form-control coordinates-input"
             value="{{ old('latitud', $property->latitud ?? '') }}"
             placeholder="Se autocompletará con el mapa" readonly>
      <div class="input-group-append">
        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
      </div>
    </div>
    @error('latitud') <span class="text-danger small">{{ $message }}</span> @enderror
  </div>
  
  <div class="form-group col-md-6">
    <label>Longitud</label>
    <div class="input-group input-group-sm">
      <input type="number" step="0.00000001" name="longitud" id="longitud" class="form-control coordinates-input"
             value="{{ old('longitud', $property->longitud ?? '') }}"
             placeholder="Se autocompletará con el mapa" readonly>
      <div class="input-group-append">
        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
      </div>
    </div>
    @error('longitud') <span class="text-danger small">{{ $message }}</span> @enderror
  </div>
</div>

{{-- 🆕 CAMPO DE ESTADO CON LÓGICA MEJORADA --}}
<div class="form-group">
  <label>Estado</label>
  
  {{-- CREACIÓN: Mostrar como campo oculto con información --}}
  @if(!isset($property) || !$property->id)
    <div class="alert alert-info py-2">
      <small class="d-flex align-items-center">
        <i class="fas fa-info-circle mr-2"></i> 
        Estado: <strong>Pendiente de Conexión</strong> - El operador realizará la instalación física
      </small>
    </div>
    <input type="hidden" name="estado" value="pendiente_conexion">
  
  {{-- EDICIÓN: Mostrar select con estados permitidos según rol --}}
  @else
    <select name="estado" class="form-control" required>
      @php
        $currentEstado = old('estado', $property->estado ?? 'pendiente_conexion');
        $user = auth()->user();
      @endphp
      
      {{-- Admin: Todos los estados --}}
      @if($user->hasRole('admin'))
        @foreach (['pendiente_conexion', 'activo', 'inactivo', 'corte_pendiente', 'cortado'] as $op)
          <option value="{{ $op }}" @selected($currentEstado == $op)>
            {{ ucfirst(str_replace('_', ' ', $op)) }}
          </option>
        @endforeach
      
      {{-- Secretaria: Puede activar desde pendiente_conexion --}}
      @elseif($user->hasRole('secretaria'))
        @if($property->estado == 'pendiente_conexion')
          <option value="pendiente_conexion" @selected($currentEstado == 'pendiente_conexion')>
            Pendiente de Conexión
          </option>
          <option value="activo" @selected($currentEstado == 'activo')>
            Activo
          </option>
        @else
          @foreach (['activo', 'inactivo', 'corte_pendiente'] as $op)
            <option value="{{ $op }}" @selected($currentEstado == $op)>
              {{ ucfirst(str_replace('_', ' ', $op)) }}
            </option>
          @endforeach
        @endif
      
      {{-- Operador: Solo puede marcar como cortado desde pendiente_conexion --}}
      @elseif($user->hasRole('operador'))
        @if($property->estado == 'pendiente_conexion')
          <option value="pendiente_conexion" @selected($currentEstado == 'pendiente_conexion')>
            Pendiente de Conexión
          </option>
          <option value="cortado" @selected($currentEstado == 'cortado')>
            Cortado (Instalación Completada)
          </option>
        @else
          <option value="{{ $property->estado }}" selected>
            {{ ucfirst(str_replace('_', ' ', $property->estado)) }}
          </option>
        @endif
      
      {{-- Por defecto: Solo estado actual --}}
      @else
        <option value="{{ $property->estado }}" selected>
          {{ ucfirst(str_replace('_', ' ', $property->estado)) }}
        </option>
      @endif
    </select>
    
    {{-- Información adicional para operador --}}
    @if(auth()->user()->hasRole('operador') && $property->estado == 'pendiente_conexion')
      <small class="form-text text-muted">
        <i class="fas fa-tools mr-1"></i> 
        Marque como "Cortado" después de completar la instalación física
      </small>
    @endif
  @endif
  
  @error('estado') <span class="text-danger small">{{ $message }}</span> @enderror
</div>

<div class="d-flex flex-column flex-sm-row gap-2">
  <button class="btn btn-primary btn-sm">
    <i class="fas fa-save mr-1"></i> Guardar Propiedad
  </button>
  <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary btn-sm">
    <i class="fas fa-times mr-1"></i> Cancelar
  </a>
</div>

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
#locationMap { cursor: crosshair; }
.leaflet-popup-content { font-size: 14px; }
.coordinates-input { background-color: #f8f9fa; }
.select2-container { width: 100% !important; }
</style>
@endpush

@push('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
// ✅ VARIABLES GLOBALES
let map = null;
let marker = null;
const COMMUNITY_CENTER = [-21.9325, -63.6345];

// ✅ INICIALIZACIÓN ROBUSTA
function initializeMap() {
    if (map) return;
    
    map = L.map('locationMap').setView(COMMUNITY_CENTER, 16);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    // ✅ EVENTO DE CLIC ÚNICO
    map.on('click', function(e) {
        handleMapClick(e.latlng);
    });

    // ✅ CARGAR COORDENADAS EXISTENTES
    loadExistingCoordinates();
}

// ✅ MANEJADOR PRINCIPAL DE CLIC EN MAPA
function handleMapClick(latlng) {
    createMarker(latlng);
    updateCoordinateFields(latlng.lat, latlng.lng);
}

// ✅ CREAR MARCADOR
function createMarker(latlng) {
    if (marker) {
        map.removeLayer(marker);
    }
    
    marker = L.marker(latlng, {
        draggable: true
    }).addTo(map);

    // ✅ ACTUALIZAR AL ARRASTRAR
    marker.on('dragend', function(e) {
        const newPos = marker.getLatLng();
        updateCoordinateFields(newPos.lat, newPos.lng);
        updateMarkerPopup(newPos);
    });

    updateMarkerPopup(latlng);
}

// ✅ ACTUALIZAR POPUP
function updateMarkerPopup(latlng) {
    if (marker) {
        marker.bindPopup(
            `📍 Ubicación seleccionada<br>
            <strong>Lat:</strong> ${latlng.lat.toFixed(6)}<br>
            <strong>Lng:</strong> ${latlng.lng.toFixed(6)}`
        ).openPopup();
    }
}

// ✅ ACTUALIZAR CAMPOS
function updateCoordinateFields(lat, lng) {
    document.getElementById('latitud').value = lat.toFixed(8);
    document.getElementById('longitud').value = lng.toFixed(8);
}

// ✅ CARGAR COORDENADAS EXISTENTES
function loadExistingCoordinates() {
    const lat = document.getElementById('latitud').value;
    const lng = document.getElementById('longitud').value;
    
    if (lat && lng) {
        const latLng = [parseFloat(lat), parseFloat(lng)];
        createMarker(latLng);
        map.setView(latLng, 16);
    }
}

// ✅ REINICIAR AL CENTRO
function resetToCommunityCenter() {
    map.setView(COMMUNITY_CENTER, 16);
}

// ✅ LIMPIAR UBICACIÓN
function clearLocation() {
    if (marker) {
        map.removeLayer(marker);
        marker = null;
    }
    document.getElementById('latitud').value = '';
    document.getElementById('longitud').value = '';
    resetToCommunityCenter();
}

// ✅ INICIALIZAR
document.addEventListener('DOMContentLoaded', function() {
    initializeMap();
    // Inicializar Select2
    $('.select2').select2({
        placeholder: "Seleccione un cliente",
        allowClear: true
    });
});
</script>
@endpush