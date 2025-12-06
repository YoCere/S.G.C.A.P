{{-- resources/views/errors/419.blade.php --}}
@extends('errors.layout')

@section('title', 'Sesión Expirada')
@section('code', '419')
@section('icon', '<i class="fas fa-clock"></i>')
@section('message', 'Tu sesión ha expirado por inactividad.')
@section('details', 'Por favor, actualiza la página e intenta nuevamente.')