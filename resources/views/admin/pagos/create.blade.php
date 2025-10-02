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
                                            $startDate = now()->subMonths(12);
                                            $endDate = now()->addMonths(6);
                                            
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
                                Seleccione el rango de meses que desea pagar
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

    function actualizarResumenMeses() {
        console.log('Actualizando resumen de meses...');
        
        const desde = mesDesde.value;
        const hasta = mesHasta.value;
        
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

        // Calcular meses entre las fechas
        const start = new Date(desde + '-01');
        const end = new Date(hasta + '-01');
        const meses = [];
        
        let current = new Date(start);
        while (current <= end) {
            meses.push(new Date(current));
            current.setMonth(current.getMonth() + 1);
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
        document.getElementById('resumenTotal').textContent = `Bs ${totalPago.toFixed(2)}`;
        
        resumenMeses.style.display = 'block';

        // Validar máximo 12 meses
        if (totalMesesCount > 12) {
            alert('Advertencia: Está intentando pagar más de 12 meses. Considere dividir el pago.');
        }
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

    // Validar antes de enviar
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

        const fechaPago = new Date(document.getElementById('fecha_pago').value);
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        
        if (fechaPago > hoy) {
            e.preventDefault();
            alert('Error: La fecha de pago no puede ser futura.');
            return false;
        }

        // Calcular total final
        const start = new Date(mesDesde.value + '-01');
        const end = new Date(mesHasta.value + '-01');
        const mesesCount = Math.round((end - start) / (30 * 24 * 60 * 60 * 1000)) + 1;
        const totalPago = mesesCount * tarifaMensual;

        const confirmacion = confirm(
            `¿Está seguro de registrar ${mesesCount} pago(s) por un total de Bs ${totalPago.toFixed(2)}?\n\n` +
            `Cliente: ${document.getElementById('resumenCliente').textContent}\n` +
            `Meses: ${mesDesde.options[mesDesde.selectedIndex].text} - ${mesHasta.options[mesHasta.selectedIndex].text}`
        );
        
        if (!confirmacion) {
            e.preventDefault();
            return false;
        }
    });

    // Inicializar fecha mínima (hoy)
    document.getElementById('fecha_pago').max = new Date().toISOString().split('T')[0];
    
    console.log('Script de pagos inicializado completamente');
});
</script>
@stop