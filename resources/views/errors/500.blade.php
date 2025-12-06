@extends('errors.layout')

@php
    $iconName = $iconName ?? 'server';
@endphp

@section('title', 'Error del Servidor')
@section('code', '500')
@section('message', 'Ha ocurrido un error interno en el servidor.')

@if(app()->environment('local') && isset($exception))
    @section('details')
        Se ha detectado un problema en el sistema. El equipo técnico ha sido notificado.
        
        @if(isset($debug) && $debug)
            <div class="mt-2">
                <strong>Información técnica:</strong><br>
                {{ $exception->getMessage() }}
            </div>
        @endif
    @endsection
@else
    @section('details', 'Nuestro equipo técnico ha sido notificado. Por favor, intenta más tarde o contacta al soporte.')
@endif