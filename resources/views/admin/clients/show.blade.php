@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Mostrar detalles de clientes</h1>
@stop

@section('content')
    <p>Este es el panel de control para la gestion de cobros de agua potable con geolocalizacion de clientes para el Comite de agua de la comunidad de La Grampa.</p>
@stop

@section('css')
    {{-- Add here extra stylesheets --}}
    {{-- <link rel="stylesheet" href="/css/admin_custom.css"> --}}
@stop

@section('js')
    <script> console.log("Hi, I'm using the Laravel-AdminLTE package!"); </script>
@stop