{{-- resources/views/admin/properties/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Nueva Propiedad')

@section('content_header')
  <h1>Nueva instalacion</h1>
@stop

@section('content')
  @if (session('info'))
    <div class="alert alert-success"><strong>{{ session('info') }}</strong></div>
  @endif

  <div class="card p-3">
    <form method="POST" action="{{ route('admin.properties.store') }}">
      @include('admin.properties._form')
    </form>
  </div>
@stop
