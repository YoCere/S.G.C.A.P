@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Crear nuevo cliente</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            {!!Form:open(['route'=>'admin.clients.store'])!!}

                @include('admin.clients.partials.form')

                {!!Form::submit('Crear cliente', ['class'=>'btn btn-primary'])!!}

            {!!Form::close()!!}
        </div>
    </div>
@stop

