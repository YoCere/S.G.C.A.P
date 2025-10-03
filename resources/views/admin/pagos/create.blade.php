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
                            <strong>Tarifa:</strong> <span id="tarifaMonto" class="text-success"></span>/mes
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
                                    <span class="{{ \Carbon\Carbon::parse($deuda->fecha_vencimiento)->isPast() ? 'text-danger' : 'text-warning' }}">
                                        {{ \Carbon\Carbon::parse($deuda->fecha_vencimiento)->format('d/m/Y') }}
                                    </span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Monto:</strong> 
                                    <span class="text-danger font-weight-bold">
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

                {{-- MESES ADEUDADOS (basado en pagos faltantes) --}}
                @if(isset($propiedadSeleccionada))
                <div class="alert alert-info mt-3" id="mesesAdeudados">
                    <h6 class="alert-heading">
                        <i class="fas fa-calendar-alt mr-2"></i>Meses por Pagar (Últimos 12 meses)
                    </h6>
                    <div class="mt-2" id="listaMesesAdeudados">
                        @php
                            $mesesAdeudados = [];
                            if (isset($propiedadSeleccionada)) {
                                // Obtener meses pagados
                                $mesesPagados = \App\Models\Pago::where('propiedad_id', $propiedadSeleccionada->id)
                                    ->pluck('mes_pagado')
                                    ->toArray();
                                
                                // Generar últimos 12 meses
                                for ($i = 11; $i >= 0; $i--) {
                                    $mes = \Carbon\Carbon::now()->subMonths($i)->format('Y-m');
                                    if (!in_array($mes, $mesesPagados)) {
                                        $mesesAdeudados[] = $mes;
                                    }
                                }
                            }
                        @endphp
                        
                        @if(count($mesesAdeudados) > 0)
                            @foreach($mesesAdeudados as $mes)
                            <span class="badge badge-warning mr-1 mb-1 mes-adeudado">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $mes)->locale('es')->translatedFormat('F Y') }}
                            </span>
                            @endforeach
                            <div class="mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="seleccionarMesesAdeudados()">
                                    <i class="fas fa-check-circle mr-1"></i>Seleccionar Meses Adeudados
                                </button>
                            </div>
                        @else
                            <span class="text-success">Todos los meses están al día</span>
                        @endif
                    </div>
                </div>
                @endif
                
                {{-- DETALLES DEL PAGO --}}
                <div class="card mt-4" id="detallesPago" style="display: none;">
                    <div class="card-header bg-light">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-money-bill-wave mr-2"></i>Detalles del Pago
                        </h5>
                    </div>
                    <div class="card-body">
                        {{-- SELECCIÓN DE MESES --}}
                        <div class="form-group">
                            <label>Meses a Pagar *</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="mes_desde">Desde:</label>
                                    <select name="mes_desde" id="mes_desde" class="form-control" required>
                                        <option value="">Seleccione mes inicial</option>
                                        @php
                                            $meses = [];
                                            // ✅ CORREGIDO: Solo hasta diciembre del año actual
                                            $startDate = now()->subMonths(12);
                                            $endDate = now()->endOfYear(); // Solo hasta diciembre del año actual
                                            
                                            $current = $startDate->copy();
                                            while ($current <= $endDate) {
                                                $valor = $current->format('Y-m');
                                                $texto = $current->locale('es')->translatedFormat('F Y');
                                                $meses[$valor] = $texto;
                                                $current->addMonth();
                                            }
                                        @endphp
                                        @foreach($meses as $valor => $texto)
                                            <option value="{{ $valor }}">{{ $texto }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="mes_hasta">Hasta:</label>
                                    <select name="mes_hasta" id="mes_hasta" class="form-control" required>
                                        <option value="">Seleccione mes final</option>
                                        @foreach($meses as $valor => $texto)
                                            <option value="{{ $valor }}">{{ $texto }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Seleccione el rango de meses que desea pagar (solo hasta {{ now()->year }})
                            </small>
                        </div>

                        {{-- RESUMEN DE MESES SELECCIONADOS --}}
                        <div class="alert alert-info" id="resumenMeses" style="display: none;">
                            <strong>Meses a pagar:</strong>
                            <div id="listaMeses" class="mt-1"></div>
                            <small class="text-muted" id="totalMeses"></small>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="fecha_pago">Fecha de Pago *</label>
                                    <input type="date" name="fecha_pago" id="fecha_pago" 
                                           class="form-control" 
                                           value="{{ old('fecha_pago', date('Y-m-d')) }}" required>
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
                                <div class="col-md-4">
                                    <strong>Cliente:</strong> <span id="resumenCliente"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Propiedad:</strong> <span id="resumenPropiedad"></span>
                                </div>
                                <div class="col-md-4">
                                    <strong>Total a Pagar:</strong> 
                                    <span id="resumenTotal" class="text-success font-weight-bold">Bs 0.00</span>
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
        /* Asegurar que los selects sean interactivos */
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
    </style>
@stop

@section('js')
<script>
    // Hacer funciones globales para poder llamarlas desde cualquier lugar
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
        
        if (buscador) buscador.value = '';
        if (resultadosBusqueda) resultadosBusqueda.style.display = 'none';
        if (infoPropiedad) infoPropiedad.style.display = 'none';
        if (detallesPago) detallesPago.style.display = 'none';
        if (submitBtn) submitBtn.disabled = true;
        if (propiedadId) propiedadId.value = '';
        if (mesDesde) mesDesde.value = '';
        if (mesHasta) mesHasta.value = '';
        if (resumenMeses) resumenMeses.style.display = 'none';
        
        // Ocultar secciones de deudas
        const deudasPendientes = document.getElementById('deudasPendientes');
        const sinDeudas = document.getElementById('sinDeudas');
        const mesesAdeudados = document.getElementById('mesesAdeudados');
        if (deudasPendientes) deudasPendientes.style.display = 'none';
        if (sinDeudas) sinDeudas.style.display = 'none';
        if (mesesAdeudados) mesesAdeudados.style.display = 'none';
        
        // Mostrar el buscador si estaba oculto
        const buscadorGroup = document.getElementById('buscadorGroup');
        if (buscadorGroup) {
            buscadorGroup.style.display = 'block';
        }
        
        // Remover mensaje de pago rápido si existe
        const alertInfo = document.querySelector('.alert-info');
        if (alertInfo) {
            alertInfo.remove();
        }
    };
    
    window.mostrarBuscador = function() {
        window.limpiarBusqueda();
    };
    
    // Función para seleccionar meses adeudados automáticamente
    window.seleccionarMesesAdeudados = function() {
        const mesDesde = document.getElementById('mes_desde');
        const mesHasta = document.getElementById('mes_hasta');
        
        if (!mesDesde || !mesHasta) {
            console.error('No se encontraron los selects de meses');
            return;
        }
        
        // Obtener todos los meses adeudados (de los badges)
        const mesesAdeudadosElements = document.querySelectorAll('.mes-adeudado');
        const mesesAdeudados = Array.from(mesesAdeudadosElements)
            .map(badge => {
                const texto = badge.textContent.trim();
                // Convertir "Enero 2024" a "2024-01"
                const partes = texto.split(' ');
                if (partes.length === 2) {
                    const meses = {
                        'enero': '01', 'febrero': '02', 'marzo': '03', 'abril': '04',
                        'mayo': '05', 'junio': '06', 'julio': '07', 'agosto': '08',
                        'septiembre': '09', 'octubre': '10', 'noviembre': '11', 'diciembre': '12'
                    };
                    const mesNumero = meses[partes[0].toLowerCase()];
                    if (mesNumero) {
                        return partes[1] + '-' + mesNumero;
                    }
                }
                return null;
            })
            .filter(mes => mes !== null)
            .sort();
        
        if (mesesAdeudados.length === 0) {
            alert('No hay meses adeudados para seleccionar');
            return;
        }
        
        // Seleccionar el rango completo de meses adeudados
        const primerMes = mesesAdeudados[0];
        const ultimoMes = mesesAdeudados[mesesAdeudados.length - 1];
        
        mesDesde.value = primerMes;
        mesHasta.value = ultimoMes;
        
        // Actualizar el resumen
        actualizarResumenMeses();
        
        // Mostrar mensaje de confirmación
        alert(`Se seleccionaron ${mesesAdeudados.length} meses adeudados automáticamente`);
    };
    
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM completamente cargado - Inicializando script de pagos');
        
        const buscador = document.getElementById('buscador');
        const resultadosBusqueda = document.getElementById('resultadosBusqueda');
        const listaResultados = document.getElementById('listaResultados');
        const infoPropiedad = document.getElementById('infoPropiedad');
        const detallesPago = document.getElementById('detallesPago');
        const submitBtn = document.getElementById('submitBtn');
        const propiedadId = document.getElementById('propiedadId');
        const mesDesde = document.getElementById('mes_desde');
        const mesHasta = document.getElementById('mes_hasta');
        const resumenMeses = document.getElementById('resumenMeses');
        const listaMeses = document.getElementById('listaMeses');
        const totalMeses = document.getElementById('totalMeses');
        
        let tarifaMensual = 0;
        let timeoutBusqueda = null;
    
        // ✅ VERIFICAR QUE TODOS LOS ELEMENTOS EXISTAN
        if (!buscador || !mesDesde || !mesHasta) {
            console.error('Error: Elementos críticos no encontrados en el DOM');
            return;
        }
    
        console.log('Todos los elementos del DOM están disponibles');
    
        // Función para seleccionar propiedad
        function seleccionarPropiedad(propiedad) {
            console.log('Seleccionando propiedad:', propiedad);
            
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
            propiedadId.value = propiedad.id;
            tarifaMensual = parseFloat(propiedad.tarifa_precio);
            
            // Mostrar secciones
            infoPropiedad.style.display = 'block';
            detallesPago.style.display = 'block';
            resultadosBusqueda.style.display = 'none';
            buscador.value = `${propiedad.referencia} - ${propiedad.cliente_nombre}`;
            
            // Cargar deudas pendientes via AJAX
            cargarDeudasPendientes(propiedad.id);
            
            // Habilitar submit
            submitBtn.disabled = false;
            
            // ✅ FORZAR HABILITACIÓN DE SELECTS
            mesDesde.disabled = false;
            mesHasta.disabled = false;
            
            // ✅ AGREGAR EVENT LISTENERS DIRECTAMENTE
            mesDesde.onchange = actualizarResumenMeses;
            mesHasta.onchange = actualizarResumenMeses;
            
            console.log('Propiedad seleccionada - Selects habilitados y listeners agregados');
            
            // Actualizar total inicial
            actualizarResumenMeses();
        }
    
        // Función para cargar deudas pendientes via AJAX
        function cargarDeudasPendientes(propiedadId) {
            // ✅ USAR LA RUTA CORRECTA
            const url = `{{ route('admin.propiedades.deudaspendientes', ['propiedad' => 'PROP_ID']) }}`.replace('PROP_ID', propiedadId);
            
            console.log('Cargando deudas desde:', url);
            
            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Deudas cargadas exitosamente:', data);
                    actualizarUIdeudasPendientes(data);
                })
                .catch(error => {
                    console.error('Error cargando deudas:', error);
                    // Ocultar secciones de deudas si hay error
                    const deudasPendientes = document.getElementById('deudasPendientes');
                    const mesesAdeudados = document.getElementById('mesesAdeudados');
                    if (deudasPendientes) deudasPendientes.style.display = 'none';
                    if (mesesAdeudados) mesesAdeudados.style.display = 'none';
                });
        }
    
        function actualizarUIdeudasPendientes(data) {
            // Esta función se puede expandir para actualizar dinámicamente
            // las deudas cuando se selecciona una propiedad via búsqueda
            console.log('Deudas cargadas:', data);
        }
    
        // ✅ FUNCIÓN ACTUALIZARRESUMENMESES - PERFECTA Y SIN ERRORES
        function actualizarResumenMeses() {
            console.log('Actualizando resumen de meses...');
            
            const desde = mesDesde.value;
            const hasta = mesHasta.value;
            
            console.log('Desde:', desde, 'Hasta:', hasta);
            
            if (!desde || !hasta) {
                resumenMeses.style.display = 'none';
                document.getElementById('resumenTotal').textContent = 'Bs 0.00';
                return;
            }
    
            // Validar que "hasta" no sea menor que "desde"
            if (desde > hasta) {
                alert('Error: El mes final no puede ser anterior al mes inicial');
                mesHasta.value = '';
                resumenMeses.style.display = 'none';
                document.getElementById('resumenTotal').textContent = 'Bs 0.00';
                return;
            }
    
            // ✅ CÁLCULO PERFECTO - SIN PROBLEMAS DE ZONA HORARIA
            const startYear = parseInt(desde.split('-')[0]);
            const startMonth = parseInt(desde.split('-')[1]) - 1; // Meses en JS: 0-11
            const endYear = parseInt(hasta.split('-')[0]);
            const endMonth = parseInt(hasta.split('-')[1]) - 1;
            
            const meses = [];
            
            let currentYear = startYear;
            let currentMonth = startMonth;
            
            // Calcular hasta que lleguemos al mes final inclusive
            while (currentYear < endYear || (currentYear === endYear && currentMonth <= endMonth)) {
                // Crear fecha sin problemas de zona horaria (usar día 15)
                const fecha = new Date(currentYear, currentMonth, 15);
                meses.push(new Date(fecha));
                
                // Avanzar al siguiente mes
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
            }
    
            console.log('Meses calculados CORRECTAMENTE:', meses.map(m => m.toLocaleDateString('es-ES', { year: 'numeric', month: 'long' })));
    
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
            document.getElementById('resumenTotal').textContent = `Bs ${totalPago.toFixed(2)}`;
            
            resumenMeses.style.display = 'block';
    
            // Validar máximo 12 meses
            if (totalMesesCount > 12) {
                alert('Advertencia: Está intentando pagar más de 12 meses. Considere dividir el pago.');
            }
        }
    
        // ✅ AUTO-SELECCIÓN SI VIENE PROPIEDAD POR URL
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
            
            // Esperar un poco más para asegurar que todo esté listo
            setTimeout(() => {
                console.log('Ejecutando auto-selección...');
                seleccionarPropiedad(propiedadData);
                
                // Ocultar el buscador para mejor UX
                const buscadorGroup = document.getElementById('buscadorGroup');
                if (buscadorGroup) {
                    buscadorGroup.style.display = 'none';
                }
                
                // Mostrar mensaje informativo
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
            const url = '{{ route("admin.propiedades.search") }}?q=' + encodeURIComponent(query);
            
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
                    const item = document.createElement('div');
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = `
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${propiedad.referencia}</h6>
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
    
        // ✅ AGREGAR EVENT LISTENERS A LOS SELECTS DE MESES (SIEMPRE)
        mesDesde.addEventListener('change', actualizarResumenMeses);
        mesHasta.addEventListener('change', actualizarResumenMeses);
        
        console.log('Event listeners de meses agregados correctamente');
    
        // Cerrar resultados al hacer click fuera
        document.addEventListener('click', function(e) {
            if (!buscador.contains(e.target) && !resultadosBusqueda.contains(e.target)) {
                resultadosBusqueda.style.display = 'none';
            }
        });
    
        // ✅ VALIDACIÓN PERFECTA - PERMITE FECHAS FUTURAS Y CÁLCULO PRECISO
        document.getElementById('pagoForm').addEventListener('submit', function(e) {
            if (!propiedadId.value) {
                e.preventDefault();
                alert('Error: Debe seleccionar una propiedad.');
                return false;
            }
    
            if (!mesDesde.value || !mesHasta.value) {
                e.preventDefault();
                alert('Error: Debe seleccionar el rango de meses a pagar.');
                return false;
            }
    
            // ✅ CÁLCULO PERFECTO para la confirmación
            const desde = mesDesde.value;
            const hasta = mesHasta.value;
            
            const startYear = parseInt(desde.split('-')[0]);
            const startMonth = parseInt(desde.split('-')[1]);
            const endYear = parseInt(hasta.split('-')[0]);
            const endMonth = parseInt(hasta.split('-')[1]);
            
            let mesesCount = (endYear - startYear) * 12 + (endMonth - startMonth) + 1;
            
            const totalPago = mesesCount * tarifaMensual;
    
            const confirmacion = confirm(
                `¿Está seguro de registrar ${mesesCount} pago(s) por un total de Bs ${totalPago.toFixed(2)}?\n\n` +
                `Cliente: ${document.getElementById('resumenCliente').textContent}\n` +
                `Propiedad: ${document.getElementById('resumenPropiedad').textContent}\n` +
                `Meses: ${mesDesde.options[mesDesde.selectedIndex].text} - ${mesHasta.options[mesHasta.selectedIndex].text}`
            );
            
            if (!confirmacion) {
                e.preventDefault();
                return false;
            }
        });
    
        console.log('Script de pagos inicializado completamente');
    });
    </script>
@stop