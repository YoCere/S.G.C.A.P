<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Error') | S.G.C.A.P</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    
    <style>
        .error-page {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f4f6f9;
        }
        .error-content {
            text-align: center;
            padding: 40px;
        }
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-number {
            font-size: 120px;
            font-weight: bold;
            color: #dc3545;
            line-height: 1;
        }
        .error-text {
            font-size: 36px;
            font-weight: 300;
            color: #343a40;
            margin: 20px 0;
        }
        .error-message {
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 30px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .error-details {
            background: #fff;
            border-radius: 5px;
            padding: 15px;
            margin: 20px auto;
            max-width: 600px;
            text-align: left;
            border-left: 4px solid #dc3545;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-action {
            margin: 5px;
            min-width: 150px;
        }
        .debug-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 5px;
            padding: 15px;
            margin: 20px auto;
            max-width: 600px;
            text-align: left;
        }
        .debug-header {
            color: #856404;
            border-bottom: 1px solid #ffeaa7;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .security-note {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 5px;
            padding: 10px;
            margin: 15px 0;
            color: #0c5460;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="hold-transition">
    <div class="wrapper">
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Main content -->
            <section class="content">
                <div class="error-page">
                    <div class="error-content">
                        {{-- Icono del error --}}
                        <div class="error-icon">
                            <i class="fas fa-{{ $iconName ?? 'exclamation-triangle' }}"></i>
                        </div>
                        
                        {{-- Código del error --}}
                        <h1 class="error-number">@yield('code', 'Error')</h1>
                        
                        {{-- Título del error --}}
                        <h3 class="error-text">@yield('title', 'Error')</h3>
                        
                        {{-- Mensaje del error --}}
                        <p class="error-message">
                            @yield('message', 'Ha ocurrido un error inesperado.')
                        </p>
                        
                        {{-- Detalles generales (si los hay) --}}
                        @hasSection('details')
                            <div class="error-details">
                                <strong><i class="fas fa-info-circle mr-1"></i> Información:</strong>
                                <p class="mt-2 mb-0">@yield('details')</p>
                            </div>
                        @endif

                        {{-- Botones de acción --}}
                        <div class="mt-4">
                            <a href="javascript:history.back()" class="btn btn-outline-secondary btn-action">
                                <i class="fas fa-arrow-left mr-2"></i> Volver Atrás
                            </a>
                            <a href="{{ auth()->check() ? (route('dashboard') ?? url('/')) : url('/') }}" class="btn btn-primary btn-action">
                                <i class="fas fa-home mr-2"></i> Ir al Inicio
                            </a>
                            @auth
                                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="btn btn-info btn-action">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @endauth
                        </div>

                        {{-- SECCIÓN SEGURA DE DEBUG --}}
                        @php
                            // Verificación estricta del entorno
                            $isLocal = app()->environment('local');
                            $isDebug = config('app.debug');
                            $showDebug = $isLocal && $isDebug && isset($exception);
                        @endphp

                        @if($showDebug)
                            <div class="debug-box mt-4">
                                
                                
                                {{-- Información SEGURA para desarrollo --}}
                                <div class="mt-2">
                                   
                                    
                                    
                                    {{-- Información de usuario (limitada) --}}
                                    @auth
                                        <p class="mb-2">
                                            <strong>Usuario:</strong> 
                                            {{ auth()->user()->name ?? auth()->user()->email }}
                                            (ID: {{ auth()->id() }})
                                        </p>
                                    @else
                                        <p class="mb-2"><strong>Estado:</strong> No autenticado</p>
                                    @endauth
                                    
                                    
                                    
                                    {{-- Código de error --}}
                                    @if($exception->getCode())
                                        <p class="mb-2">
                                            <strong>Código:</strong> {{ $exception->getCode() }}
                                        </p>
                                    @endif
                                    
                                    
                                </div>
                                
                               
                            </div>
                            
                            {{-- Script para copiar información --}}
                            <script>
                                function copyErrorInfo() {
                                    const errorInfo = `Error: {{ addslashes($exception->getMessage()) }}\n` +
                                                     `URL: {{ request()->fullUrl() }}\n` +
                                                     `Tipo: {{ get_class($exception) }}\n` +
                                                     @if($exception->getFile())
                                                     `Archivo: {{ addslashes(str_replace(base_path(), '', $exception->getFile())) }}\n` +
                                                     @endif
                                                     `Línea: {{ $exception->getLine() ?? 'N/A' }}`;
                                    
                                    navigator.clipboard.writeText(errorInfo).then(() => {
                                        alert('Información copiada al portapapeles');
                                    });
                                }
                            </script>
                        @elseif(isset($exception) && app()->environment('local'))
                            {{-- Si estamos en local pero debug está desactivado --}}
                            
                        @endif
                        
                        {{-- Mensaje para producción --}}
                        @if(app()->environment('production'))
                            <div class="security-note mt-4">
                                <i class="fas fa-shield-alt mr-1"></i>
                                <strong>Modo Producción:</strong> Si el error persiste, contacta al administrador del sistema.
                            </div>
                        @endif
                    </div>
                    <!-- /.error-content -->
                </div>
                <!-- /.error-page -->
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
</body>
</html>