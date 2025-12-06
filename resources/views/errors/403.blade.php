@extends('errors.layout')

@section('title', 'Acceso Prohibido')
@section('code', '403')
@section('icon', '<i class="fas fa-ban"></i>')
@section('message', 'No tienes permisos para acceder a esta página.')

@if(app()->environment('local'))
    @section('details', 'Tu rol de usuario no tiene los permisos necesarios para esta acción.')
@endif