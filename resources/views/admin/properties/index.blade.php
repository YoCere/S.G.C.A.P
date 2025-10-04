{{-- resources/views/admin/properties/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Propiedades')

@section('content_header')
  <h1>Lista de Propiedades</h1>
@stop

@section('content')
  @if (session('info'))
    <div class="alert alert-success alert-dismissible fade show">
      <strong>{{ session('info') }}</strong>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
      <strong>{{ session('error') }}</strong>
      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
  @endif

  <!-- FILTROS AVANZADOS -->
  <div class="card card-outline card-primary">
    <div class="card-header">
      <h3 class="card-title">
        <i class="fas fa-filter mr-2"></i>Filtros de Búsqueda
      </h3>
      <div class="card-tools">
        <button type="button" class="btn btn-tool" data-card-widget="collapse">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    </div>
    <div class="card-body">
      <form action="{{ route('admin.properties.index') }}" method="GET" id="filterForm">
        <div class="row">
          <!-- Búsqueda general -->
          <div class="col-md-4">
            <div class="form-group">
              <label for="search">Búsqueda General</label>
              <input type="text" name="search" id="search" class="form-control form-control-sm" 
                     placeholder="Referencia, cliente, cédula..." 
                     value="{{ request('search') }}">
            </div>
          </div>

          <!-- Filtro por Estado -->
          <div class="col-md-3">
            <div class="form-group">
              <label for="estado">Estado de Propiedad</label>
              <select name="estado" id="estado" class="form-control form-control-sm">
                <option value="">Todos los estados</option>
                <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                <option value="corte_pendiente" {{ request('estado') == 'corte_pendiente' ? 'selected' : '' }}>Corte Pendiente</option>
                <option value="cortado" {{ request('estado') == 'cortado' ? 'selected' : '' }}>Cortado</option>
                <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
              </select>
            </div>
          </div>

          <!-- Filtro por Barrio -->
          <div class="col-md-3">
            <div class="form-group">
              <label for="barrio">Barrio</label>
              <select name="barrio" id="barrio" class="form-control form-control-sm">
                <option value="">Todos los barrios</option>
                <option value="Centro" {{ request('barrio') == 'Centro' ? 'selected' : '' }}>Centro</option>
                <option value="Aroma" {{ request('barrio') == 'Aroma' ? 'selected' : '' }}>Aroma</option>
                <option value="Los Valles" {{ request('barrio') == 'Los Valles' ? 'selected' : '' }}>Los Valles</option>
                <option value="Caipitandy" {{ request('barrio') == 'Caipitandy' ? 'selected' : '' }}>Caipitandy</option>
                <option value="Primavera" {{ request('barrio') == 'Primavera' ? 'selected' : '' }}>Primavera</option>
                <option value="Arboleda" {{ request('barrio') == 'Arboleda' ? 'selected' : '' }}>Arboleda</option>
              </select>
            </div>
          </div>

          <!-- Filtro por Tarifa -->
          <div class="col-md-2">
            <div class="form-group">
              <label for="tarifa_id">Tarifa</label>
              <select name="tarifa_id" id="tarifa_id" class="form-control form-control-sm">
                <option value="">Todas las tarifas</option>
                @foreach($tariffs as $tarifa)
                  <option value="{{ $tarifa->id }}" {{ request('tarifa_id') == $tarifa->id ? 'selected' : '' }}>
                    {{ $tarifa->nombre }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <!-- Filtro por Cliente -->
          <div class="col-md-6">
            <div class="form-group">
              <label for="cliente_id">Cliente Específico</label>
              <select name="cliente_id" id="cliente_id" class="form-control form-control-sm">
                <option value="">Todos los clientes</option>
                @foreach($clients as $cliente)
                  <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                    {{ $cliente->nombre }} @if($cliente->ci) ({{ $cliente->ci }}) @endif
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <!-- Filtro por Estado de Cuenta del Cliente -->
          <div class="col-md-3">
            <div class="form-group">
              <label for="estado_cuenta">Estado de Cuenta</label>
              <select name="estado_cuenta" id="estado_cuenta" class="form-control form-control-sm">
                <option value="">Todos</option>
                <option value="activo" {{ request('estado_cuenta') == 'activo' ? 'selected' : '' }}>Cuenta Activa</option>
                <option value="inactivo" {{ request('estado_cuenta') == 'inactivo' ? 'selected' : '' }}>Cuenta Inactiva</option>
              </select>
            </div>
          </div>

          <!-- Ordenamiento -->
          <div class="col-md-3">
            <div class="form-group">
              <label for="orden">Ordenar por</label>
              <select name="orden" id="orden" class="form-control form-control-sm">
                <option value="reciente" {{ request('orden') == 'reciente' ? 'selected' : '' }}>Más recientes</option>
                <option value="antiguo" {{ request('orden') == 'antiguo' ? 'selected' : '' }}>Más antiguos</option>
                <option value="referencia" {{ request('orden') == 'referencia' ? 'selected' : '' }}>Referencia (A-Z)</option>
                <option value="cliente" {{ request('orden') == 'cliente' ? 'selected' : '' }}>Cliente (A-Z)</option>
                <option value="barrio" {{ request('orden') == 'barrio' ? 'selected' : '' }}>Barrio</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="row">
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="fas fa-search mr-1"></i> Aplicar Filtros
            </button>
            <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary btn-sm">
              <i class="fas fa-undo mr-1"></i> Limpiar Filtros
            </a>
            <button type="button" class="btn btn-info btn-sm" id="toggleFilters">
              <i class="fas fa-eye mr-1"></i> Mostrar/Ocultar Filtros
            </button>
            
            @if(request()->anyFilled(['search', 'estado', 'barrio', 'tarifa_id', 'cliente_id', 'estado_cuenta', 'orden']))
              <span class="badge badge-success ml-2">
                <i class="fas fa-filter mr-1"></i>Filtros activos
              </span>
            @endif
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- ESTADÍSTICAS RÁPIDAS -->
  <div class="row mb-4">
    <div class="col-md-2 col-sm-6">
      <div class="info-box bg-gradient-info">
        <span class="info-box-icon"><i class="fas fa-home"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Total</span>
          <span class="info-box-number">{{ $totalPropiedades }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-sm-6">
      <div class="info-box bg-gradient-success">
        <span class="info-box-icon"><i class="fas fa-check-circle"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Activas</span>
          <span class="info-box-number">{{ $estadisticas['activas'] ?? 0 }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-sm-6">
      <div class="info-box bg-gradient-warning">
        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Corte Pendiente</span>
          <span class="info-box-number">{{ $estadisticas['corte_pendiente'] ?? 0 }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-sm-6">
      <div class="info-box bg-gradient-danger">
        <span class="info-box-icon"><i class="fas fa-ban"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Cortadas</span>
          <span class="info-box-number">{{ $estadisticas['cortadas'] ?? 0 }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-sm-6">
      <div class="info-box bg-gradient-secondary">
        <span class="info-box-icon"><i class="fas fa-map-marker-alt"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Con Ubicación</span>
          <span class="info-box-number">{{ $estadisticas['con_ubicacion'] ?? 0 }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-sm-6">
      <div class="info-box bg-gradient-primary">
        <span class="info-box-icon"><i class="fas fa-users"></i></span>
        <div class="info-box-content">
          <span class="info-box-text">Clientes Activos</span>
          <span class="info-box-number">{{ $estadisticas['clientes_activos'] ?? 0 }}</span>
        </div>
      </div>
    </div>
  </div>

  <!-- TABLA DE PROPIEDADES -->
  <div class="card">
    <div class="card-header">
      <div class="row align-items-center">
        <div class="col-md-6">
          <a class="btn btn-primary btn-sm" href="{{ route('admin.properties.create') }}">
            <i class="fas fa-plus-circle mr-1"></i>Nueva Propiedad
          </a>
          <a href="{{ route('admin.cortes.pendientes') }}" class="btn btn-warning btn-sm ml-2">
            <i class="fas fa-clock mr-1"></i>Ver Cortes Pendientes
          </a>
        </div>
        <div class="col-md-6 text-right">
          <span class="badge badge-light">
            Mostrando {{ $properties->count() }} de {{ $properties->total() }} propiedades
          </span>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      @if($properties->count())
        <div class="table-responsive">
          <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
              <tr>
                <th width="50">#</th>
                <th>Cliente</th>
                <th>Referencia</th>
                <th>Barrio</th>
                <th>Tarifa</th>
                <th width="100">Precio</th>
                <th width="120">Estado</th>
                <th width="300" class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              @foreach($properties as $p)
                <tr>
                  <td class="text-muted">{{ $p->id }}</td>
                  <td>
                    <div class="d-flex flex-column">
                      <strong class="text-primary">{{ $p->client->nombre ?? 'N/A' }}</strong>
                      <small class="text-muted">{{ $p->client->ci ?? 'Sin CI' }}</small>
                      <small class="text-{{ $p->client->estado_cuenta == 'activo' ? 'success' : 'warning' }}">
                        {{ $p->client->estado_cuenta ?? 'N/A' }}
                      </small>
                    </div>
                  </td>
                  <td>
                    <strong>{{ $p->referencia }}</strong>
                    @if($p->estado === 'cortado')
                      <span class="badge badge-danger ml-1">CORTADO</span>
                    @elseif($p->estado === 'corte_pendiente')
                      <span class="badge badge-warning ml-1">PENDIENTE</span>
                    @endif
                  </td>
                  <td>
                    @if($p->barrio)
                      <span class="badge badge-light border">{{ $p->barrio }}</span>
                    @else
                      <span class="text-muted small">—</span>
                    @endif
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <span>{{ $p->tariff->nombre ?? '—' }}</span>
                      @if($p->tariff && !$p->tariff->activo)
                        <span class="badge badge-warning ml-1 small" title="Tarifa inactiva">INACTIVA</span>
                      @endif
                    </div>
                  </td>
                  <td>
                    <strong class="text-success">Bs {{ number_format($p->tariff->precio_mensual ?? 0, 2) }}</strong>
                  </td>
                  <td>
                    @if($p->estado === 'activo')
                      <span class="badge badge-success">Activo</span>
                    @elseif($p->estado === 'corte_pendiente')
                      <span class="badge badge-warning">Corte Pendiente</span>
                    @elseif($p->estado === 'cortado')
                      <span class="badge badge-danger">Cortado</span>
                    @else
                      <span class="badge badge-secondary">Inactivo</span>
                    @endif
                  </td>
                  <td>
                    {{-- Ubicación --}}
                    @if($p->latitud && $p->longitud)
                      <button class="btn btn-info btn-sm mb-1"
                              data-toggle="modal"
                              data-target="#mapModal"
                              data-lat="{{ $p->latitud }}"
                              data-lng="{{ $p->longitud }}"
                              data-ref="{{ $p->referencia }}"
                              data-id="{{ $p->id }}">
                        Ubicación
                      </button>
                    @else
                      <button class="btn btn-outline-secondary btn-sm mb-1" disabled>
                        Sin Ubicación
                      </button>
                    @endif

                    {{-- Editar --}}
                    <a class="btn btn-primary btn-sm mb-1" href="{{ route('admin.properties.edit', $p) }}">
                      Editar
                    </a>

                    {{-- Cortar/Reconectar/Cancelar Servicio --}}
                    @if($p->estado === 'activo')
                      <form action="{{ route('admin.properties.cut', $p) }}" method="POST" class="d-inline mb-1">
                        @csrf @method('PUT')
                        <button class="btn btn-warning btn-sm" type="button" 
                                onclick="confirmCutService({{ $p->id }}, '{{ $p->referencia }}')">
                          <i class="fas fa-clock"></i> Solicitar Corte
                        </button>
                      </form>
                    @elseif($p->estado === 'corte_pendiente')
                      <div class="btn-group-vertical d-inline mb-1">
                        <form action="{{ route('admin.cortes.marcar-cortado', $p) }}" method="POST" class="d-inline">
                          @csrf
                          <button class="btn btn-danger btn-sm mb-1" type="button" 
                                  onclick="confirmMarkAsCut({{ $p->id }}, '{{ $p->referencia }}')">
                            <i class="fas fa-ban"></i> Marcar Cortado
                          </button>
                        </form>
                        <form action="{{ route('admin.properties.cancel-cut', $p) }}" method="POST" class="d-inline">
                          @csrf @method('PUT')
                          <button class="btn btn-secondary btn-sm" type="button" 
                                  onclick="confirmCancelCut({{ $p->id }}, '{{ $p->referencia }}')">
                            <i class="fas fa-times"></i> Cancelar
                          </button>
                        </form>
                      </div>
                    @elseif($p->estado === 'cortado')
                      <form action="{{ route('admin.properties.restore', $p) }}" method="POST" class="d-inline mb-1">
                        @csrf @method('PUT')
                        <button class="btn btn-success btn-sm" type="button"
                                onclick="confirmRestoreService({{ $p->id }}, '{{ $p->referencia }}')">
                          <i class="fas fa-plug"></i> Reconectar
                        </button>
                      </form>
                    @endif

                    {{-- Eliminar --}}
                    <button class="btn btn-danger btn-sm mb-1" type="button" 
                            onclick="confirmDelete({{ $p->id }}, '{{ $p->referencia }}')">
                      Eliminar
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @else
        <div class="text-center py-5">
          <i class="fas fa-home fa-3x text-muted mb-3"></i>
          <h4 class="text-muted">
            @if(request()->anyFilled(['search', 'estado', 'barrio', 'tarifa_id', 'cliente_id', 'estado_cuenta']))
              No se encontraron propiedades con los filtros aplicados
            @else
              No hay propiedades registradas
            @endif
          </h4>
          @if(!request()->anyFilled(['search', 'estado', 'barrio', 'tarifa_id', 'cliente_id', 'estado_cuenta']))
            <a href="{{ route('admin.properties.create') }}" class="btn btn-primary mt-2">
              <i class="fas fa-plus-circle mr-1"></i>Crear Primera Propiedad
            </a>
          @else
            <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary mt-2">
              <i class="fas fa-undo mr-1"></i>Limpiar Filtros
            </a>
          @endif
        </div>
      @endif
    </div>

    @if($properties->count())
      <div class="card-footer">
        <div class="d-flex justify-content-between align-items-center">
          <div class="text-muted small">
            Mostrando {{ $properties->firstItem() }} - {{ $properties->lastItem() }} de {{ $properties->total() }} registros
          </div>
          {{ $properties->appends(request()->query())->links() }}
        </div>
      </div>
    @endif
  </div>

  {{-- Modal con mapa --}}
  <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">
            <i class="fas fa-map-marker-alt mr-2"></i>
            Ubicación de la Propiedad
          </h5>
          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-0" style="height: 500px;">
          <div id="leafletMap" style="height:100%; width:100%;"></div>
        </div>
        <div class="modal-footer">
          <small class="text-muted mr-auto" id="mapCoordinates"></small>
          <a id="gmapsLink" href="#" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-external-link-alt mr-1"></i>Abrir en Google Maps
          </a>
          <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
@stop

@section('css')
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
  <style>
    .table td {
      vertical-align: middle;
    }
    .badge {
      font-size: 0.75em;
      font-weight: 500;
    }
    .btn-sm {
      margin: 1px;
      padding: 0.25rem 0.5rem;
    }
    #leafletMap {
      border-radius: 0 0 0.25rem 0.25rem;
    }
    .btn-group-vertical .btn {
      margin-bottom: 2px;
    }
    .info-box {
      cursor: default;
    }
    .card-outline {
      border-top: 3px solid #007bff;
    }
  </style>
@stop

@section('js')
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
          integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
          crossorigin=""></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <script>
    let leafletMap = null;
    let leafletMarker = null;

    // Inicializar Select2 para selects largos
    $(document).ready(function() {
      $('#cliente_id').select2({
        placeholder: "Seleccione un cliente",
        allowClear: true,
        width: '100%'
      });

      // Auto-submit cuando cambien algunos filtros
      $('#estado, #barrio, #tarifa_id, #estado_cuenta, #orden').change(function() {
        $('#filterForm').submit();
      });

      // Toggle de filtros
      $('#toggleFilters').click(function() {
        $('.card-outline .card-body').toggle();
      });

      // Auto-ocultar alertas después de 5 segundos
      setTimeout(() => {
        $('.alert').alert('close');
      }, 5000);
    });

    // ... (el resto del código JavaScript del mapa y confirmaciones se mantiene igual)
    $('#mapModal').on('shown.bs.modal', function (event) {
      const button = $(event.relatedTarget);
      const lat = parseFloat(button.data('lat'));
      const lng = parseFloat(button.data('lng'));
      const ref = button.data('ref') || 'Propiedad';
      const id = button.data('id');

      // Actualizar información
      $('#mapCoordinates').text(`Coordenadas: ${lat.toFixed(6)}, ${lng.toFixed(6)}`);
      
      // Link a Google Maps
      const gmaps = `https://www.google.com/maps?q=${lat},${lng}`;
      $('#gmapsLink').attr('href', gmaps);

      // Si no hay coordenadas válidas
      if (isNaN(lat) || isNaN(lng)) {
        if (leafletMap) { leafletMap.remove(); leafletMap = null; }
        document.getElementById('leafletMap').innerHTML = `
          <div class="d-flex justify-content-center align-items-center h-100 bg-light">
            <div class="text-center text-muted">
              <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
              <p>Sin coordenadas registradas</p>
            </div>
          </div>`;
        return;
      }

      // Inicializar mapa
      if (leafletMap) { leafletMap.remove(); }
      
      leafletMap = L.map('leafletMap');
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
      }).addTo(leafletMap);

      // Centrar y agregar marcador
      leafletMap.setView([lat, lng], 16);
      leafletMarker = L.marker([lat, lng]).addTo(leafletMap)
        .bindPopup(`<strong>${ref}</strong><br>ID: ${id}`)
        .openPopup();

      // Ajustar tamaño del mapa
      setTimeout(() => leafletMap.invalidateSize(), 100);
    });

    $('#mapModal').on('hidden.bs.modal', function () {
      if (leafletMap) { 
        leafletMap.remove(); 
        leafletMap = null; 
      }
    });

    function confirmDelete(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Está seguro?',
        html: `¿Eliminar la propiedad: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-warning">Esta acción no se puede deshacer.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/properties/${propertyId}`;
          form.innerHTML = `
            @csrf
            @method('DELETE')
          `;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function confirmCutService(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Solicitar Corte?',
        html: `¿Solicitar corte de servicio para: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-warning">La propiedad quedará en estado "Corte Pendiente" hasta que el equipo físico realice el corte.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, solicitar corte',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/properties/${propertyId}/cut`;
          form.innerHTML = `
            @csrf
            @method('PUT')
          `;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function confirmMarkAsCut(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Confirmar Corte Físico?',
        html: `¿Confirmar que se realizó el corte físico de: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-danger">Se aplicará multa automáticamente y el servicio se cortará definitivamente.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, corte realizado',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/cortes/marcar-cortado/${propertyId}`;
          form.innerHTML = `@csrf`;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function confirmCancelCut(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Cancelar Corte Pendiente?',
        html: `¿Cancelar la solicitud de corte para: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-info">La propiedad volverá a estado "Activo".</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#6c757d',
        cancelButtonColor: '#28a745',
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'Mantener',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/properties/${propertyId}/cancel-cut`;
          form.innerHTML = `
            @csrf
            @method('PUT')
          `;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function confirmRestoreService(propertyId, propertyRef) {
      Swal.fire({
        title: '¿Reconectar Servicio?',
        html: `¿Reconectar el servicio de agua de: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-success">El cliente podrá recibir agua nuevamente.</small>`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, reconectar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.action = `/admin/properties/${propertyId}/restore`;
          form.innerHTML = `
            @csrf
            @method('PUT')
          `;
          document.body.appendChild(form);
          form.submit();
        }
      });
    }
  </script>
@stop