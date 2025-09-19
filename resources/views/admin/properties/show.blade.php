{{-- resources/views/admin/properties/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Detalle de propiedad')

@section('content_header')
  <h1>Propiedad #{{ $property->id }}</h1>
@stop

@section('content')
  <div class="card">
    <div class="card-body">
      <p><strong>Cliente:</strong> {{ $property->client->nombre ?? '—' }}</p>
      <p><strong>Tarifa:</strong> {{ $property->tariff->nombre ?? '—' }}</p>
      <p><strong>Referencia:</strong> {{ $property->referencia }}</p>
      <p><strong>Coordenadas:</strong> {{ $property->latitud ?? '—' }}, {{ $property->longitud ?? '—' }}</p>

      <div id="leafletMap" style="height: 420px; border-radius:6px;"></div>

      @if($property->latitud && $property->longitud)
        <div class="mt-2">
          <a href="https://www.google.com/maps?q={{ $property->latitud }},{{ $property->longitud }}" target="_blank">
            Abrir en Google Maps
          </a>
        </div>
      @endif
    </div>
  </div>
@stop

@section('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@stop

@section('js')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
<script>
  const lat = {{ $property->latitud ?? 'null' }};
  const lng = {{ $property->longitud ?? 'null' }};
  if (lat && lng) {
    const map = L.map('leafletMap');
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19, attribution: '&copy; OpenStreetMap'
    }).addTo(map);
    map.setView([lat, lng], 18);
    L.marker([lat, lng]).addTo(map).bindPopup(`{{ $property->referencia }}`).openPopup();
  } else {
    document.getElementById('leafletMap').innerHTML = '<div class="p-3">Sin coordenadas registradas.</div>';
  }
</script>
@stop
