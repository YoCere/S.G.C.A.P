@extends('adminlte::page')

@section('title', 'Registrar Pago')

@section('content_header')
    <h1>Registrar Pago de Agua</h1>
    <small class="text-muted">Registre pagos mensuales para propiedades</small>
@stop

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Errores encontrados:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Formulario de Pago</h3>
        </div>
        @if(session('success') && isset($esReconexion) && $esReconexion)
    <div class="alert alert-success">
        <i class="fas fa-bolt mr-2"></i>
        <strong>RECONEXIÓN SOLICITADA</strong>
        <p class="mb-0 mt-1">{{ session('success') }}</p>
    </div>
@endif

@if(isset($propiedadSeleccionada) && $propiedadSeleccionada->estado === \App\Models\Property::ESTADO_CORTADO)
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>PROPIEDAD CORTADA - RECONEXIÓN PENDIENTE</strong>
        <p class="mb-0 mt-1">
            <strong>Requisito para reconexión:</strong> Debe pagar <strong>TODOS</strong> los meses pendientes 
            + la multa de reconexión para que el servicio sea restablecido.
        </p>
        @php
            $mesesAdeudados = $propiedadSeleccionada->obtenerMesesAdeudados();
            $totalMeses = count($mesesAdeudados);
        @endphp
        @if($totalMeses > 0)
            <small class="text-muted">
                Meses adeudados: {{ $totalMeses }} ({{ implode(', ', array_map(function($mes) {
                    return \Carbon\Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('F Y');
                }, $mesesAdeudados)) }})
            </small>
        @endif
    </div>
@endif
        <div class="card-body">
            <form action="{{ route('admin.pagos.store') }}" method="POST" id="pagoForm">
                @csrf
                
                {{-- ✅ NUEVO: Campos para detectar reconexión --}}
                @if(isset($esReconexion) && $esReconexion)
                <input type="hidden" name="reconexion" value="1">
            @endif

            @if(isset($forzarPagoCompleto) && $forzarPagoCompleto)
                <input type="hidden" name="forzar_pago_completo" value="1">
            @endif

            @if(isset($mesDesdeReconexion))
                <input type="hidden" name="mes_desde_reconexion" value="{{ $mesDesdeReconexion }}">
            @endif

            @if(isset($mesHastaReconexion))
                <input type="hidden" name="mes_hasta_reconexion" value="{{ $mesHastaReconexion }}">
            @endif

            @if(isset($multaIdReconexion))
                <input type="hidden" name="multa_id_reconexion" value="{{ $multaIdReconexion }}">
            @endif
                                {{-- BUSCADOR DE CLIENTES/PROPIEDADES --}}
                <div class="form-group" id="buscadorGroup">
                    <label for="buscador">Buscar Cliente o Propiedad *</label>
                    <div class="input-group">
                        <input type="text" id="buscador" class="form-control" 
                               placeholder="Escriba nombre, CI del cliente o referencia de propiedad..."
                               autocomplete="off">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" onclick="limpiarBusqueda()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">
                        Comience a escribir para buscar clientes o propiedades
                    </small>
                    
                    {{-- RESULTADOS DE BÚSQUEDA --}}
                    <div id="resultadosBusqueda" class="mt-2" style="display: none;">
                        <div class="list-group" id="listaResultados"></div>
                    </div>
                </div>

                {{-- INFORMACIÓN DE LA PROPIEDAD SELECCIONADA --}}
                <div class="alert alert-success" id="infoPropiedad" style="display: none;">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Cliente:</strong> <span id="clienteNombre"></span>
                            <br>
                            <small class="text-muted" id="clienteCI"></small>
                        </div>
                        <div class="col-md-4">
                            <strong>Propiedad:</strong> <span id="propiedadReferencia"></span>
                            <br>
                            <small class="text-muted" id="propiedadBarrio"></small>
                        </div>
                        <div class="col-md-4">
                            <strong>Tarifa:</strong> <span id="tarifaMonto" class=""></span>/mes
                            <br>
                            <small class="text-muted" id="tarifaNombre"></small>
                        </div>
                    </div>
                    <input type="hidden" name="propiedad_id" id="propiedadId">
                </div>

                {{-- DEUDAS PENDIENTES --}}
                @if(isset($deudasPendientes) && $deudasPendientes->count() > 0)
                <div class="alert alert-danger mt-3" id="deudasPendientes">
                    <h6 class="alert-heading">
                        <i class="fas fa-exclamation-triangle mr-2"></i>Deudas Pendientes
                    </h6>
                    <div class="mt-2">
                        @foreach($deudasPendientes as $deuda)
                        <div class="deuda-item border-bottom pb-2 mb-2">
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>Emisión:</strong> 
                                    {{ \Carbon\Carbon::parse($deuda->fecha_emision)->format('d/m/Y') }}
                                </div>
                                <div class="col-md-4">
                                    <strong>Vencimiento:</strong> 
                                    <span class="{{ \Carbon\Carbon::parse($deuda->fecha_vencimiento)->isPast() ? '' : 'text-warning' }}">
                                        {{ \Carbon\Carbon::parse($deuda->fecha_vencimiento)->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Monto:</strong> 
                                    <span class="" font-weight-bold">
                                        Bs {{ number_format($deuda->monto_pendiente, 2) }}
                                    </span>
                                </div>
                            </div>
                            @if($deuda->multas && $deuda->multas->count() > 0)
                            <div class="mt-1">
                                <small class="text-muted">
                                    <i class="fas fa-balance-scale mr-1"></i>
                                    Incluye {{ $deuda->multas->count() }} multa(s)
                                </small>
                            </div>
                            @endif
                        </div>
                        @endforeach
                        
                        <div class="mt-2 pt-2 border-top">
                            <strong>Total Deudas Pendientes:</strong> 
                            <span class="text-danger font-weight-bold">
                                Bs {{ number_format($deudasPendientes->sum('monto_pendiente'), 2) }}
                            </span>
                        </div>
                    </div>
                </div>
                @elseif(isset($propiedadSeleccionada))
                <div class="alert alert-success mt-3" id="sinDeudas">
                    <i class="fas fa-check-circle mr-2"></i>
                    No hay deudas pendientes para esta propiedad
                </div>
                @endif

                {{-- ✅ NUEVA SECCIÓN: MULTAS PENDIENTES --}}
                <div class="alert alert-warning mt-3" id="seccionMultasPendientes" style="display: none;">
                    <h6 class="alert-heading">
                        <i class="fas fa-balance-scale mr-2"></i>Multas Pendientes
                    </h6>
                    <div class="mt-2" id="listaMultasPendientes">
                        {{-- Se llena automáticamente con JavaScript --}}
                    </div>
                    <div class="mt-2" id="resumenMultas" style="display: none;">
                        <div class="border-top pt-2">
                            <strong>Multas seleccionadas:</strong> 
                            <span id="totalMultasSeleccionadas" class="text-warning font-weight-bold">0</span>
                            <span class="text-success ml-2" id="montoTotalMultas">Bs 0.00</span>
                        </div>
                    </div>
                </div>

                {{-- ✅ CORREGIDO: MESES PENDIENTES DINÁMICOS --}}
                <div class="alert alert-warning mt-3" id="seccionMesesPendientes" style="display: none;">
                    <h6 class="alert-heading">
                        <i class="fas fa-calendar-check mr-2"></i>Meses para pagar disponibles
                    </h6>

                    {{-- ✅ NUEVO: Información de pago cronológico --}}
                    <div class="alert alert-info mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Importante:</strong> Debe pagar los meses en orden cronológico desde el más antiguo.
                        No puede saltar meses pendientes.
                    </div>

                    <div class="mt-2" id="listaMesesPendientes">
                        {{-- Se llena automáticamente con JavaScript --}}
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="seleccionarTodosMesesPendientes()">
                            <i class="fas fa-check-double mr-1"></i>Seleccionar Todos los Meses Pendientes
                        </button>
                    </div>
                </div>

                {{-- DETALLES DEL PAGO --}}
                <div class="card mt-4" id="detallesPago" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-money-bill-wave mr-2"></i>Detalles del Pago
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- ✅ CORREGIDO: SELECTS DINÁMICOS DE MESES PENDIENTES --}}
                        <div class="form-group">
                            <label>Meses a Pagar *</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="mes_desde">Desde:</label>
                                    <select name="mes_desde" id="mes_desde" class="form-control" required disabled>
                                        <option value="">Seleccione mes inicial</option>
                                        {{-- ✅ Se llena dinámicamente con JavaScript --}}
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="mes_hasta">Hasta:</label>
                                    <select name="mes_hasta" id="mes_hasta" class="form-control" required disabled>
                                        <option value="">Seleccione mes final</option>
                                        {{-- ✅ Se llena dinámicamente con JavaScript --}}
                                    </select>
                                </div>
                            </div>
                            <small class="form-text text-muted" id="textoMesesPendientes">
                                Seleccione el rango de meses pendientes que desea pagar
                            </small>
                        </div>

                        {{-- ✅ NUEVO: MENSAJE DE VALIDACIÓN EN TIEMPO REAL --}}
                        <div id="mensajeValidacionMeses" class="alert" style="display: none;"></div>

                        {{-- RESUMEN DE MESES SELECCIONADOS --}}
                        <div class="alert alert-info" id="resumenMeses" style="display: none;">
                            <strong>Meses a pagar:</strong>
                            <div id="listaMeses" class="mt-1"></div>
                            <small class="text-muted" id="totalMeses"></small>
                        </div>

                        <div class="row">
                            {{-- ✅ MODIFICADO: FECHA DE PAGO FIJA --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_pago">Fecha de Pago *</label>
                                    <input type="text" id="fecha_pago_display" 
                                           class="form-control bg-light" 
                                           value="{{ date('d/m/Y') }}" 
                                           readonly
                                           style="cursor: not-allowed;">
                                    <input type="hidden" name="fecha_pago" 
                                           value="{{ date('Y-m-d') }}">
                                    <small class="form-text text-muted">
                                        <i class="fas fa-lock mr-1"></i>Fecha actual del sistema
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="metodo">Método de Pago *</label>
                                    <select name="metodo" id="metodo" class="form-control" required>
                                        <option value="">Seleccione método</option>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="qr">QR</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="comprobante">N° Comprobante (opcional)</label>
                                    <input type="text" name="comprobante" id="comprobante" 
                                           class="form-control" 
                                           placeholder="N° de transferencia, recibo, etc.">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="observaciones">Observaciones (opcional)</label>
                            <textarea name="observaciones" id="observaciones" 
                                      class="form-control" 
                                      rows="2" placeholder="Observaciones adicionales..."></textarea>
                        </div>

                        {{-- RESUMEN FINAL --}}
                        <div class="alert alert-warning mt-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>Cliente:</strong> <span id="resumenCliente"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Propiedad:</strong> <span id="resumenPropiedad"></span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Meses:</strong> <span id="resumenMesesCount">0</span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Total a Pagar:</strong> 
                                    <span id="resumenTotal" class="text-success font-weight-bold">Bs 0.00</span>
                                </div>
                            </div>
                            {{-- ✅ NUEVO: DESGLOSE DEL TOTAL --}}
                            <div class="row mt-2" id="desgloseTotal" style="display: none;">
                                <div class="col-12">
                                    <small class="text-muted">
                                        <span id="desgloseMeses">0 meses</span> 
                                        + 
                                        <span id="desgloseMultas">0 multas</span>
                                    </small>
                                </div>
                            </div>
                        </div>
                        {{-- Después del campo de total a pagar --}}
                        <div id="multasContainer" class="mt-3"></div>

                        {{-- Para mostrar configuración actual --}}
                        @php
                            $configMora = \App\Models\ConfigMultaMora::first();
                        @endphp

                        @if($configMora)
                            <div class="alert alert-info small mt-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                <strong>Configuración de multas por mora:</strong><br>
                                <span class="ml-2">
                                    • Se aplica después de <strong>{{ $configMora->meses_gracia }} mes(es)</strong> de atraso<br>
                                    • Porcentaje: <strong>{{ $configMora->porcentaje_multa }}%</strong> sobre el monto base<br>
                                    • Estado: <span class="badge badge-{{ $configMora->activo ? 'success' : 'secondary' }}">
                                        {{ $configMora->activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- BOTONES --}}
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                        <i class="fas fa-save mr-1"></i>Registrar Pago(s)
                    </button>
                    <a href="{{ route('admin.pagos.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times mr-1"></i>Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
@stop
@section('css')
    <style>
         .list-group-item:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
    .list-group-item.active {
        background-color: #007bff;
        border-color: #007bff;
    }
    .mes-item {
        display: inline-block;
        background: #e9ecef;
        padding: 2px 8px;
        margin: 2px;
        border-radius: 3px;
        font-size: 0.85em;
    }
    select.form-control {
        pointer-events: all !important;
    }
    .deuda-item {
        font-size: 0.9em;
    }
    .mes-adeudado {
        font-size: 0.8em;
        cursor: pointer;
    }
    .mes-adeudado:hover {
        opacity: 0.8;
    }
    #mensajeValidacionMeses {
        transition: all 0.3s ease;
    }
    .multa-item {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 10px;
        margin-bottom: 8px;
        background: #f8f9fa;
    }
    .multa-item:hover {
        background: #e9ecef;
    }
    .multa-seleccionada {
        border-color: #ff3907;
        background: #852c02;
    }
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    /* ✅ NUEVO: Estilos para reconexión automática */
    .alert-reconexion {
        border-left: 4px solid #28a745;
        background: linear-gradient(135deg, #f8fff9 0%, #e8f5e8 100%);
    }
    .alert-reconexion .fa-bolt {
        color: #28a745;
    }
    /* Mejoras visuales para multas auto-seleccionadas en reconexión */
    .multa-auto-seleccionada {
        border: 2px solid #28a745 !important;
        background: #f8fff9 !important;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    
    /* ✅ CORREGIDO: Estilos para alerta de propiedad cortada */
    .alerta-cortada {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 15px;
        border-left: 4px solid #856404;
    }

    .alerta-cortada .contenido {
        flex: 1;
        min-width: 200px;
    }

    .alerta-cortada .boton-reconexion {
        flex-shrink: 0;
    }

    /* ✅ NUEVO: Estilos mejorados para el botón en fondo amarillo */
    .btn-outline-warning-improved {
        color: #856404;
        border-color: #856404;
        background-color: transparent;
        font-weight: 600;
        border-width: 2px;
    }

    .btn-outline-warning-improved:hover {
        color: #fff;
        background-color: #856404;
        border-color: #856404;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(133, 100, 4, 0.3);
    }

    .btn-outline-warning-improved:focus {
        box-shadow: 0 0 0 0.2rem rgba(133, 100, 4, 0.25);
    }

    .btn-outline-warning-improved:active {
        transform: translateY(0);
        box-shadow: none;
    }

    /* ✅ ALTERNATIVA: Botón con mejor contraste */
    .btn-warning-contrast {
        color: #fff;
        background-color: #e67700;
        border-color: #e67700;
        font-weight: 600;
    }

    .btn-warning-contrast:hover {
        color: #fff;
        background-color: #cc6a00;
        border-color: #cc6a00;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(230, 119, 0, 0.3);
    }

    .btn-warning-contrast:focus {
        box-shadow: 0 0 0 0.2rem rgba(230, 119, 0, 0.25);
    }

    .btn-warning-contrast:active {
        transform: translateY(0);
        box-shadow: none;
    }

    /* Responsive */
    @media (max-width: 576px) {
        .alerta-cortada {
            flex-direction: column;
            align-items: stretch;
        }
        
        .alerta-cortada .boton-reconexion {
            align-self: flex-end;
            margin-top: 8px;
        }
        
        .btn-outline-warning-improved,
        .btn-warning-contrast {
            width: 100%;
            text-align: center;
        }
    }
    
    /* Estilos para el formulario en modo reconexión */
    #buscadorGroup[style*="display: none"] + .alert-info {
        margin-top: 0;
    }
    </style>
@stop
@section('js')
<script>
    // =============================================
    // VARIABLES GLOBALES
    // =============================================
    let tarifaMensual = 0;
    let mesesPendientes = [];
    let multasPendientes = [];
    let timeoutBusqueda = null;

    // =============================================
    // ✅ NUEVO: AUTO-CONFIGURACIÓN PARA RECONEXIÓN
    // =============================================

    function configurarAutoReconexion() {
        // Verificar si viene de reconexión
        const urlParams = new URLSearchParams(window.location.search);
        const esReconexion = urlParams.has('reconexion');
        const mesDesdeReconexion = urlParams.get('mes_desde');
        const mesHastaReconexion = urlParams.get('mes_hasta');
        const multaIdReconexion = urlParams.get('multa_id');

        if (!esReconexion) return;

        console.log('🔄 Iniciando auto-configuración para reconexión...');

        // Ocultar buscador cuando viene de reconexión
        const buscadorGroup = document.getElementById('buscadorGroup');
        if (buscadorGroup) {
            buscadorGroup.style.display = 'none';
        }

        // Esperar a que se cargue la propiedad y datos
        setTimeout(() => {
            // Auto-seleccionar meses si están disponibles
            if (mesDesdeReconexion && mesHastaReconexion) {
                const mesDesde = document.getElementById('mes_desde');
                const mesHasta = document.getElementById('mes_hasta');
                
                if (mesDesde && mesHasta) {
                    mesDesde.value = mesDesdeReconexion;
                    mesHasta.value = mesHastaReconexion;
                    
                    console.log('✅ Meses auto-seleccionados:', mesDesdeReconexion, 'a', mesHastaReconexion);
                    
                    // Disparar eventos para actualizar resumen
                    if (mesDesde.dispatchEvent) {
                        mesDesde.dispatchEvent(new Event('change'));
                    }
                    if (mesHasta.dispatchEvent) {
                        mesHasta.dispatchEvent(new Event('change'));
                    }
                }
            }

            // Auto-seleccionar multa de reconexión
            if (multaIdReconexion) {
                setTimeout(() => {
                    const multaCheckbox = document.getElementById(`multa_${multaIdReconexion}`);
                    if (multaCheckbox) {
                        multaCheckbox.checked = true;
                        multaCheckbox.dispatchEvent(new Event('change'));
                        
                        // Agregar clase especial para multa auto-seleccionada
                        const multaItem = multaCheckbox.closest('.multa-item');
                        if (multaItem) {
                            multaItem.classList.add('multa-auto-seleccionada');
                        }
                        
                        console.log('✅ Multa de reconexión auto-seleccionada:', multaIdReconexion);
                    }
                }, 1000);
            }

            // Habilitar formulario
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
            }

            // Mostrar mensaje especial para reconexión
            mostrarMensajeReconexion();

        }, 1500);
    }

    function mostrarMensajeReconexion() {
        const alertaExistente = document.querySelector('.alert-reconexion');
        if (alertaExistente) return;

        const alerta = document.createElement('div');
        alerta.className = 'alert alert-success alert-reconexion';
        alerta.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-bolt mr-3 fa-2x"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">RECONEXIÓN AUTOMÁTICA CONFIGURADA</h5>
                    <p class="mb-0">El sistema ha precargado automáticamente todos los elementos necesarios para la reconexión.</p>
                    <small class="text-muted">Revise los datos y haga clic en "Registrar Pago" para completar el proceso.</small>
                </div>
                <button type="button" class="close" onclick="this.parentElement.parentElement.remove()">
                    <span>&times;</span>
                </button>
            </div>
        `;

        // Insertar al inicio del card-body
        const cardBody = document.querySelector('.card-body');
        if (cardBody) {
            cardBody.insertBefore(alerta, cardBody.firstChild);
        }
    }

    // =============================================
    // ✅ NUEVA FUNCIÓN: SOLICITAR RECONEXIÓN
    // =============================================

    function solicitarReconexion(propiedadId) {
        if (confirm('¿Solicitar reconexión para esta propiedad? Se aplicará multa automáticamente.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/properties/${propiedadId}/request-reconnection`;
            form.innerHTML = `
                @csrf
                @method('PUT')
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // =============================================
    // FUNCIONES GLOBALES
    // =============================================
    
    window.limpiarBusqueda = function() {
        const buscador = document.getElementById('buscador');
        const resultadosBusqueda = document.getElementById('resultadosBusqueda');
        const infoPropiedad = document.getElementById('infoPropiedad');
        const detallesPago = document.getElementById('detallesPago');
        const submitBtn = document.getElementById('submitBtn');
        const propiedadId = document.getElementById('propiedadId');
        const mesDesde = document.getElementById('mes_desde');
        const mesHasta = document.getElementById('mes_hasta');
        const resumenMeses = document.getElementById('resumenMeses');
        const seccionMesesPendientes = document.getElementById('seccionMesesPendientes');
        const seccionMultasPendientes = document.getElementById('seccionMultasPendientes');
        
        if (buscador) buscador.value = '';
        if (resultadosBusqueda) resultadosBusqueda.style.display = 'none';
        if (infoPropiedad) infoPropiedad.style.display = 'none';
        if (detallesPago) detallesPago.style.display = 'none';
        if (submitBtn) submitBtn.disabled = true;
        if (propiedadId) propiedadId.value = '';
        if (mesDesde) {
            mesDesde.innerHTML = '<option value="">Seleccione mes inicial</option>';
            mesDesde.disabled = true;
        }
        if (mesHasta) {
            mesHasta.innerHTML = '<option value="">Seleccione mes final</option>';
            mesHasta.disabled = true;
        }
        if (resumenMeses) resumenMeses.style.display = 'none';
        if (seccionMesesPendientes) seccionMesesPendientes.style.display = 'none';
        if (seccionMultasPendientes) seccionMultasPendientes.style.display = 'none';
        
        // Ocultar secciones
        const deudasPendientes = document.getElementById('deudasPendientes');
        const sinDeudas = document.getElementById('sinDeudas');
        if (deudasPendientes) deudasPendientes.style.display = 'none';
        if (sinDeudas) sinDeudas.style.display = 'none';
        
        // Mostrar buscador
        const buscadorGroup = document.getElementById('buscadorGroup');
        if (buscadorGroup) buscadorGroup.style.display = 'block';
        
        // Remover mensajes
        const alertInfo = document.querySelector('.alert-info');
        const alertReconexion = document.querySelector('.alert-reconexion');
        if (alertInfo) alertInfo.remove();
        if (alertReconexion) alertReconexion.remove();
        
        ocultarMensajeValidacion();
        
        // Resetear variables
        tarifaMensual = 0;
        mesesPendientes = [];
        multasPendientes = [];
        
        // Limpiar resumen
        actualizarResumenTotal();
    };
    
    window.mostrarBuscador = function() {
        window.limpiarBusqueda();
    };

    // =============================================
    // ✅ NUEVO: GESTIÓN DE MULTAS PENDIENTES
    // =============================================
    
    function cargarMultasPendientes(propiedadId) {
        const url = `/admin/pagos/obtener-multas-pendientes/${propiedadId}`;
        
        console.log('🔍 Cargando multas pendientes desde:', url);
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Error HTTP: ' + response.status);
                return response.json();
            })
            .then(data => {
                console.log('📊 DATOS DE MULTAS:', data);
                
                if (data.success && data.multasPendientes) {
                    multasPendientes = data.multasPendientes;
                    actualizarListaMultasPendientesUI(multasPendientes);
                } else {
                    console.log('ℹ️ No hay multas pendientes');
                    document.getElementById('seccionMultasPendientes').style.display = 'none';
                }
            })
            .catch(error => {
                console.error('💥 Error cargando multas pendientes:', error);
                document.getElementById('seccionMultasPendientes').style.display = 'none';
            });
    }
    
    function actualizarListaMultasPendientesUI(multas) {
        const listaMultasPendientes = document.getElementById('listaMultasPendientes');
        const seccionMultasPendientes = document.getElementById('seccionMultasPendientes');
        
        if (!listaMultasPendientes || !seccionMultasPendientes) return;
        
        listaMultasPendientes.innerHTML = '';
        
        if (multas.length > 0) {
            multas.forEach(multa => {
                const multaDiv = document.createElement('div');
                multaDiv.className = 'multa-item';
                multaDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input multa-checkbox" 
                               type="checkbox" 
                               name="multas_seleccionadas[]" 
                               value="${multa.id}" 
                               id="multa_${multa.id}"
                               onchange="actualizarResumenMultas()">
                        <label class="form-check-label w-100" for="multa_${multa.id}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${multa.nombre}</strong>
                                    <br>
                                    <small class="text-muted">${multa.descripcion}</small>
                                    <br>
                                    <small class="text-info">
                                        <i class="fas fa-calendar mr-1"></i>
                                        ${multa.fecha_aplicacion_formateada}
                                    </small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-warning">Bs ${parseFloat(multa.monto).toFixed(2)}</span>
                                    <br>
                                    <small class="text-muted">${multa.tipo_nombre}</small>
                                </div>
                            </div>
                        </label>
                    </div>
                `;
                listaMultasPendientes.appendChild(multaDiv);
            });
            
            seccionMultasPendientes.style.display = 'block';
        } else {
            seccionMultasPendientes.style.display = 'none';
        }
    }
    
    function actualizarResumenMultas() {
        const checkboxes = document.querySelectorAll('.multa-checkbox:checked');
        const totalMultasSeleccionadas = document.getElementById('totalMultasSeleccionadas');
        const montoTotalMultas = document.getElementById('montoTotalMultas');
        const resumenMultas = document.getElementById('resumenMultas');
        
        let totalMonto = 0;
        checkboxes.forEach(checkbox => {
            const multaId = checkbox.value;
            const multa = multasPendientes.find(m => m.id == multaId);
            if (multa) {
                totalMonto += parseFloat(multa.monto);
            }
        });
        
        totalMultasSeleccionadas.textContent = checkboxes.length;
        montoTotalMultas.textContent = `Bs ${totalMonto.toFixed(2)}`;
        
        if (checkboxes.length > 0) {
            resumenMultas.style.display = 'block';
        } else {
            resumenMultas.style.display = 'none';
        }
        
        // Actualizar el resumen total
        actualizarResumenTotal();
        
        // Actualizar clases visuales
        document.querySelectorAll('.multa-item').forEach(item => {
            const checkbox = item.querySelector('.multa-checkbox');
            if (checkbox.checked) {
                item.classList.add('multa-seleccionada');
            } else {
                item.classList.remove('multa-seleccionada');
                item.classList.remove('multa-auto-seleccionada');
            }
        });
    }

    // =============================================
    // ✅ ACTUALIZADO: CÁLCULO DEL TOTAL CON MULTAS
    // =============================================
    
    function actualizarResumenTotal() {
        const mesDesde = document.getElementById('mes_desde');
        const mesHasta = document.getElementById('mes_hasta');
        const resumenTotal = document.getElementById('resumenTotal');
        const resumenMesesCount = document.getElementById('resumenMesesCount');
        const desgloseTotal = document.getElementById('desgloseTotal');
        const desgloseMeses = document.getElementById('desgloseMeses');
        const desgloseMultas = document.getElementById('desgloseMultas');
        
        const desde = mesDesde.value;
        const hasta = mesHasta.value;
        
        // Calcular total de meses
        let totalMeses = 0;
        let totalMontoMeses = 0;
        
        if (desde && hasta && desde <= hasta) {
            const startYear = parseInt(desde.split('-')[0]);
            const startMonth = parseInt(desde.split('-')[1]) - 1;
            const endYear = parseInt(hasta.split('-')[0]);
            const endMonth = parseInt(hasta.split('-')[1]) - 1;
            
            let currentYear = startYear;
            let currentMonth = startMonth;
            
            while (currentYear < endYear || (currentYear === endYear && currentMonth <= endMonth)) {
                totalMeses++;
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
            }
            
            totalMontoMeses = totalMeses * tarifaMensual;
        }
        
        // Calcular total de multas
        const checkboxes = document.querySelectorAll('.multa-checkbox:checked');
        let totalMultas = 0;
        let totalMontoMultas = 0;
        
        checkboxes.forEach(checkbox => {
            const multaId = checkbox.value;
            const multa = multasPendientes.find(m => m.id == multaId);
            if (multa) {
                totalMultas++;
                totalMontoMultas += parseFloat(multa.monto);
            }
        });
        
        // Calcular total general
        const totalGeneral = totalMontoMeses + totalMontoMultas;
        
        // Actualizar interfaz
        resumenMesesCount.textContent = totalMeses;
        resumenTotal.textContent = `Bs ${totalGeneral.toFixed(2)}`;
        
        if (totalMeses > 0 || totalMultas > 0) {
            desgloseTotal.style.display = 'block';
            desgloseMeses.textContent = `${totalMeses} meses (Bs ${totalMontoMeses.toFixed(2)})`;
            desgloseMultas.textContent = `${totalMultas} multas (Bs ${totalMontoMultas.toFixed(2)})`;
        } else {
            desgloseTotal.style.display = 'none';
        }
    }

    // =============================================
    // ✅ CORREGIDO: GESTIÓN DE MESES PENDIENTES
    // =============================================
    
    function cargarMesesPendientes(propiedadId) {
        const url = `/admin/pagos/obtener-meses-pendientes/${propiedadId}`;
        
        console.log('🔍 Cargando meses pendientes desde:', url);
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Error HTTP: ' + response.status);
                return response.json();
            })
            .then(data => {
                console.log('📊 DATOS COMPLETOS DE LA API:', data);
                
                if (data.success && data.mesesPendientes) {
                    console.log('✅ Meses pendientes recibidos:', data.mesesPendientes);
                    console.log('✅ Total de meses:', data.totalPendientes);
                    
                    actualizarSelectsMeses(data.mesesPendientes);
                    actualizarListaMesesPendientesUI(data.mesesPendientes);
                    
                    document.getElementById('textoMesesPendientes').textContent = 
                        `Seleccione el rango de meses pendientes (${data.totalPendientes} disponibles)`;
                } else {
                    console.error('❌ Error en respuesta API:', data.message);
                    mostrarMensajeValidacion('error', 'Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('💥 Error cargando meses pendientes:', error);
                mostrarMensajeValidacion('error', 'Error al cargar meses pendientes');
            });
    }
    
    function actualizarSelectsMeses(mesesPendientesObj) {
        const mesDesde = document.getElementById('mes_desde');
        const mesHasta = document.getElementById('mes_hasta');
        
        // Convertir objeto a array y ordenar
        mesesPendientes = Object.entries(mesesPendientesObj)
            .map(([valor, texto]) => ({ valor, texto }))
            .sort((a, b) => a.valor.localeCompare(b.valor));
        
        // Limpiar selects
        mesDesde.innerHTML = '<option value="">Seleccione mes inicial</option>';
        mesHasta.innerHTML = '<option value="">Seleccione mes final</option>';
        
        // Llenar selects con meses pendientes
        mesesPendientes.forEach(mes => {
            const optionDesde = new Option(mes.texto, mes.valor);
            const optionHasta = new Option(mes.texto, mes.valor);
            mesDesde.add(optionDesde);
            mesHasta.add(optionHasta);
        });
        
        // Habilitar selects
        mesDesde.disabled = false;
        mesHasta.disabled = false;
        
        console.log('Selects actualizados con', mesesPendientes.length, 'meses pendientes');
    }
    
    function actualizarListaMesesPendientesUI(mesesPendientesObj) {
        const listaMesesPendientes = document.getElementById('listaMesesPendientes');
        const seccionMesesPendientes = document.getElementById('seccionMesesPendientes');
        
        if (!listaMesesPendientes || !seccionMesesPendientes) return;
        
        listaMesesPendientes.innerHTML = '';
        
        const mesesArray = Object.entries(mesesPendientesObj)
            .map(([valor, texto]) => ({ valor, texto }))
            .sort((a, b) => a.valor.localeCompare(b.valor));
        
        if (mesesArray.length > 0) {
            mesesArray.forEach(mes => {
                const badge = document.createElement('span');
                badge.className = 'badge badge-warning mr-1 mb-1 mes-adeudado';
                badge.textContent = mes.texto;
                badge.style.cursor = 'pointer';
                badge.onclick = function() {
                    seleccionarMesEspecifico(mes.valor);
                };
                listaMesesPendientes.appendChild(badge);
            });
            
            seccionMesesPendientes.style.display = 'block';
        } else {
            seccionMesesPendientes.style.display = 'none';
            mostrarMensajeValidacion('info', 'Esta propiedad no tiene meses pendientes de pago');
        }
    }
    
    function seleccionarMesEspecifico(mes) {
    const mesesPendientesOrdenados = [...mesesPendientes].sort((a, b) => a.valor.localeCompare(b.valor));
    const primerMes = mesesPendientesOrdenados[0].valor;
    const mesIndex = mesesPendientesOrdenados.findIndex(m => m.valor === mes);
    
    // Si intenta seleccionar un mes que no es el primero
    if (mes !== primerMes) {
        const primerMesTexto = mesesPendientesOrdenados[0].texto;
        const mesSeleccionadoTexto = mesesPendientesOrdenados.find(m => m.valor === mes)?.texto;
        
        // Calcular los meses que debería pagar primero
        const mesesNecesarios = mesesPendientesOrdenados.slice(0, mesIndex + 1).map(m => m.texto).join(', ');
        
        if (!confirm(`Para pagar ${mesSeleccionadoTexto}, primero debe pagar: ${mesesNecesarios}\n\n¿Desea seleccionar automáticamente desde ${primerMesTexto} hasta ${mesSeleccionadoTexto}?`)) {
            return;
        }
        
        // Forzar selección desde el primer mes
        document.getElementById('mes_desde').value = primerMes;
        document.getElementById('mes_hasta').value = mes;
    } else {
        // Si selecciona el primer mes, permitir selección individual
        document.getElementById('mes_desde').value = mes;
        document.getElementById('mes_hasta').value = mes;
    }
    
    actualizarResumenMeses();
    validarSeleccionCronologica();
    actualizarResumenTotal();
}

    // ✅ MODIFICADO: Seleccionar todos los meses - ahora selecciona desde el primero
    window.seleccionarTodosMesesPendientes = function() {
        if (mesesPendientes.length === 0) {
            alert('No hay meses pendientes para seleccionar');
            return;
        }
        
        const mesesOrdenados = [...mesesPendientes].sort((a, b) => a.valor.localeCompare(b.valor));
        const primerMes = mesesOrdenados[0].valor;
        const ultimoMes = mesesOrdenados[mesesOrdenados.length - 1].valor;
        
        document.getElementById('mes_desde').value = primerMes;
        document.getElementById('mes_hasta').value = ultimoMes;
        actualizarResumenMeses();
        validarMesesEnTiempoReal();
        actualizarResumenTotal();
        
        alert(`Se seleccionaron ${mesesPendientes.length} meses pendientes automáticamente desde ${mesesOrdenados[0].texto}`);
    };
    // =============================================
    // ✅ ACTUALIZADO: RESÚMEN DE MESES
    // =============================================
    
    function actualizarResumenMeses() {
        const mesDesde = document.getElementById('mes_desde');
        const mesHasta = document.getElementById('mes_hasta');
        const resumenMeses = document.getElementById('resumenMeses');
        const listaMeses = document.getElementById('listaMeses');
        const totalMeses = document.getElementById('totalMeses');
        
        const desde = mesDesde.value;
        const hasta = mesHasta.value;
        
        if (!desde || !hasta) {
            resumenMeses.style.display = 'none';
            actualizarResumenTotal();
            return;
        }
        
        if (desde > hasta) {
            resumenMeses.style.display = 'none';
            actualizarResumenTotal();
            return;
        }
        
        // Calcular meses en el rango
        const startYear = parseInt(desde.split('-')[0]);
        const startMonth = parseInt(desde.split('-')[1]) - 1;
        const endYear = parseInt(hasta.split('-')[0]);
        const endMonth = parseInt(hasta.split('-')[1]) - 1;
        
        const meses = [];
        let currentYear = startYear;
        let currentMonth = startMonth;
        
        while (currentYear < endYear || (currentYear === endYear && currentMonth <= endMonth)) {
            const fecha = new Date(currentYear, currentMonth, 15);
            meses.push(new Date(fecha));
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
        }
        
        // Mostrar meses
        listaMeses.innerHTML = '';
        meses.forEach(mes => {
            const mesFormateado = mes.toLocaleDateString('es-ES', { 
                year: 'numeric', 
                month: 'long' 
            });
            const span = document.createElement('span');
            span.className = 'mes-item';
            span.textContent = mesFormateado;
            listaMeses.appendChild(span);
        });
        
        // Calcular total
        const totalMesesCount = meses.length;
        const totalPago = totalMesesCount * tarifaMensual;
        
        totalMeses.textContent = `Total: ${totalMesesCount} mes(es) × Bs ${tarifaMensual.toFixed(2)} = Bs ${totalPago.toFixed(2)}`;
        resumenMeses.style.display = 'block';
        
        // Actualizar el resumen total
        actualizarResumenTotal();
    }

    // =============================================
    // ✅ CORREGIDO: VALIDACIÓN EN TIEMPO REAL
    // =============================================
    
    function validarMesesEnTiempoReal() {
        const propiedadId = document.getElementById('propiedadId').value;
        const mesDesde = document.getElementById('mes_desde').value;
        const mesHasta = document.getElementById('mes_hasta').value;
        
        if (!propiedadId || !mesDesde || !mesHasta) {
            ocultarMensajeValidacion();
            return;
        }
        
        if (mesDesde > mesHasta) {
            mostrarMensajeValidacion('error', 'El mes final no puede ser anterior al mes inicial');
            document.getElementById('submitBtn').disabled = true;
            return;
        }
        
        // ✅ SOLUCIÓN TEMPORAL: Validación local sin llamada al servidor
        const esValido = validarRangoLocalmente(mesDesde, mesHasta);
        
        if (esValido) {
            mostrarMensajeValidacion('success', 'Rango de meses válido');
            document.getElementById('submitBtn').disabled = false;
        } else {
            mostrarMensajeValidacion('warning', 'El rango seleccionado contiene meses ya pagados');
            document.getElementById('submitBtn').disabled = true;
        }
    }

    function validarRangoLocalmente(mesDesde, mesHasta) {
        // Validación simple: si los meses están en la lista de pendientes, son válidos
        const desdeIndex = mesesPendientes.findIndex(mes => mes.valor === mesDesde);
        const hastaIndex = mesesPendientes.findIndex(mes => mes.valor === mesHasta);
        
        return desdeIndex !== -1 && hastaIndex !== -1 && desdeIndex <= hastaIndex;
    }
    
    function mostrarMensajeValidacion(tipo, mensaje) {
        const mensajeDiv = document.getElementById('mensajeValidacionMeses');
        const iconos = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-triangle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        
        mensajeDiv.className = `alert alert-${tipo}`;
        mensajeDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas ${iconos[tipo]} mr-2"></i>
                <div class="flex-grow-1">${mensaje}</div>
                <button type="button" class="close" onclick="ocultarMensajeValidacion()">
                    <span>&times;</span>
                </button>
            </div>
        `;
        mensajeDiv.style.display = 'block';
    }
    
    function ocultarMensajeValidacion() {
        const mensajeDiv = document.getElementById('mensajeValidacionMeses');
        mensajeDiv.style.display = 'none';
    }

    // =============================================
    // ✅ CORREGIDO: FUNCIÓN SELECCIONAR PROPIEDAD CON BOTÓN DE RECONEXIÓN MEJORADO
    // =============================================
    
    function validarSeleccionCronologica() {
    const mesDesde = document.getElementById('mes_desde').value;
    const mesHasta = document.getElementById('mes_hasta').value;
    
    if (!mesDesde || !mesHasta || mesesPendientes.length === 0) {
        return;
    }
    
    const mesesOrdenados = [...mesesPendientes].sort((a, b) => a.valor.localeCompare(b.valor));
    const primerMesPendiente = mesesOrdenados[0].valor;
    
    // Validar que empiece desde el primer mes
    if (mesDesde !== primerMesPendiente) {
        const primerMesTexto = mesesOrdenados[0].texto;
        const mesDesdeTexto = mesesPendientes.find(m => m.valor === mesDesde)?.texto;
        
        mostrarMensajeValidacion('warning', 
            `Debe pagar desde el primer mes pendiente (${primerMesTexto}). ` +
            `No puede pagar desde ${mesDesdeTexto} sin antes pagar los meses anteriores.`
        );
        
        document.getElementById('submitBtn').disabled = true;
        return;
    }
    
    // Validar que no haya saltos en la selección
    const desdeIndex = mesesOrdenados.findIndex(m => m.valor === mesDesde);
    const hastaIndex = mesesOrdenados.findIndex(m => m.valor === mesHasta);
    
    if (hastaIndex - desdeIndex + 1 !== (hastaIndex + 1)) {
        const mesesEsperados = mesesOrdenados.slice(0, hastaIndex + 1).map(m => m.texto).join(', ');
        
        mostrarMensajeValidacion('warning', 
            `Debe pagar los meses en orden secuencial: ${mesesEsperados}. ` +
            `No puede saltar meses pendientes.`
        );
        
        document.getElementById('submitBtn').disabled = true;
        return;
    }
    
    // Si pasa todas las validaciones
    ocultarMensajeValidacion();
    document.getElementById('submitBtn').disabled = false;
}


    function seleccionarPropiedad(propiedad) {
        console.log('Seleccionando propiedad:', propiedad);
        
        // ✅ CORREGIDO: Botón con mejor visibilidad en fondo amarillo
        if (propiedad.estado === 'cortado') {
            const alertaExistente = document.getElementById('alertaCortado');
            if (alertaExistente) alertaExistente.remove();
            
            const alerta = document.createElement('div');
            alerta.id = 'alertaCortado';
            alerta.className = 'alert alert-warning mt-3 alerta-cortada';
            alerta.innerHTML = `
                <div class="contenido">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <strong>Propiedad CORTADA:</strong> Esta propiedad está actualmente sin servicio.
                </div>
                <div class="boton-reconexion">
                    <button type="button" class="btn btn-warning-contrast btn-sm" 
                            onclick="solicitarReconexion(${propiedad.id})">
                        <i class="fas fa-plug mr-1"></i>Solicitar Reconexión
                    </button>
                </div>
            `;
            
            const buscadorGroup = document.getElementById('buscadorGroup');
            if (buscadorGroup && buscadorGroup.parentNode) {
                buscadorGroup.parentNode.insertBefore(alerta, buscadorGroup.nextSibling);
            }
        }
        
        // Actualizar información mostrada
        document.getElementById('clienteNombre').textContent = propiedad.cliente_nombre;
        document.getElementById('clienteCI').textContent = propiedad.cliente_ci ? `CI: ${propiedad.cliente_ci}` : 'Sin CI';
        document.getElementById('propiedadReferencia').textContent = propiedad.referencia;
        document.getElementById('propiedadBarrio').textContent = propiedad.barrio ? `Barrio: ${propiedad.barrio}` : 'Sin barrio';
        document.getElementById('tarifaMonto').textContent = `Bs ${parseFloat(propiedad.tarifa_precio).toFixed(2)}`;
        document.getElementById('tarifaNombre').textContent = propiedad.tarifa_nombre;
        
        // Actualizar resumen
        document.getElementById('resumenCliente').textContent = propiedad.cliente_nombre;
        document.getElementById('resumenPropiedad').textContent = propiedad.referencia;
        
        // Guardar datos
        document.getElementById('propiedadId').value = propiedad.id;
        tarifaMensual = parseFloat(propiedad.tarifa_precio);
        
        // Mostrar secciones
        document.getElementById('infoPropiedad').style.display = 'block';
        document.getElementById('detallesPago').style.display = 'block';
        document.getElementById('resultadosBusqueda').style.display = 'none';
        document.getElementById('buscador').value = `${propiedad.referencia} - ${propiedad.cliente_nombre}`;
        
        // ✅ Cargar datos pendientes automáticamente
        cargarMesesPendientes(propiedad.id);
        cargarMultasPendientes(propiedad.id);
        cargarDeudasPendientes(propiedad.id);
        
        console.log('Propiedad seleccionada - Cargando datos pendientes...');
    }
    
    function cargarDeudasPendientes(propiedadId) {
        const url = `/admin/properties/${propiedadId}/deudaspendientes`;
        
        console.log('Cargando deudas desde:', url);
        
        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error(`Error HTTP: ${response.status}`);
                return response.json();
            })
            .then(data => {
                console.log('Deudas cargadas exitosamente:', data);
            })
            .catch(error => {
                console.error('Error cargando deudas:', error);
            });
    }

    // =============================================
    // INICIALIZACIÓN PRINCIPAL MEJORADA
    // =============================================
    
    document.addEventListener('DOMContentLoaded', function() {
        configurarModoReconexion();
        console.log('DOM completamente cargado - Inicializando script de pagos');
        
        const buscador = document.getElementById('buscador');
        const resultadosBusqueda = document.getElementById('resultadosBusqueda');
        const listaResultados = document.getElementById('listaResultados');
        const mesDesde = document.getElementById('mes_desde');
        const mesHasta = document.getElementById('mes_hasta');
        
        // Event listeners para meses
        mesDesde.addEventListener('change', function() {
            actualizarResumenMeses();
            validarMesesEnTiempoReal();
            validarSeleccionCronologica();
            actualizarResumenTotal();
        });
        
        mesHasta.addEventListener('change', function() {
            actualizarResumenMeses();
            validarMesesEnTiempoReal();
            validarSeleccionCronologica();
            actualizarResumenTotal();
        });
        
        // Búsqueda en tiempo real
        buscador.addEventListener('input', function() {
            clearTimeout(timeoutBusqueda);
            const query = this.value.trim();
            
            if (query.length < 2) {
                resultadosBusqueda.style.display = 'none';
                return;
            }
            
            timeoutBusqueda = setTimeout(() => {
                buscarPropiedades(query);
            }, 300);
        });
        
        function buscarPropiedades(query) {
            const url = `/admin/propiedades/buscar?q=${encodeURIComponent(query)}`;
            
            fetch(url)
                .then(response => {
                    if (!response.ok) throw new Error('Error HTTP: ' + response.status);
                    return response.json();
                })
                .then(data => mostrarResultados(data))
                .catch(error => {
                    console.error('Error en búsqueda:', error);
                    listaResultados.innerHTML = `
                        <div class="list-group-item text-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Error al buscar propiedades
                        </div>
                    `;
                    resultadosBusqueda.style.display = 'block';
                });
        }
        
        function mostrarResultados(propiedades) {
            listaResultados.innerHTML = '';
            
            if (propiedades.length === 0) {
                listaResultados.innerHTML = `
                    <div class="list-group-item text-muted">
                        <i class="fas fa-search mr-2"></i>No se encontraron propiedades para "${buscador.value}"
                    </div>
                `;
            } else {
                propiedades.forEach(propiedad => {
                    // Determinar badge según estado
                    let estadoBadge = '';
                    if (propiedad.estado === 'cortado') {
                        estadoBadge = '<span class="badge badge-danger ml-2">CORTADO</span>';
                    } else if (propiedad.estado === 'corte_pendiente') {
                        estadoBadge = '<span class="badge badge-warning ml-2">CORTE PENDIENTE</span>';
                    } else if (propiedad.estado === 'activo') {
                        estadoBadge = '<span class="badge badge-success ml-2">ACTIVO</span>';
                    }
                    
                    const item = document.createElement('div');
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = `
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${propiedad.referencia} ${estadoBadge}</h6>
                            <small class="text-success">Bs ${parseFloat(propiedad.tarifa_precio).toFixed(2)}/mes</small>
                        </div>
                        <p class="mb-1">
                            <strong>Cliente:</strong> ${propiedad.cliente_nombre}
                            ${propiedad.cliente_ci ? `(CI: ${propiedad.cliente_ci})` : ''}
                        </p>
                        <small class="text-muted">
                            ${propiedad.barrio ? `Barrio: ${propiedad.barrio} • ` : ''}
                            Tarifa: ${propiedad.tarifa_nombre}
                        </small>
                    `;
                    item.addEventListener('click', function() {
                        seleccionarPropiedad(propiedad);
                    });
                    listaResultados.appendChild(item);
                });
            }
            
            resultadosBusqueda.style.display = 'block';
        }
        
        // ✅ NUEVO: Configurar auto-reconexión si aplica
        setTimeout(() => {
            configurarAutoReconexion();
        }, 500);
        
        // Auto-selección si viene propiedad por URL
        @if(isset($propiedadSeleccionada) && $propiedadSeleccionada)
            console.log('Iniciando auto-selección para propiedad ID:', {{ $propiedadSeleccionada->id }});
            
            const propiedadData = {
                id: {{ $propiedadSeleccionada->id }},
                referencia: '{{ addslashes($propiedadSeleccionada->referencia) }}',
                cliente_nombre: '{{ addslashes($propiedadSeleccionada->client->nombre) }}',
                cliente_ci: '{{ $propiedadSeleccionada->client->ci ?? '' }}',
                barrio: '{{ $propiedadSeleccionada->barrio ?? '' }}',
                tarifa_precio: {{ $propiedadSeleccionada->tariff->precio_mensual ?? 0 }},
                tarifa_nombre: '{{ $propiedadSeleccionada->tariff->nombre ?? 'N/A' }}',
                estado: '{{ $propiedadSeleccionada->estado }}'
            };
            
            setTimeout(() => {
                console.log('Ejecutando auto-selección...');
                seleccionarPropiedad(propiedadData);
                
                // ✅ MEJORADO: Ocultar buscador cuando viene con propiedad seleccionada
                const buscadorGroup = document.getElementById('buscadorGroup');
                if (buscadorGroup) {
                    buscadorGroup.style.display = 'none';
                }
                
                // ✅ MEJORADO: Mensaje informativo
                const infoHeader = document.createElement('div');
                infoHeader.className = 'alert alert-info mb-3';
                infoHeader.innerHTML = `
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Pago rápido:</strong> Está pagando la propiedad <strong>${propiedadData.referencia}</strong> 
                    del cliente <strong>${propiedadData.cliente_nombre}</strong>
                    <a href="javascript:void(0)" onclick="mostrarBuscador()" class="float-right">
                        <small><i class="fas fa-search mr-1"></i>Cambiar propiedad</small>
                    </a>
                `;
                const cardBody = document.querySelector('.card-body');
                if (cardBody) {
                    cardBody.insertBefore(infoHeader, cardBody.firstChild);
                }
                
                console.log('Auto-selección completada exitosamente');
                
                // ✅ NUEVO: Si es reconexión, auto-configurar meses y multas
                @if(isset($esReconexion) && $esReconexion)
                    console.log('🔄 Configurando auto-reconexión...');
                    
                    // Auto-seleccionar meses si están disponibles
                    @if(isset($mesDesdeReconexion) && isset($mesHastaReconexion))
                        setTimeout(() => {
                            const mesDesde = document.getElementById('mes_desde');
                            const mesHasta = document.getElementById('mes_hasta');
                            
                            if (mesDesde && mesHasta) {
                                mesDesde.value = '{{ $mesDesdeReconexion }}';
                                mesHasta.value = '{{ $mesHastaReconexion }}';
                                
                                // Disparar eventos para actualizar
                                if (typeof actualizarResumenMeses === 'function') {
                                    actualizarResumenMeses();
                                }
                                if (typeof validarMesesEnTiempoReal === 'function') {
                                    validarMesesEnTiempoReal();
                                }
                                if (typeof actualizarResumenTotal === 'function') {
                                    actualizarResumenTotal();
                                }
                                
                                console.log('✅ Meses de reconexión auto-seleccionados');
                            }
                        }, 1000);
                    @endif
                    
                    // Auto-seleccionar multa si está disponible
                    @if(isset($multaIdReconexion))
                        setTimeout(() => {
                            const multaCheckbox = document.getElementById(`multa_{{ $multaIdReconexion }}`);
                            if (multaCheckbox) {
                                multaCheckbox.checked = true;
                                if (typeof actualizarResumenMultas === 'function') {
                                    actualizarResumenMultas();
                                }
                                
                                // Agregar clase especial para multa auto-seleccionada
                                const multaItem = multaCheckbox.closest('.multa-item');
                                if (multaItem) {
                                    multaItem.classList.add('multa-auto-seleccionada');
                                }
                                
                                console.log('✅ Multa de reconexión auto-seleccionada');
                            }
                        }, 1500);
                    @endif
                    
                    // Mostrar mensaje especial de reconexión
                    mostrarMensajeReconexion();
                    
                @endif
                
            }, 300);
        @endif
        
        // ✅ NUEVO: Cargar multas pendientes si vienen por URL
        @if(isset($multasPendientes) && $multasPendientes->count() > 0)
            @php
                $multasData = $multasPendientes->map(function($multa) {
                    return [
                        'id' => $multa->id,
                        'nombre' => $multa->nombre,
                        'descripcion' => $multa->descripcion,
                        'monto' => $multa->monto,
                        'tipo_nombre' => $multa->nombre_tipo,
                        'fecha_aplicacion_formateada' => \Carbon\Carbon::parse($multa->fecha_aplicacion)->format('d/m/Y')
                    ];
                })->toArray();
            @endphp
            
            setTimeout(() => {
                console.log('Cargando multas pendientes desde PHP...');
                const multasData = @json($multasData);
                multasPendientes = multasData;
                actualizarListaMultasPendientesUI(multasPendientes);
            }, 500);
        @endif
    });
    // EN resources/views/admin/pagos/create.blade.php - AGREGAR esta función:

    // ✅ NUEVO: Validar formulario antes de enviar
        document.getElementById('pagoForm').addEventListener('submit', function(e) {
            const propiedadId = document.getElementById('propiedadId').value;
            const mesDesde = document.getElementById('mes_desde').value;
            const mesHasta = document.getElementById('mes_hasta').value;
            
            if (!propiedadId) {
                e.preventDefault();
                alert('Debe seleccionar una propiedad antes de registrar el pago.');
                return;
            }
            
            if (!mesDesde || !mesHasta) {
                e.preventDefault();
                alert('Debe seleccionar el rango de meses a pagar.');
                return;
            }
            
            if (mesDesde > mesHasta) {
                e.preventDefault();
                alert('El mes final no puede ser anterior al mes inicial.');
                return;
            }
            
            // Mostrar loading
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Procesando...';
        });

        function configurarModoReconexion() {
        const urlParams = new URLSearchParams(window.location.search);
        const forzarPagoCompleto = urlParams.has('forzar_pago_completo');
        
        if (forzarPagoCompleto) {
            // Deshabilitar cambios en meses
            const mesDesde = document.getElementById('mes_desde');
            const mesHasta = document.getElementById('mes_hasta');
            const btnSeleccionarTodos = document.querySelector('button[onclick*="seleccionarTodosMesesPendientes"]');
            
            if (mesDesde && mesHasta) {
                mesDesde.disabled = true;
                mesHasta.disabled = true;
                
                // Ocultar botón de seleccionar todos (ya están todos seleccionados)
                if (btnSeleccionarTodos) {
                    btnSeleccionarTodos.style.display = 'none';
                }
                
                // Mostrar mensaje informativo
                const infoMeses = document.createElement('div');
                infoMeses.className = 'alert alert-info mt-2';
                infoMeses.innerHTML = `
                    <i class="fas fa-lock mr-2"></i>
                    <strong>Modo reconexión:</strong> Debe pagar todos los meses adeudados + la multa de reconexión.
                    Los meses han sido bloqueados para asegurar el pago completo.
                `;
                
                const seccionMeses = document.querySelector('.form-group:has(#mes_desde)');
                if (seccionMeses) {
                    seccionMeses.appendChild(infoMeses);
                }
            }
        }
    }
    // En tu JavaScript, cambia la función para usar meses enteros:
function calcularMultasPorMora(mesesSeleccionados, tarifaMensual) {
    if (!mesesSeleccionados || !mesesSeleccionados.length || !tarifaMensual) {
        return {
            aplicaMulta: false,
            totalMultas: 0,
            mesesConMulta: [],
            detalleMultas: []
        };
    }

    const configMora = @json(\App\Models\ConfigMultaMora::first());
    
    if (!configMora || !configMora.activo) {
        return {
            aplicaMulta: false,
            totalMultas: 0,
            mesesConMulta: [],
            detalleMultas: []
        };
    }

    const hoy = new Date();
    const hoyMes = hoy.getFullYear() * 12 + hoy.getMonth();
    const hoyAnio = hoy.getFullYear();
    const hoyMesNum = hoy.getMonth();
    
    let totalMultas = 0;
    let mesesConMulta = [];
    let detalleMultas = [];

    mesesSeleccionados.forEach(mes => {
        const [year, month] = mes.split('-').map(Number);
        
        // Calcular diferencia en meses enteros
        const mesesAtrasados = ((hoyAnio - year) * 12) + (hoyMesNum - (month - 1));
        
        // Solo considerar atraso si es positivo
        if (mesesAtrasados > 0 && mesesAtrasados >= configMora.meses_gracia) {
            const multa = tarifaMensual * (configMora.porcentaje_multa / 100);
            totalMultas += multa;
            mesesConMulta.push(mes);
            
            detalleMultas.push({
                mes: mes,
                mesesAtrasados: mesesAtrasados,
                multa: multa,
                montoBase: tarifaMensual,
                porcentaje: configMora.porcentaje_multa
            });
        }
    });

    return {
        aplicaMulta: totalMultas > 0,
        totalMultas: totalMultas,
        mesesConMulta: mesesConMulta,
        detalleMultas: detalleMultas,
        configuracion: configMora
    };
}

// Función para actualizar el total del pago con multas
function actualizarTotalConMultas(mesesSeleccionados, tarifaMensual) {
    const totalMeses = mesesSeleccionados.length;
    const montoBaseTotal = totalMeses * tarifaMensual;
    
    const multasInfo = calcularMultasPorMora(mesesSeleccionados, tarifaMensual);
    
    let html = '';
    
    if (multasInfo.aplicaMulta) {
        html += `
            <div class="alert alert-warning mt-3" id="multaInfo">
                <h6><i class="fas fa-exclamation-triangle mr-2"></i>Multas por mora aplicables</h6>
                <hr class="my-2">
                <div class="row">
                    <div class="col-md-6">
                        <small class="d-block"><strong>Meses con multa:</strong> ${multasInfo.mesesConMulta.length}</small>
                        <small class="d-block"><strong>Total multas:</strong> Bs ${multasInfo.totalMultas.toFixed(2)}</small>
                    </div>
                    <div class="col-md-6">
                        <small class="d-block"><strong>Monto base:</strong> Bs ${montoBaseTotal.toFixed(2)}</small>
                        <small class="d-block"><strong>Total a pagar:</strong> Bs ${(montoBaseTotal + multasInfo.totalMultas).toFixed(2)}</small>
                    </div>
                </div>
        `;
        
        if (multasInfo.detalleMultas.length > 0) {
            html += `<hr class="my-2">`;
            multasInfo.detalleMultas.forEach(detalle => {
                const fecha = new Date(detalle.mes + '-01');
                const mesNombre = fecha.toLocaleDateString('es-ES', { month: 'long', year: 'numeric' });
                html += `
                    <small class="d-block">
                        <strong>${mesNombre}:</strong> ${detalle.mesesAtrasados} mes(es) atrasado → 
                        Multa: ${detalle.porcentaje}% = Bs ${detalle.multa.toFixed(2)}
                    </small>
                `;
            });
        }
        
        html += `</div>`;
    }
    
    return {
        html: html,
        total: montoBaseTotal + multasInfo.totalMultas,
        montoBase: montoBaseTotal,
        multas: multasInfo.totalMultas,
        tieneMultas: multasInfo.aplicaMulta
    };
}

// Usar en tu formulario cuando seleccionan meses
document.addEventListener('DOMContentLoaded', function() {
    const mesesSelect = document.querySelector('[name="meses_pagados[]"]');
    const montoBaseInput = document.querySelector('#montoBase');
    const totalPagarInput = document.querySelector('#totalPagar');
    const multasContainer = document.querySelector('#multasContainer');
    
    if (mesesSelect) {
        mesesSelect.addEventListener('change', function() {
            const mesesSeleccionados = Array.from(this.selectedOptions).map(opt => opt.value);
            const tarifaMensual = parseFloat(montoBaseInput?.value) || 0;
            
            const resultado = actualizarTotalConMultas(mesesSeleccionados, tarifaMensual);
            
            // Actualizar total
            if (totalPagarInput) {
                totalPagarInput.value = resultado.total.toFixed(2);
            }
            
            // Mostrar información de multas
            if (multasContainer) {
                multasContainer.innerHTML = resultado.html;
            }
        });
    }
});

</script>
@stop