@extends('layouts.admin-ultralight')
@section('title', 'Reportes - SGCAF')

@section('content_header')
    <h1>📋 Reportes - COMITE DE AGUA LA GRAMPA</h1>
    <p class="text-muted">Listas simples y compactas para impresión/PDF</p>
@stop

@section('content')
    <div class="row">
        <!-- Reporte de Morosidad -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">🔴 Morosidad</h3>
                </div>
                <div class="card-body">
                    <p class="card-text">Lista de clientes con deudas pendientes ordenados por monto y antigüedad.</p>
                    <div class="mt-3">
                        <span class="badge badge-info">6 columnas</span>
                        <span class="badge badge-success">Compacto</span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.reportes.morosidad') }}" class="btn btn-primary btn-block">
                        <i class="fas fa-file-alt mr-2"></i> Generar Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Clientes -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">👥 Clientes</h3>
                </div>
                <div class="card-body">
                    <p class="card-text">Listado completo de clientes con información básica y estado de cuenta.</p>
                    <div class="mt-3">
                        <span class="badge badge-info">7 columnas</span>
                        <span class="badge badge-success">Compacto</span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.reportes.clientes') }}" class="btn btn-success btn-block">
                        <i class="fas fa-users mr-2"></i> Generar Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Propiedades -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">🏠 Propiedades</h3>
                </div>
                <div class="card-body">
                    <p class="card-text">Inventario de propiedades con estado de servicio y trabajos pendientes.</p>
                    <div class="mt-3">
                        <span class="badge badge-info">8 columnas</span>
                        <span class="badge badge-success">Compacto</span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.reportes.propiedades') }}" class="btn btn-info btn-block">
                        <i class="fas fa-home mr-2"></i> Generar Reporte
                    </a>
                </div>
            </div>
        </div>

        <!-- Reporte de Trabajos Pendientes -->
        <div class="col-md-6 col-lg-3">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">🛠️ Trabajos Pendientes</h3>
                </div>
                <div class="card-body">
                    <p class="card-text">Trabajos pendientes de ejecución ordenados por antigüedad.</p>
                    <div class="mt-3">
                        <span class="badge badge-info">7 columnas</span>
                        <span class="badge badge-success">Compacto</span>
                    </div>
                </div>
                <div class="card-footer">
                    <a href="{{ route('admin.reportes.trabajos-pendientes') }}" class="btn btn-warning btn-block">
                        <i class="fas fa-tools mr-2"></i> Generar Reporte
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Características de los Reportes -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">📄 Características de los Reportes</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>✅ Diseño Optimizado para Impresión</h5>
                            <ul>
                                <li><strong>Máximo 8 columnas</strong> por reporte</li>
                                <li><strong>~40 registros</strong> por hoja A4</li>
                                <li><strong>Fuentes compactas</strong> para ahorrar espacio</li>
                                <li><strong>Sin elementos decorativos</strong> innecesarios</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>🎯 Filtros Disponibles</h5>
                            <ul>
                                <li><strong>Filtro por Barrio</strong> en todos los reportes</li>
                                <li><strong>Filtro por Estado</strong> en clientes y propiedades</li>
                                <li><strong>Filtro por Tipo de Trabajo</strong> en trabajos pendientes</li>
                                <li><strong>Filtro por Meses de Mora</strong> en morosidad</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-info mt-3">
                        <h6><i class="fas fa-print mr-2"></i>Instrucciones para Imprimir</h6>
                        <p class="mb-0">
                            Cada reporte incluye un botón "🖨️ Imprimir/PDF" que optimiza automáticamente 
                            la vista para impresión, ocultando elementos innecesarios y ajustando el diseño.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .card-header {
            border-bottom: 2px solid rgba(0,0,0,0.1);
        }
        .badge {
            font-size: 0.7rem;
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Efecto hover en las tarjetas
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.15)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.boxShadow = '0 2px 4px rgba(0,0,0,0.1)';
                });
            });
        });
    </script>
@stop