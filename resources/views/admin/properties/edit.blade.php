{{-- resources/views/admin/properties/edit.blade.php --}}
@extends('layouts.admin-ultralight')

@section('title', 'Editar Propiedad')

@section('content_header')
  <h1>Editar propiedad</h1>
@stop

@section('content')
  @if (session('info'))
    <div class="alert alert-success"><strong>{{ session('info') }}</strong></div>
  @endif

  <div class="card p-3">
    <form method="POST" action="{{ route('admin.properties.update', $property) }}">
      @method('PUT')
      @include('admin.properties._form')
    </form>
  </div>
@stop
