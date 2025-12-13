@csrf

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Cliente</label>
        <input list="clientes-list" name="cliente_id" id="cliente-input"
            class="form-control @error('cliente_id') is-invalid @enderror"
            placeholder="Escriba el nombre del cliente..."
            value="{{ old('cliente_id', $property->cliente_id ?? '') }}"
            required
            autocomplete="off">
        <datalist id="clientes-list">
            @foreach ($clients as $c)
                <option value="{{ $c->id }}">
                    {{ $c->nombre }}
                    @if($c->ci) (CI: {{ $c->ci }}) @endif
                    @if($c->codigo_cliente) - Código: {{ $c->codigo_cliente }} @endif
                </option>
            @endforeach
        </datalist>
        @error('cliente_id') 
            <span class="text-danger small">{{ $message }}</span> 
        @enderror
        <small class="text-muted">Escriba el nombre del cliente y seleccione de la lista</small>
    </div>

    <div class="form-group col-md-6">
        <label>Tarifa</label>
        <select name="tarifa_id" class="form-control @error('tarifa_id') is-invalid @enderror" required>
            <option value="">— Seleccione —</option>
            @foreach ($tariffs as $t)
                <option value="{{ $t->id }}"
                    @selected(old('tarifa_id', $property->tarifa_id ?? null) == $t->id)>
                    {{ $t->nombre }} (Bs {{ number_format($t->precio_mensual,2) }})
                    @if(!$t->activo) - INACTIVA @endif
                </option>
            @endforeach
        </select>
        @error('tarifa_id') 
            <span class="text-danger small">{{ $message }}</span> 
        @enderror
    </div>
</div>

<div class="form-group">
    <label>Referencia</label>
    <input type="text" name="referencia" id="referencia"
        class="form-control @error('referencia') is-invalid @enderror"
        value="{{ old('referencia', $property->referencia ?? '') }}"
        placeholder="Ej: Casa color azul con portón negro"
        required
        oninput="this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÑ\s0-9.,\-()]/g, '')"
        maxlength="255">
    @error('referencia') 
        <span class="text-danger small">{{ $message }}</span> 
    @enderror
    <small class="text-muted">Solo letras, números, espacios y los caracteres .,-()</small>
</div>

<div class="form-group">
    <label>Barrio</label>
    <select name="barrio" class="form-control @error('barrio') is-invalid @enderror" required>
        <option value="">— Seleccione barrio —</option>
        <option value="Centro" @selected(old('barrio', $property->barrio ?? null) == 'Centro')>Centro</option>
        <option value="Aroma" @selected(old('barrio', $property->barrio ?? null) == 'Aroma')>Aroma</option>
        <option value="Los Valles" @selected(old('barrio', $property->barrio ?? null) == 'Los Valles')>Los Valles</option>
        <option value="Caipitandy" @selected(old('barrio', $property->barrio ?? null) == 'Caipitandy')>Caipitandy</option>
        <option value="Primavera" @selected(old('barrio', $property->barrio ?? null) == 'Primavera')>Primavera</option>
        <option value="Fatima" @selected(old('barrio', $property->barrio ?? null) == 'Fatima')>Fatima</option>
        <option value="Arboleda" @selected(old('barrio', $property->barrio ?? null) == 'Arboleda')>Arboleda</option>
    </select>
    @error('barrio') 
        <span class="text-danger small">{{ $message }}</span> 
    @enderror
</div>

{{-- MAPA ULTRA SIMPLE Y FUNCIONAL --}}
<div class="form-group">
    <label>Ubicación en el mapa</label>
    
    <div class="alert alert-info py-2 mb-2">
        <small>
            <i class="fas fa-info-circle mr-1"></i>
            <strong>Haz clic en el mapa para seleccionar la ubicación.</strong> Puedes arrastrar el marcador para ajustar.
        </small>
    </div>
    
    {{-- Contenedor del mapa --}}
    <div id="mapContainer" style="position: relative;">
        <div id="map" style="height: 400px; width: 100%; border: 2px solid #ccc; border-radius: 5px; background: #f8f9fa;"></div>
        <div id="mapLoading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando mapa...</span>
            </div>
        </div>
    </div>
    
    {{-- Botones de acción --}}
    <div class="d-flex flex-wrap gap-2 my-3">
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="centerMap()">
            <i class="fas fa-home mr-1"></i> Centrar
        </button>
        <button type="button" class="btn btn-sm btn-outline-warning" onclick="clearMap()">
            <i class="fas fa-trash mr-1"></i> Limpiar
        </button>
        <button type="button" class="btn btn-sm btn-outline-success" onclick="getMyLocation()">
            <i class="fas fa-location-arrow mr-1"></i> Mi ubicación
        </button>
    </div>
    
    {{-- Previsualización de coordenadas --}}
    <div id="coordsPreview" class="alert alert-light border mb-3" style="display: none;">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <small><i class="fas fa-map-marker-alt text-primary mr-1"></i> 
                <strong>Coordenadas seleccionadas:</strong> 
                <span id="currentCoords">-</span></small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-info" onclick="copyCoords()">
                <i class="fas fa-copy"></i>
            </button>
        </div>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Latitud</label>
        <div class="input-group input-group-sm">
            <input type="number" step="0.000001" name="latitud" id="latitud"
                class="form-control @error('latitud') is-invalid @enderror"
                value="{{ old('latitud', $property->latitud ?? '') }}"
                placeholder="Haz clic en el mapa"
                required>
            <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-globe-americas"></i></span>
            </div>
        </div>
        @error('latitud') 
            <span class="text-danger small">{{ $message }}</span> 
        @enderror
        <small class="text-muted">Ejemplo: -21.932500</small>
    </div>
    
    <div class="form-group col-md-6">
        <label>Longitud</label>
        <div class="input-group input-group-sm">
            <input type="number" step="0.000001" name="longitud" id="longitud"
                class="form-control @error('longitud') is-invalid @enderror"
                value="{{ old('longitud', $property->longitud ?? '') }}"
                placeholder="Haz clic en el mapa"
                required>
            <div class="input-group-append">
                <span class="input-group-text"><i class="fas fa-globe-americas"></i></span>
            </div>
        </div>
        @error('longitud') 
            <span class="text-danger small">{{ $message }}</span> 
        @enderror
        <small class="text-muted">Ejemplo: -63.634500</small>
    </div>
</div>

{{-- ESTADO --}}
<div class="form-group">
    <label>Estado</label>
    @if(!isset($property) || !$property->id)
        <div class="alert alert-info py-2">
            <small class="d-flex align-items-center">
                <i class="fas fa-info-circle mr-2"></i>
                Estado: <strong>Pendiente de Conexión</strong> - El operador realizará la instalación física
            </small>
        </div>
        <input type="hidden" name="estado" value="pendiente_conexion">
    @else
        <select name="estado" class="form-control @error('estado') is-invalid @enderror" required>
            @php
                $currentEstado = old('estado', $property->estado ?? 'pendiente_conexion');
                $user = auth()->user();
            @endphp
            
            @if($user->hasRole('admin'))
                @foreach (['pendiente_conexion', 'activo', 'inactivo', 'corte_pendiente', 'cortado'] as $op)
                    <option value="{{ $op }}" @selected($currentEstado == $op)>
                        {{ ucfirst(str_replace('_', ' ', $op)) }}
                    </option>
                @endforeach
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
            @else
                <option value="{{ $property->estado }}" selected>
                    {{ ucfirst(str_replace('_', ' ', $property->estado)) }}
                </option>
            @endif
        </select>
        
        @if(auth()->user()->hasRole('operador') && $property->estado == 'pendiente_conexion')
            <small class="form-text text-muted">
                <i class="fas fa-tools mr-1"></i>
                Marque como "Cortado" después de completar la instalación física
            </small>
        @endif
    @endif
    @error('estado') 
        <span class="text-danger small">{{ $message }}</span> 
    @enderror
</div>

<div class="d-flex flex-column flex-sm-row gap-2 mt-4">
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="fas fa-save mr-1"></i> Guardar Propiedad
    </button>
    <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-times mr-1"></i> Cancelar
    </a>
</div>

@push('css')
<style>
    /* ESTILOS MÍNIMOS PARA EL MAPA */
    #map {
        min-height: 400px;
        z-index: 1;
    }
    
    .leaflet-container {
        font-family: inherit;
        font-size: 14px;
    }
    
    /* Asegurar que el mapa esté sobre otros elementos */
    .leaflet-pane {
        z-index: 400 !important;
    }
    
    .leaflet-top, .leaflet-bottom {
        z-index: 1000 !important;
    }
    
    /* Oculta el texto de carga cuando el mapa esté listo */
    .map-loaded #mapLoading {
        display: none !important;
    }
</style>
@endpush

@push('scripts')
<script>
// ============================
// CONFIGURACIÓN BÁSICA
// ============================
const DEFAULT_CENTER = [-21.9325, -63.6345];
const DEFAULT_ZOOM = 16;
let map = null;
let marker = null;

// ============================
// CARGAR LEAFLET DINÁMICAMENTE
// ============================
function loadLeaflet() {
    return new Promise((resolve, reject) => {
        // Si ya está cargado, continuar
        if (window.L) {
            resolve();
            return;
        }
        
        // Mostrar loading
        document.getElementById('mapLoading').style.display = 'block';
        
        // Cargar CSS
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        link.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=';
        link.crossOrigin = '';
        document.head.appendChild(link);
        
        // Cargar JS
        const script = document.createElement('script');
        script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
        script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=';
        script.crossOrigin = '';
        
        script.onload = () => {
            document.getElementById('mapLoading').style.display = 'none';
            resolve();
        };
        
        script.onerror = () => {
            document.getElementById('mapLoading').style.display = 'none';
            reject(new Error('No se pudo cargar Leaflet'));
        };
        
        document.head.appendChild(script);
    });
}

// ============================
// INICIALIZAR MAPA
// ============================
async function initMap() {
    try {
        // 1. Cargar Leaflet
        await loadLeaflet();
        
        // 2. Obtener contenedor
        const mapElement = document.getElementById('map');
        if (!mapElement) {
            throw new Error('Elemento del mapa no encontrado');
        }
        
        // 3. Crear mapa
        map = L.map('map').setView(DEFAULT_CENTER, DEFAULT_ZOOM);
        
        // 4. Añadir capa base
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(map);
        
        // 5. Añadir evento de clic
        map.on('click', function(e) {
            placeMarker(e.latlng);
        });
        
        // 6. Marcar como cargado
        document.getElementById('mapContainer').classList.add('map-loaded');
        
        // 7. Cargar ubicación existente
        loadExistingLocation();
        
        // 8. Forzar actualización de tamaño
        setTimeout(() => {
            if (map) {
                map.invalidateSize();
            }
        }, 100);
        
        console.log('Mapa inicializado correctamente');
        
    } catch (error) {
        console.error('Error al inicializar el mapa:', error);
        document.getElementById('map').innerHTML = `
            <div class="alert alert-danger m-3">
                <h6>Error al cargar el mapa</h6>
                <p>${error.message}</p>
                <button class="btn btn-sm btn-primary" onclick="initMap()">Reintentar</button>
            </div>
        `;
    }
}

// ============================
// COLOCAR MARCADOR
// ============================
function placeMarker(latlng) {
    if (!map) return;
    
    // Remover marcador anterior
    if (marker) {
        map.removeLayer(marker);
    }
    
    // Crear nuevo marcador
    marker = L.marker(latlng, {
        draggable: true,
        title: 'Ubicación seleccionada'
    }).addTo(map);
    
    // Popup
    marker.bindPopup(`
        <div style="font-size: 14px; padding: 5px;">
            <strong>📍 Ubicación</strong><br>
            Lat: ${latlng.lat.toFixed(6)}<br>
            Lng: ${latlng.lng.toFixed(6)}
        </div>
    `).openPopup();
    
    // Actualizar al arrastrar
    marker.on('dragend', function() {
        const pos = marker.getLatLng();
        updateFormFields(pos.lat, pos.lng);
    });
    
    // Actualizar campos del formulario
    updateFormFields(latlng.lat, latlng.lng);
}

// ============================
// ACTUALIZAR CAMPOS DEL FORMULARIO
// ============================
function updateFormFields(lat, lng) {
    // Campos de entrada
    document.getElementById('latitud').value = lat.toFixed(6);
    document.getElementById('longitud').value = lng.toFixed(6);
    
    // Previsualización
    document.getElementById('currentCoords').textContent = 
        `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    document.getElementById('coordsPreview').style.display = 'block';
    
    // Feedback visual
    showToast('success', 'Ubicación seleccionada');
}

// ============================
// CARGAR UBICACIÓN EXISTENTE
// ============================
function loadExistingLocation() {
    if (!map) return;
    
    const lat = document.getElementById('latitud').value;
    const lng = document.getElementById('longitud').value;
    
    if (lat && lng) {
        const latNum = parseFloat(lat);
        const lngNum = parseFloat(lng);
        
        if (!isNaN(latNum) && !isNaN(lngNum)) {
            const position = [latNum, lngNum];
            placeMarker(position);
            map.setView(position, DEFAULT_ZOOM);
        }
    }
}

// ============================
// FUNCIONES DE CONTROL
// ============================
function centerMap() {
    if (map) {
        map.setView(DEFAULT_CENTER, DEFAULT_ZOOM);
        showToast('info', 'Mapa centrado');
    }
}

function clearMap() {
    if (marker && map) {
        map.removeLayer(marker);
        marker = null;
    }
    
    document.getElementById('latitud').value = '';
    document.getElementById('longitud').value = '';
    document.getElementById('coordsPreview').style.display = 'none';
    
    showToast('warning', 'Ubicación limpiada');
}

function getMyLocation() {
    if (!navigator.geolocation) {
        showToast('error', 'Geolocalización no soportada');
        return;
    }
    
    showToast('info', 'Obteniendo tu ubicación...');
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const latlng = [position.coords.latitude, position.coords.longitude];
            
            if (map) {
                placeMarker(latlng);
                map.setView(latlng, 16);
                showToast('success', 'Ubicación actual obtenida');
            }
        },
        function(error) {
            let message = 'No se pudo obtener tu ubicación. ';
            switch(error.code) {
                case 1: message += 'Permiso denegado.'; break;
                case 2: message += 'Ubicación no disponible.'; break;
                case 3: message += 'Tiempo agotado.'; break;
                default: message += 'Error desconocido.';
            }
            showToast('error', message);
        }
    );
}

function copyCoords() {
    const coords = document.getElementById('currentCoords').textContent;
    if (coords && coords !== '-') {
        navigator.clipboard.writeText(coords)
            .then(() => showToast('success', 'Coordenadas copiadas'))
            .catch(() => showToast('error', 'No se pudo copiar'));
    }
}

// ============================
// NOTIFICACIONES
// ============================
function showToast(type, message) {
    // Crear toast simple
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show`;
    toast.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 250px;
        max-width: 500px;
        animation: fadeIn 0.3s;
    `;
    
    toast.innerHTML = `
        ${message}
        <button type="button" class="close" onclick="this.parentElement.remove()">
            <span>&times;</span>
        </button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-eliminar
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 3000);
}

// ============================
// INICIALIZACIÓN AL CARGAR LA PÁGINA
// ============================
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar el mapa con retraso para evitar conflictos
    setTimeout(initMap, 500);
    
    // Si el mapa está en un tab, reiniciar cuando se muestre
    $(document).on('shown.bs.tab', function() {
        setTimeout(() => {
            if (map) {
                map.invalidateSize();
            } else {
                initMap();
            }
        }, 300);
    });
});

// ============================
// EXPORTAR FUNCIONES PARA BOTONES HTML
// ============================
window.centerMap = centerMap;
window.clearMap = clearMap;
window.getMyLocation = getMyLocation;
window.copyCoords = copyCoords;
</script>
@endpush