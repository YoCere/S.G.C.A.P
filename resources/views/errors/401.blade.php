{{-- resources/views/errors/401.blade.php --}}
@extends('errors.layout')

@section('title', 'Acceso No Autorizado')
@section('code', '401')
@section('icon', '<i class="fas fa-user-lock"></i>')
@section('message', 'No tienes autorización para acceder a esta página.')
@section('details', 'Por favor, inicia sesión con credenciales válidas o contacta al administrador del sistema.')