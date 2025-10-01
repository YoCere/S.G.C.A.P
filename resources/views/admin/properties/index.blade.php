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

  <div class="card">
    <div class="card-header">
      <div class="row align-items-center">
        <div class="col-md-6">
          <a class="btn btn-primary btn-sm" href="{{ route('admin.properties.create') }}">
            <i class="fas fa-plus-circle mr-1"></i>Nueva Propiedad
          </a>
        </div>
        <div class="col-md-6">
          <form action="{{ route('admin.properties.index') }}" method="GET" class="form-inline float-right">
            <div class="input-group input-group-sm">
              <input type="text" name="search" class="form-control" 
                     placeholder="Buscar por referencia, cliente o barrio..." 
                     value="{{ request('search') }}">
              <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="submit" title="Buscar">
                  <i class="fas fa-search"></i>
                </button>
                @if(request('search'))
                  <a href="{{ route('admin.properties.index') }}" class="btn btn-outline-danger" title="Limpiar búsqueda">
                    <i class="fas fa-times"></i>
                  </a>
                @endif
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="card-body p-0">
      @if(request('search'))
        <div class="alert alert-info mb-0 mx-3 mt-3">
          <i class="fas fa-info-circle mr-1"></i>
          Mostrando resultados para: <strong>"{{ request('search') }}"</strong>
          <a href="{{ route('admin.properties.index') }}" class="float-right text-dark font-weight-bold">
            <i class="fas fa-times mr-1"></i>Limpiar
          </a>
        </div>
      @endif

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
                <th width="100">Estado</th>
                <th width="250" class="text-center">Acciones</th>
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
                    </div>
                  </td>
                  <td>
                    <strong>{{ $p->referencia }}</strong>
                    @if($p->estado === 'cortado')
                      <span class="badge badge-danger ml-1">CORTADO</span>
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

                    {{-- Cortar/Reconectar Servicio --}}
                    @if($p->estado === 'activo')
                      <form action="{{ route('admin.properties.cut', $p) }}" method="POST" class="d-inline mb-1">
                        @csrf @method('PUT')
                        <button class="btn btn-warning btn-sm" type="button" 
                                onclick="confirmCutService({{ $p->id }}, '{{ $p->referencia }}')">
                          Cortar Agua
                        </button>
                      </form>
                    @elseif($p->estado === 'cortado')
                      <form action="{{ route('admin.properties.restore', $p) }}" method="POST" class="d-inline mb-1">
                        @csrf @method('PUT')
                        <button class="btn btn-success btn-sm" type="button"
                                onclick="confirmRestoreService({{ $p->id }}, '{{ $p->referencia }}')">
                          Reconectar
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
            @if(request('search'))
              No se encontraron propiedades para "{{ request('search') }}"
            @else
              No hay propiedades registradas
            @endif
          </h4>
          @if(!request('search'))
            <a href="{{ route('admin.properties.create') }}" class="btn btn-primary mt-2">
              <i class="fas fa-plus-circle mr-1"></i>Crear Primera Propiedad
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
          {{ $properties->links() }}
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
        title: '¿Cortar Servicio?',
        html: `¿Cortar el servicio de agua de: <strong>"${propertyRef}"</strong>?<br>
               <small class="text-warning">El cliente no podrá recibir agua hasta que se reconecte.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ffc107',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, cortar agua',
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

    // Auto-ocultar alertas después de 5 segundos
    $(document).ready(function() {
      setTimeout(() => {
        $('.alert').alert('close');
      }, 5000);
    });
  </script>
@stop