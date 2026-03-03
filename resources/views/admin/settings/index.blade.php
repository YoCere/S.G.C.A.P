@extends('layouts.admin-ultralight')

@section('title', 'Configuración General')

@section('content_header')
    <h1 class="h5 font-weight-bold">Configuración General</h1>
    <small class="text-muted">Administre la información pública del sistema</small>
@stop

@section('content')

@if(session('info'))
    <div class="alert alert-success alert-dismissible fade show">
        <strong>{{ session('info') }}</strong>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
@endif

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="row">

    {{-- 🏢 INFORMACIÓN DE CONTACTO --}}
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <strong>Información de Contacto</strong>
            </div>
            <div class="card-body">

                <div class="form-group">
                    <label>Dirección</label>
                    <input type="text"
                           name="contact_address"
                           class="form-control @error('contact_address') is-invalid @enderror"
                           value="{{ old('contact_address', $settings['contact_address'] ?? '') }}">
                    @error('contact_address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text"
                           name="contact_phone"
                           class="form-control @error('contact_phone') is-invalid @enderror"
                           value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}">
                    @error('contact_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Correo Electrónico</label>
                    <input type="email"
                           name="contact_email"
                           class="form-control @error('contact_email') is-invalid @enderror"
                           value="{{ old('contact_email', $settings['contact_email'] ?? '') }}">
                    @error('contact_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    {{-- 🕒 HORARIOS --}}
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <strong>Horarios de Atención</strong>
            </div>
            <div class="card-body">

                <div class="form-group">
                    <label>Lunes a Viernes</label>
                    <input type="text"
                           name="schedule_weekdays"
                           class="form-control @error('schedule_weekdays') is-invalid @enderror"
                           value="{{ old('schedule_weekdays', $settings['schedule_weekdays'] ?? '') }}">
                    @error('schedule_weekdays')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Sábados</label>
                    <input type="text"
                           name="schedule_saturday"
                           class="form-control @error('schedule_saturday') is-invalid @enderror"
                           value="{{ old('schedule_saturday', $settings['schedule_saturday'] ?? '') }}">
                    @error('schedule_saturday')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

            </div>
        </div>
    </div>

    {{-- 💳 QR DE PAGOS --}}
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <strong>QR para Pagos</strong>
            </div>
            <div class="card-body">

                <div class="row align-items-center">
                    <div class="col-md-6">

                        <div class="form-group">
                            <label>Subir nueva imagen QR</label>
                            <input type="file"
                                   name="qr_image"
                                   class="form-control-file @error('qr_image') is-invalid @enderror"
                                   accept="image/png,image/jpeg,image/webp">
                            <small class="form-text text-muted">
                                Tamaño máximo 1MB. Recomendado: imagen cuadrada.
                            </small>
                            @error('qr_image')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>

                    <div class="col-md-6 text-center">

                        @if(!empty($settings['qr_image']))
                            <p class="font-weight-bold mb-2">QR Actual</p>
                            <img src="{{ asset('storage/' . $settings['qr_image']) }}"
                                 alt="QR actual"
                                 width="200"
                                 height="200"
                                 loading="lazy"
                                 class="img-fluid border rounded shadow-sm">
                        @else
                            <p class="text-muted">No hay QR configurado.</p>
                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

<div class="text-right">
    <button type="submit" class="btn btn-primary btn-lg">
        Guardar Cambios
    </button>
</div>

</form>

@stop