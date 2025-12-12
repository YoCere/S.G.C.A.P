<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - {{ config('app.name', 'SGCAP') }}</title>

    <!-- Tailwind / Jetstream -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <!-- AdminLTE (solo lo esencial, muy ligero) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Figtree', sans-serif !important;
        }
        /* Evitar que AdminLTE pisé Jetstream */
        [x-cloak] { display: none !important; }

        /* Mantener diseño original */
        .content-wrapper {
            background: #f4f6f9 !important;
            padding: 20px !important;
        }
    </style>

    @yield('css')
</head>

<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

    <!-- TOP NAVBAR (la original del panel, la blanca) -->
    <!-- TOP NAVBAR (estilo original AdminLTE + avatar Jetstream) -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    <!-- Botón Sidebar -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#">
                <i class="fas fa-bars"></i>
            </a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <span class="nav-link font-bold">Dashboard</span>
        </li>
    </ul>

    <!-- Parte derecha -->
    <ul class="navbar-nav ml-auto">

        <!-- Buscar -->
        <li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#">
                <i class="fas fa-search"></i>
            </a>
        </li>

        <!-- Pantalla completa -->
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#">
                <i class="fas fa-expand"></i>
            </a>
        </li>

        <!-- Avatar + Menú de usuario -->
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="#">
                <img src="{{ Auth::user()->profile_photo_url }}"
                    class="rounded-circle"
                    style="width:32px; height:32px; object-fit:cover;">
                <span class="ml-2">{{ Auth::user()->name }}</span>
            </a>

            <div class="dropdown-menu dropdown-menu-right">

                <a href="{{ route('profile.show') }}" class="dropdown-item">
                    <i class="fas fa-user mr-2"></i> Perfil
                </a>

                <div class="dropdown-divider"></div>

                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                    @csrf
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión
                    </button>
                </form>

            </div>
        </li>

    </ul>
</nav>

    <!-- SIDEBAR -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">

        <!-- Logo -->
        <a href="{{ route('admin.home') }}" class="brand-link text-center">
            <img src="{{ asset('images/just_logo.png') }}" class="brand-image" style="opacity:.9">
            <span class="brand-text font-weight-bold">S.G.C.A.P</span>
        </a>

        <div class="sidebar">

            <!-- Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview">
                    @foreach(config('adminlte.menu') as $item)
                        @if(isset($item['route']))
                            <li class="nav-item">
                                <a href="{{ route($item['route']) }}"
                                   class="nav-link {{ request()->routeIs($item['route']) ? 'active' : '' }}">
                                    <i class="nav-icon {{ $item['icon'] ?? 'fas fa-circle' }}"></i>
                                    <p>{{ $item['text'] }}</p>
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
            </nav>

        </div>
    </aside>

    <!-- CONTENIDO -->
    <div class="content-wrapper">

        <div class="pt-3 pb-2 border-bottom mb-3">
            <h1 class="text-2xl font-bold">@yield('title')</h1>
            @yield('content_header')
        </div>

        @yield('content')

    </div>

</div>

@livewireScripts

<!-- jQuery (requerido por AdminLTE) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Popper (NECESARIO para dropdowns de Bootstrap 4) -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>

<!-- Bootstrap 4 (versión correcta para AdminLTE 3) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>

<!-- AdminLTE -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>

@yield('js')
@stack('scripts')

</body>
</html>
