{{-- resources/views/errors/503.blade.php --}}
@extends('errors.layout')

@section('title', 'Servicio No Disponible')
@section('code', '503')
@section('icon', '<i class="fas fa-tools"></i>')
@section('message', 'El servicio está temporalmente no disponible.')
@section('details', 'Estamos realizando mantenimiento o actualizaciones. Por favor, vuelve más tarde.')