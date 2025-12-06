@extends('errors.layout')

@php
    $iconName = 'map-signs';
@endphp

@section('title', 'Página No Encontrada')
@section('code', '404')
@section('message', 'La página que estás buscando no existe o ha sido movida.')

@if(app()->environment('local'))
    @section('details', 'URL solicitada: ' . request()->fullUrl())
@endif