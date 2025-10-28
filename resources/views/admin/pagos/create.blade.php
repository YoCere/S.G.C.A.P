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
        <div class="card-body">
            <form action="{{ route('admin.pagos.store') }}" method="POST" id="pagoForm">
                @csrf

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
            border-color: #ffc107;
            background: #fff3cd;
        }
        .bg-light {
            background-color: #f8f9fa !important;
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
        if (alertInfo) alertInfo.remove();
        
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
        document.getElementById('mes_desde').value = mes;
        document.getElementById('mes_hasta').value = mes;
        actualizarResumenMeses();
        validarMesesEnTiempoReal();
        actualizarResumenTotal();
    }
    
    window.seleccionarTodosMesesPendientes = function() {
        if (mesesPendientes.length === 0) {
            alert('No hay meses pendientes para seleccionar');
            return;
        }
        
        const primerMes = mesesPendientes[0].valor;
        const ultimoMes = mesesPendientes[mesesPendientes.length - 1].valor;
        
        document.getElementById('mes_desde').value = primerMes;
        document.getElementById('mes_hasta').value = ultimoMes;
        actualizarResumenMeses();
        validarMesesEnTiempoReal();
        actualizarResumenTotal();
        
        alert(`Se seleccionaron ${mesesPendientes.length} meses pendientes automáticamente`);
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
    // FUNCIONES EXISTENTES (MANTENIDAS)
    // =============================================
    
    function seleccionarPropiedad(propiedad) {
    console.log('Seleccionando propiedad:', propiedad);
    
    // ✅ NUEVO: Mostrar alerta si está cortada (VERSIÓN SEGURA)
    if (propiedad.estado === 'cortado') {
        const alertaExistente = document.getElementById('alertaCortado');
        if (alertaExistente) alertaExistente.remove();
        
        const alerta = document.createElement('div');
        alerta.id = 'alertaCortado';
        alerta.className = 'alert alert-warning mt-3';
        alerta.innerHTML = `
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Propiedad CORTADA:</strong> Esta propiedad está actualmente sin servicio. 
            Después de pagar las deudas pendientes, solicite la reconexión.
            <a href="/admin/properties/${propiedad.id}/request-reconnection" class="btn btn-sm btn-outline-warning ml-2">
                <i class="fas fa-plug mr-1"></i>Solicitar Reconexión
            </a>
        `;
        
        // ✅ VERSIÓN SEGURA: Insertar después del buscador
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
    
    // ✅ Cargar meses pendientes automáticamente
    cargarMesesPendientes(propiedad.id);
    
    // ✅ NUEVO: Cargar multas pendientes automáticamente
    cargarMultasPendientes(propiedad.id);
    
    // Cargar deudas pendientes
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
    // INICIALIZACIÓN PRINCIPAL
    // =============================================
    
    document.addEventListener('DOMContentLoaded', function() {
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
            actualizarResumenTotal();
        });
        
        mesHasta.addEventListener('change', function() {
            actualizarResumenMeses();
            validarMesesEnTiempoReal();
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
                tarifa_nombre: '{{ $propiedadSeleccionada->tariff->nombre ?? 'N/A' }}'
            };
            
            setTimeout(() => {
                console.log('Ejecutando auto-selección...');
                seleccionarPropiedad(propiedadData);
                
                const buscadorGroup = document.getElementById('buscadorGroup');
                if (buscadorGroup) {
                    buscadorGroup.style.display = 'none';
                }
                
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
    
    <script>
        setTimeout(() => {
            console.log('Cargando multas pendientes desde PHP...');
            const multasData = @json($multasData);
            multasPendientes = multasData;
            actualizarListaMultasPendientesUI(multasPendientes);
        }, 500);
    </script>
@endif
    });
</script>
@stop