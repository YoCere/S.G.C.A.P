{{-- resources/views/admin/properties/show.blade.php --}}
@extends('layouts.admin-ultralight')

@section('title', 'Detalle de Propiedad')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1>Propiedad #{{ $property->id }}</h1>
    <a href="{{ route('admin.properties.index') }}" class="btn btn-secondary btn-sm">
      <i class="fas fa-arrow-left mr-1"></i> Volver
    </a>
  </div>
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

  <div class="row">
    <!-- Información Principal -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-primary text-white">
          <h5 class="card-title mb-0">
            <i class="fas fa-info-circle mr-2"></i>Información de la Propiedad
          </h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-12 mb-3">
              <strong>Referencia:</strong>
              <p class="mb-1">{{ $property->referencia }}</p>
            </div>
            
            <div class="col-12 mb-3">
              <strong>Barrio:</strong>
              <p class="mb-1">
                @if($property->barrio)
                  <span class="badge badge-light border">{{ $property->barrio }}</span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </p>
            </div>
            
            <div class="col-12 mb-3">
              <strong>Estado:</strong>
              <p class="mb-1">
                @if($property->estado === 'activo')
                  <span class="badge badge-success">Activo</span>
                @elseif($property->estado === 'corte_pendiente')
                  <span class="badge badge-warning">Corte Pendiente</span>
                @elseif($property->estado === 'cortado')
                  <span class="badge badge-danger">Cortado</span>
                @else
                  <span class="badge badge-secondary">Inactivo</span>
                @endif
              </p>
            </div>
            
            <div class="col-12 mb-3">
              <strong>Tarifa:</strong>
              <p class="mb-1">
                {{ $property->tariff->nombre ?? '—' }} 
                @if($property->tariff)
                  - <span class="text-success">Bs {{ number_format($property->tariff->precio_mensual, 2) }}</span>
                  @if(!$property->tariff->activo)
                    <span class="badge badge-warning ml-1">INACTIVA</span>
                  @endif
                @endif
              </p>
            </div>

            <div class="col-12 mb-3">
              <strong>Fecha de Registro:</strong>
              <p class="mb-1">{{ $property->created_at->format('d/m/Y H:i') }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Información del Cliente -->
      <div class="card mt-3">
        <div class="card-header bg-info text-white">
          <h5 class="card-title mb-0">
            <i class="fas fa-user mr-2"></i>Información del Cliente
          </h5>
        </div>
        <div class="card-body">
          @if($property->client)
            <div class="row">
              <div class="col-12 mb-2">
                <strong>Nombre:</strong>
                <p class="mb-1">{{ $property->client->nombre }}</p>
              </div>
              
              <div class="col-12 mb-2">
                <strong>Cédula de Identidad:</strong>
                <p class="mb-1">{{ $property->client->ci ?? '—' }}</p>
              </div>
              
              <div class="col-12 mb-2">
                <strong>Código de Cliente:</strong>
                <p class="mb-1">
                  <span class="badge badge-primary font-lg">{{ $property->client->codigo_cliente ?? 'N/A' }}</span>
                </p>
              </div>
              
              <div class="col-12 mb-2">
                <strong>Estado de Cuenta:</strong>
                <p class="mb-1">
                  <span class="badge badge-{{ $property->client->estado_cuenta == 'activo' ? 'success' : 'warning' }}">
                    {{ $property->client->estado_cuenta ?? 'N/A' }}
                  </span>
                </p>
              </div>
              
              <div class="col-12 mb-2">
                <strong>Teléfono:</strong>
                <p class="mb-1">{{ $property->client->telefono ?? '—' }}</p>
              </div>
              
              <div class="col-12">
                <strong>Fecha de Registro:</strong>
                <p class="mb-1">{{ $property->client->fecha_registro ? $property->client->fecha_registro->format('d/m/Y') : '—' }}</p>
              </div>
            </div>

            <div class="mt-3">
              <a href="{{ route('admin.clients.show', $property->client) }}" class="btn btn-outline-info btn-sm">
                <i class="fas fa-external-link-alt mr-1"></i> Ver Detalles del Cliente
              </a>
            </div>
          @else
            <div class="text-center text-muted py-3">
              <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
              <p>Cliente no asignado</p>
            </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Mapa y Acciones -->
    <div class="col-md-6">
      <div class="card">
        <div class="card-header bg-success text-white">
          <h5 class="card-title mb-0">
            <i class="fas fa-map-marker-alt mr-2"></i>Ubicación
          </h5>
        </div>
        <div class="card-body p-0">
          @if($property->latitud && $property->longitud)
            <div id="leafletMap" style="height: 300px; width: 100%; border-radius: 0.25rem;"></div>
            <div class="p-3">
              <div class="row">
                <div class="col-6">
                  <small class="text-muted">
                    <strong>Latitud:</strong> {{ $property->latitud }}
                  </small>
                </div>
                <div class="col-6">
                  <small class="text-muted">
                    <strong>Longitud:</strong> {{ $property->longitud }}
                  </small>
                </div>
              </div>
              <div class="mt-2">
                <a href="https://www.google.com/maps?q={{ $property->latitud }},{{ $property->longitud }}" 
                   target="_blank" class="btn btn-outline-primary btn-sm btn-block">
                  <i class="fas fa-external-link-alt mr-1"></i>Abrir en Google Maps
                </a>
              </div>
            </div>
          @else
            <div class="text-center py-5 text-muted">
              <i class="fas fa-map-marker-alt fa-3x mb-3"></i>
              <p>Sin coordenadas registradas</p>
              <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-primary btn-sm mt-2">
                <i class="fas fa-edit mr-1"></i> Agregar Ubicación
              </a>
            </div>
          @endif
        </div>
      </div>

      <!-- Acciones Rápidas -->
      <div class="card mt-3">
        <div class="card-header bg-warning">
          <h5 class="card-title mb-0">
            <i class="fas fa-bolt mr-2"></i>Acciones Rápidas
          </h5>
        </div>
        <div class="card-body">
          <div class="d-grid gap-2">
            <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-primary btn-sm">
              <i class="fas fa-edit mr-1"></i> Editar Propiedad
            </a>
            
            @if($property->estado === 'activo')
              <form action="{{ route('admin.properties.cut', $property) }}" method="POST" class="d-grid">
                @csrf @method('PUT')
                <button type="button" class="btn btn-warning btn-sm" 
                        onclick="confirmCutService({{ $property->id }}, '{{ $property->referencia }}')">
                  <i class="fas fa-clock mr-1"></i> Solicitar Corte
                </button>
              </form>
            @elseif($property->estado === 'corte_pendiente')
              <div class="btn-group-vertical">
                <form action="{{ route('admin.cortes.marcar-cortado', $property) }}" method="POST" class="d-grid">
                  @csrf
                  <button type="button" class="btn btn-danger btn-sm mb-1" 
                          onclick="confirmMarkAsCut({{ $property->id }}, '{{ $property->referencia }}')">
                    <i class="fas fa-ban mr-1"></i> Marcar Cortado
                  </button>
                </form>
                <form action="{{ route('admin.properties.cancel-cut', $property) }}" method="POST" class="d-grid">
                  @csrf @method('PUT')
                  <button type="button" class="btn btn-secondary btn-sm" 
                          onclick="confirmCancelCut({{ $property->id }}, '{{ $property->referencia }}')">
                    <i class="fas fa-times mr-1"></i> Cancelar Corte
                  </button>
                </form>
              </div>
            @can('admin.properties.restore')
            @elseif($property->estado === 'cortado')
              <form action="{{ route('admin.properties.restore', $property) }}" method="POST" class="d-grid">
                @csrf @method('PUT')
                <button type="button" class="btn btn-success btn-sm"
                        onclick="confirmRestoreService({{ $property->id }}, '{{ $property->referencia }}')">
                  <i class="fas fa-plug mr-1"></i> Reconectar Servicio
                </button>
              </form>
            @endif
            @endcan

            
          </div>
        </div>
      </div>

      <!-- Información Adicional -->
<div class="card mt-3">
  <div class="card-header bg-secondary text-white">
      <h5 class="card-title mb-0">
          <i class="fas fa-chart-bar mr-2"></i>Estadísticas
      </h5>
  </div>
  <div class="card-body">
      <div class="row text-center">
          <div class="col-6 mb-2">
              <small class="text-muted d-block">Deudas Pendientes</small>
              <strong class="text-danger">Bs {{ number_format($property->debts->where('estado', 'pendiente')->sum('monto_pendiente'), 2) }}</strong>
          </div>
          <div class="col-6 mb-2">
              <small class="text-muted d-block">Meses Adeudados</small>
              <strong>{{ count($property->obtenerMesesAdeudados()) }}</strong>
          </div>
          <div class="col-6">
              <small class="text-muted d-block">Multas Pendientes</small>
              <strong>{{ $property->multas()->where('estado', 'pendiente')->count() }}</strong>
          </div>
          <div class="col-6">
              <small class="text-muted d-block">Estado General</small>
              @php
                  $totalDeudas = $property->debts->where('estado', 'pendiente')->sum('monto_pendiente');
                  $mesesAdeudados = count($property->obtenerMesesAdeudados());
              @endphp
              
              @if($totalDeudas == 0 && $mesesAdeudados == 0)
                  <span class="badge badge-success">AL DÍA</span>
              @elseif($property->estado === 'corte_pendiente' || $property->estado === 'cortado')
                  <span class="badge badge-danger">{{ strtoupper($property->estado) }}</span>
              @else
                  <span class="badge badge-warning">CON DEUDA</span>
              @endif
          </div>
      </div>
      
      @if($property->debts->where('estado', 'pendiente')->count() > 0)
          <div class="mt-3">
              <small class="text-muted d-block mb-2">Deudas Pendientes:</small>
              <div class="list-group list-group-flush">
                  @foreach($property->debts->where('estado', 'pendiente')->take(3) as $debt)
                      <div class="list-group-item px-0 py-1 small">
                          <div class="d-flex justify-content-between">
                              <span>Deuda {{ \Carbon\Carbon::parse($debt->fecha_emision)->format('M Y') }}</span>
                              <span class="text-danger">Bs {{ number_format($debt->monto_pendiente, 2) }}</span>
                          </div>
                          <small class="text-muted">
                              Emitida: {{ $debt->fecha_emision->format('d/m/Y') }}
                              @if($debt->fecha_vencimiento)
                                  | Vence: {{ $debt->fecha_vencimiento->format('d/m/Y') }}
                              @endif
                          </small>
                      </div>
                  @endforeach
              </div>
              @if($property->debts->where('estado', 'pendiente')->count() > 3)
                  <small class="text-muted">+{{ $property->debts->where('estado', 'pendiente')->count() - 3 }} más...</small>
              @endif
          </div>
      @else
          <div class="mt-3 text-center">
              <small class="text-success">
                  <i class="fas fa-check-circle"></i> No hay deudas pendientes
              </small>
          </div>
      @endif

      <!-- ✅ NUEVO: Mostrar meses adeudados específicos -->
      @if(count($property->obtenerMesesAdeudados()) > 0)
          <div class="mt-3">
              <small class="text-muted d-block mb-2">Meses Adeudados:</small>
              <div class="d-flex flex-wrap gap-1">
                  @foreach($property->obtenerMesesAdeudados() as $mes)
                      <span class="badge badge-danger small">
                          {{ \Carbon\Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('M Y') }}
                      </span>
                  @endforeach
              </div>
          </div>
      @endif
  </div>
</div>
    </div>
  </div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
.card { margin-bottom: 1rem; }
.badge { font-size: 0.8em; }
.btn-group-vertical .btn { margin-bottom: 0.25rem; }
.list-group-item { border: none; }
</style>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Mapa en show
document.addEventListener('DOMContentLoaded', function() {
  const lat = {{ $property->latitud ?? 'null' }};
  const lng = {{ $property->longitud ?? 'null' }};
  
  if (lat && lng) {
    const map = L.map('leafletMap');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19, 
      attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    
    map.setView([lat, lng], 16);
    L.marker([lat, lng]).addTo(map)
      .bindPopup(`<strong>{{ $property->referencia }}</strong><br>Cliente: {{ $property->client->nombre ?? 'N/A' }}`)
      .openPopup();
  }
});

// Funciones de confirmación
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
      form.innerHTML = `@csrf @method('DELETE')`;
      document.body.appendChild(form);
      form.submit();
    }
  });
}

function confirmCutService(propertyId, propertyRef) {
  Swal.fire({
    title: '¿Solicitar Corte?',
    html: `¿Solicitar corte de servicio para: <strong>"${propertyRef}"</strong>?<br>
           <small class="text-warning">La propiedad quedará en estado "Corte Pendiente".</small>`,
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
      form.innerHTML = `@csrf @method('PUT')`;
      document.body.appendChild(form);
      form.submit();
    }
  });
}

function confirmMarkAsCut(propertyId, propertyRef) {
  Swal.fire({
    title: '¿Confirmar Corte Físico?',
    html: `¿Confirmar que se realizó el corte físico de: <strong>"${propertyRef}"</strong>?<br>
           <small class="text-danger">Se aplicará multa automáticamente.</small>`,
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
      form.innerHTML = `@csrf @method('PUT')`;
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
      form.innerHTML = `@csrf @method('PUT')`;
      document.body.appendChild(form);
      form.submit();
    }
  });
}
</script>
@stop