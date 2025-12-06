@extends('errors.layout')

@php
    $iconName = $iconName ?? 'exclamation-circle';
@endphp

@section('title', 'Error de Validación')
@section('code', '422')
@section('message', 'Los datos enviados no son válidos.')

@if(app()->environment('local') && isset($exception) && $exception instanceof \Illuminate\Validation\ValidationException)
    @section('details')
        <div class="validation-errors">
            <strong>Errores encontrados:</strong>
            <ul class="mt-2 mb-0">
                @foreach ($exception->validator->errors()->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endsection
@else
    @section('details', 'Por favor, verifica la información ingresada e intenta nuevamente.')
@endif