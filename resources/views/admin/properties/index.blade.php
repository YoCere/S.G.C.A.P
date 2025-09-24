{{-- resources/views/admin/properties/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Propiedades')

@section('content_header')
  <h1>Lista de propiedades</h1>
@stop

@section('content')
  @if (session('info'))
    <div class="alert alert-success"><strong>{{ session('info') }}</strong></div>
  @endif

  <div class="card">
    <div class="card-header">
      <a class="btn btn-primary" href="{{ route('admin.properties.create') }}">Nueva propiedad</a>
    </div>

    <div class="card-body">
  <table class="table table-striped mb-0">
    <thead>
      <tr>
        <th>#</th>
        <th>Cliente</th>
        <th>Tarifa</th>
        <th>Referencia</th>
        <th>Estado</th>
        <th colspan="3" class="text-right">Acciones</th>
      </tr>
    </thead>
    <tbody>
      @forelse($properties as $p)
        <tr>
          <td>{{ $p->id }}</td>
          <td>{{ $p->client->nombre ?? '—' }}</td>
          <td>{{ $p->tariff->nombre ?? '—' }}</td>
          <td>
            @if ($p->tariff)
                {{ $p->tariff->nombre }}
                @if ($p->tariff->trashed())
                <span class="badge badge-warning ml-1">Archivada</span>
                @endif
            @else
                —
            @endif
            </td>
          <td>
            <span class="badge badge-{{ $p->estado === 'activo' ? 'success' : 'secondary' }}">
              {{ ucfirst($p->estado) }}
            </span>
          </td>

          {{-- Detalles (abre modal con mapa) --}}
          <td width="10px">
            <button class="btn btn-info btn-sm"
                    data-toggle="modal"
                    data-target="#mapModal"
                    data-lat="{{ $p->latitud }}"
                    data-lng="{{ $p->longitud }}"
                    data-ref="{{ $p->referencia }}"
                    data-id="{{ $p->id }}">
              Ubicacion
            </button>
          </td>

          {{-- Editar --}}
          <td width="10px">
            <a class="btn btn-primary btn-sm" href="{{ route('admin.properties.edit', $p) }}">
              Editar
            </a>
          </td>

          {{-- Eliminar + SweetAlert2 --}}
          <td width="10px">
            <form action="{{ route('admin.properties.destroy', $p) }}" method="POST" id="delete-form-{{ $p->id }}">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm" type="button" onclick="confirmDelete({{ $p->id }})">
                Eliminar
                </button>
            </form>
            </td>
        </tr>
      @empty
        <tr><td colspan="8" class="text-center py-4">No hay propiedades</td></tr>
      @endforelse
    </tbody>
  </table>
</div>

    <div class="card-footer">
      {{ $properties->links() }}
    </div>
  </div>

  {{-- Modal con mapa --}}
  <div class="modal fade" id="mapModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            Ubicación de la propiedad <span id="mapRef" class="text-muted"></span>
          </h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" style="height: 480px;">
          <div id="leafletMap" style="height:100%; width:100%; border-radius:6px;"></div>
          <div class="mt-2">
            <a id="gmapsLink" href="#" target="_blank">Abrir en Google Maps</a>
          </div>
        </div>
      </div>
    </div>
  </div>
@stop

@section('css')
  {{-- Leaflet CSS --}}
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@stop

@section('js')
  {{-- Leaflet JS --}}
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
          integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
          crossorigin=""></script>
  <script>
    let leafletMap = null;
    let leafletMarker = null;

    $('#mapModal').on('shown.bs.modal', function (event) {
      const button = $(event.relatedTarget);
      const lat = parseFloat(button.data('lat'));
      const lng = parseFloat(button.data('lng'));
      const ref = button.data('ref') || '';
      $('#mapRef').text(ref ? `— ${ref}` : '');

      // Link a Google Maps
      const gmaps = `https://www.google.com/maps?q=${lat},${lng}`;
      $('#gmapsLink').attr('href', gmaps);

      // Si no hay coordenadas válidas
      if (isNaN(lat) || isNaN(lng)) {
        if (leafletMap) { leafletMap.remove(); leafletMap = null; }
        document.getElementById('leafletMap').innerHTML = '<div class="p-3">Sin coordenadas registradas.</div>';
        return;
      }

      // Inicializa o reinicia el mapa
      if (leafletMap) { leafletMap.remove(); leafletMap = null; }

      leafletMap = L.map('leafletMap');
      const tiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
      }).addTo(leafletMap);

      leafletMap.setView([lat, lng], 18);
      leafletMarker = L.marker([lat, lng]).addTo(leafletMap).bindPopup(ref || 'Ubicación').openPopup();

      // Por si el modal cambia de tamaño
      setTimeout(() => leafletMap.invalidateSize(), 200);
    });

    $('#mapModal').on('hidden.bs.modal', function () {
      if (leafletMap) { leafletMap.remove(); leafletMap = null; }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    function confirmDelete(propertyId) {
      Swal.fire({
        title: '¿Está seguro?',
        text: "¡No podrá revertir esta acción!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById(`delete-form-${propertyId}`).submit();
        }
      });
    }
  </script>
@stop
