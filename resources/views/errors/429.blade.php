{{-- resources/views/errors/429.blade.php --}}
@extends('errors.layout')

@section('title', 'Demasiadas Solicitudes')
@section('code', '429')
@section('icon', '<i class="fas fa-tachometer-alt"></i>')
@section('message', 'Has excedido el límite de solicitudes permitidas.')
@section('details', 'Por favor, espera unos minutos antes de intentar nuevamente.')