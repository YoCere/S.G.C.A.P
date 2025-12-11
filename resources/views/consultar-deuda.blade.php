{{-- resources/views/consultar-deuda.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Consultar Estado de Deuda
        </h2>
    </x-slot>

    @push('scripts')
        <script>
            function limpiarFormulario() {
                document.getElementById('debt-form').reset();
            }

            // Validación en tiempo real de inputs
            document.addEventListener('DOMContentLoaded', function() {
                const codigoInput = document.getElementById('codigo_cliente');
                const ciInput = document.getElementById('ci');

                if (codigoInput) {
                    codigoInput.addEventListener('input', function(e) {
                        this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
                    });
                }

                if (ciInput) {
                    ciInput.addEventListener('input', function(e) {
                        this.value = this.value.replace(/[^\d]/g, '');
                    });
                }
            });
        </script>
    @endpush

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-cyan-100 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-3">Consultar Estado de Deuda</h1>
                <p class="text-gray-600">Ingrese su código de cliente y CI para consultar sus deudas pendientes</p>
            </div>

            <!-- Formulario de Consulta -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
                <form action="{{ route('buscar-deuda') }}" method="POST" class="space-y-6" id="debt-form">
                    @csrf
                    
                    <!-- Campo honeypot para protección básica -->
                    <input type="text" name="honeypot" style="display:none;" tabindex="-1" autocomplete="off">
                    
                    <!-- Mensajes de Estado -->
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            {{ session('info') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Código Cliente -->
                        <div>
                            <label for="codigo_cliente" class="block text-sm font-medium text-gray-700 mb-2">
                                Código de Cliente *
                            </label>
                            <input type="text" 
                                   name="codigo_cliente" 
                                   id="codigo_cliente"
                                   value="{{ old('codigo_cliente') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Ej: CL00123"
                                   required
                                   maxlength="20">
                            @error('codigo_cliente')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- CI -->
                        <div>
                            <label for="ci" class="block text-sm font-medium text-gray-700 mb-2">
                                Cédula de Identidad *
                            </label>
                            <input type="text" 
                                   name="ci" 
                                   id="ci"
                                   value="{{ old('ci') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Ej: 1234567"
                                   required
                                   maxlength="20">
                            @error('ci')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Información de Seguridad (Simplificada) -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                        <p class="text-sm font-medium text-blue-700 mb-1">🔒 Consulta Segura</p>
                        <p class="text-xs text-blue-600">Sus datos están protegidos</p>
                    </div>

                    <!-- Botones -->
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <button type="submit" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition duration-200 flex items-center justify-center min-w-[160px]">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Consultar Deuda
                        </button>
                        
                        <button type="button" 
                                onclick="limpiarFormulario()"
                                class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-6 rounded-lg transition duration-200">
                            Limpiar Formulario
                        </button>
                    </div>
                </form>
            </div>

            <!-- Resultados - MOSTRAR TODAS LAS PROPIEDADES -->
            @if(isset($client) && isset($properties))
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <!-- Información del Cliente -->
                    <div class="border-b border-gray-200 pb-6 mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Información del Cliente</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Nombre</p>
                                <p class="font-semibold">{{ $client->nombre }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">CI</p>
                                <p class="font-semibold">{{ $client->ci }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Código Cliente</p>
                                <p class="font-semibold">{{ $client->codigo_cliente }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Resumen General -->
                    @php
                        // Función para formatear mes en español
                        function formatearMes($fecha) {
                            if (!$fecha) return 'N/A';
                            
                            $meses = [
                                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                                5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                            ];
                            
                            $carbonDate = \Carbon\Carbon::parse($fecha);
                            $mes = $carbonDate->month;
                            $año = $carbonDate->year;
                            
                            return $meses[$mes] . ' ' . $año;
                        }

                        $totalDeudas = 0;
                        $propiedadesConProblemas = 0;
                        
                        foreach($properties as $property) {
                            // FILTRAR SOLO DEUDAS PENDIENTES (NO PAGADAS)
                            $deudasPendientes = $property->debts ? $property->debts->where('estado', 'pendiente') : collect();
                            
                            $totalDeudas += $deudasPendientes->sum('monto_pendiente');
                            
                            if($deudasPendientes->count() > 0) {
                                $propiedadesConProblemas++;
                            }
                        }
                    @endphp

                    <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-blue-600">{{ $properties->count() }}</p>
                            <p class="text-sm text-blue-700">Total Propiedades</p>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-red-600">{{ $propiedadesConProblemas }}</p>
                            <p class="text-sm text-red-700">Propiedades con Deudas</p>
                        </div>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                            <p class="text-2xl font-bold text-orange-600">Bs. {{ number_format($totalDeudas, 2) }}</p>
                            <p class="text-sm text-orange-700">Total Deudas Pendientes</p>
                        </div>
                    </div>

                    <!-- Propiedades del Cliente -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Detalle por Propiedad</h2>
                        
                        @if($properties->count() > 0)
                            <div class="space-y-6">
                                @foreach($properties as $property)
                                    @php
                                        // FILTRAR SOLO DEUDAS PENDIENTES
                                        $deudasPendientes = $property->debts ? $property->debts->where('estado', 'pendiente') : collect();
                                        
                                        $totalPropiedadDeudas = $deudasPendientes->sum('monto_pendiente');
                                        $tieneProblemas = $deudasPendientes->count() > 0;
                                        
                                        // Determinar color según estado
                                        $estadoColor = 'bg-gray-100 text-gray-800';
                                        if($property->estado == 'activa' || $property->estado == 'activo') {
                                            $estadoColor = $tieneProblemas ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800';
                                        } elseif($property->estado == 'cortada' || $property->estado == 'cortado') {
                                            $estadoColor = 'bg-red-100 text-red-800';
                                        } elseif($property->estado == 'corte_pendiente') {
                                            $estadoColor = 'bg-orange-100 text-orange-800';
                                        }
                                    @endphp

                                    <div class="border border-gray-200 rounded-lg p-6 {{ $tieneProblemas ? 'bg-red-50 border-red-300' : 'bg-green-50 border-green-300' }}">
                                        <!-- Encabezado de la Propiedad -->
                                        <div class="flex justify-between items-start mb-4">
                                            <div class="flex-1">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                                    {{ $property->referencia ?? 'Propiedad #' . $property->id }}
                                                    @if($property->direccion)
                                                        - {{ $property->direccion }}
                                                    @endif
                                                </h3>
                                                <div class="flex flex-wrap gap-2">
                                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full {{ $estadoColor }}">
                                                        Estado: {{ ucfirst($property->estado) }}
                                                    </span>
                                                    <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                        Tarifa: {{ $property->tariff->nombre ?? 'No asignada' }}
                                                    </span>
                                                    @if($tieneProblemas)
                                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                            ⚠️ Tiene deudas pendientes
                                                        </span>
                                                    @else
                                                        <span class="inline-flex px-3 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                            ✅ Al día
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Deudas PENDIENTES de la Propiedad -->
                                        @if($deudasPendientes->count() > 0)
                                            <div class="mb-4">
                                                <h4 class="text-md font-semibold text-gray-800 mb-3 flex items-center">
                                                    <svg class="w-4 h-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                    Deudas Pendientes ({{ $deudasPendientes->count() }} meses)
                                                </h4>
                                                <div class="overflow-x-auto">
                                                    <table class="min-w-full bg-white rounded-lg border border-red-200">
                                                        <thead>
                                                            <tr class="bg-red-50">
                                                                <th class="px-4 py-2 text-left text-xs font-medium text-red-700 uppercase">Mes/Año</th>
                                                                <th class="px-4 py-2 text-left text-xs font-medium text-red-700 uppercase">Monto</th>
                                                                <th class="px-4 py-2 text-left text-xs font-medium text-red-700 uppercase">Vencimiento</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-red-100">
                                                            @foreach($deudasPendientes as $debt)
                                                                <tr>
                                                                    <td class="px-4 py-2 text-sm text-gray-900">
                                                                        {{ formatearMes($debt->fecha_emision) }}
                                                                    </td>
                                                                    <td class="px-4 py-2 text-sm font-semibold text-red-600">
                                                                        Bs. {{ number_format($debt->monto_pendiente, 2) }}
                                                                    </td>
                                                                    <td class="px-4 py-2 text-sm text-gray-500">
                                                                        @if($debt->fecha_vencimiento)
                                                                            {{ \Carbon\Carbon::parse($debt->fecha_vencimiento)->format('d/m/Y') }}
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                        <tfoot class="bg-red-50">
                                                            <tr>
                                                                <td colspan="3" class="px-4 py-2 text-right text-sm font-semibold text-red-700">
                                                                    Total Deudas Pendientes: 
                                                                    <span class="ml-2">Bs. {{ number_format($totalPropiedadDeudas, 2) }}</span>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mb-4 p-3 bg-green-100 border border-green-200 rounded-lg">
                                                <p class="text-green-700 text-sm flex items-center">
                                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    No tiene deudas pendientes
                                                </p>
                                            </div>
                                        @endif

                                        <!-- Resumen de la Propiedad -->
                                        @if($tieneProblemas)
                                            <div class="mt-4 p-3 bg-red-100 border border-red-300 rounded-lg">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-semibold text-red-700">Total Pendiente en esta propiedad:</span>
                                                    <span class="text-lg font-bold text-red-700">
                                                        Bs. {{ number_format($totalPropiedadDeudas, 2) }}
                                                    </span>
                                                </div>
                                                @if($property->estado == 'cortada' || $property->estado == 'cortado')
                                                    <p class="text-red-600 text-sm mt-1">⚠️ Servicio cortado por morosidad</p>
                                                @elseif($property->estado == 'corte_pendiente')
                                                    <p class="text-red-600 text-sm mt-1">⚠️ Corte de servicio pendiente</p>
                                                @else
                                                    <p class="text-red-600 text-sm mt-1">⚠️ Tiene deudas pendientes</p>
                                                @endif
                                            </div>
                                        @else
                                            <div class="mt-4 p-3 bg-green-100 border border-green-300 rounded-lg">
                                                <div class="flex justify-between items-center">
                                                    <span class="font-semibold text-green-700">Estado de la propiedad:</span>
                                                    <span class="text-lg font-bold text-green-700">
                                                        ✅ Al día
                                                    </span>
                                                </div>
                                                <p class="text-green-600 text-sm mt-1">No tiene deudas pendientes</p>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Total General -->
                            @if($totalDeudas > 0)
                                <div class="mt-8 p-6 bg-red-100 border border-red-300 rounded-lg">
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-xl font-bold text-gray-900">Total General a Pagar:</span>
                                        <span class="text-3xl font-bold text-red-700">
                                            Bs. {{ number_format($totalDeudas, 2) }}
                                        </span>
                                    </div>
                                    <div class="text-sm mt-2">
                                        <p class="text-red-600">Por favor, acérquese a nuestras oficinas para regularizar su situación.</p>
                                    </div>
                                </div>
                            @else
                                <div class="mt-8 p-6 bg-green-100 border border-green-300 rounded-lg text-center">
                                    <div class="text-green-500 text-4xl mb-2">✅</div>
                                    <h3 class="text-xl font-semibold text-green-700 mb-2">¡Excelente!</h3>
                                    <p class="text-green-600">Todas sus propiedades están al día. No tiene deudas pendientes.</p>
                                </div>
                            @endif

                            <!-- Botón de WhatsApp para contactar a la secretaria -->
                            <div class="mt-8 p-6 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-lg shadow-sm">
                                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                                    <div class="flex items-center">
                                        <div class="mr-4 bg-green-100 p-3 rounded-full">
                                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.76.982.998-3.675-.236-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.9 6.994c-.004 5.45-4.438 9.88-9.885 9.88m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.333.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.333 11.893-11.893 0-3.18-1.24-6.162-3.495-8.411"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">¿Necesita ayuda con su pago?</h3>
                                            <p class="text-gray-600">Contacte a nuestra secretaria para consultas sobre pagos y regularización</p>
                                        </div>
                                    </div>
                                    <button onclick="enviarWhatsApp()" 
                                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition duration-200 flex items-center justify-center min-w-[200px] shadow-md hover:shadow-lg">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.76.982.998-3.675-.236-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.9 6.994c-.004 5.45-4.438 9.88-9.885 9.88m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.333.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.333 11.893-11.893 0-3.18-1.24-6.162-3.495-8.411"/>
                                        </svg>
                                        Contactar por WhatsApp
                                    </button>
                                </div>
                                <div class="mt-4 text-center text-sm text-gray-500">
                                    <p>📞 Número: +591 76817297 (Secretaria)</p>
                                    <p class="mt-1">⏰ Horario de atención: Lunes a Viernes 8:00 - 16:00</p>
                                </div>
                            </div>

                        @else
                            <div class="text-center py-12">
                                <div class="text-gray-500 text-5xl mb-4">🏠</div>
                                <h3 class="text-xl font-semibold text-gray-600 mb-2">Sin propiedades</h3>
                                <p class="text-gray-500">No tiene propiedades registradas.</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Script para WhatsApp en el cuerpo principal -->
    <script>
        // Función para enviar mensaje por WhatsApp
        function enviarWhatsApp() {
            @if(isset($client) && isset($properties))
                const telefono = '+59176817297';
                let mensaje = "Hola, consulto sobre mis deudas de agua.%0A";
                mensaje += "Cliente: {{ $client->nombre }}%0A";
                mensaje += "CI: {{ $client->ci }}%0A";
                mensaje += "Código: {{ $client->codigo_cliente }}%0A";
                
                @php
                    $totalDeudas = 0;
                    $propiedadesConProblemas = 0;
                    
                    if(isset($properties)) {
                        foreach($properties as $property) {
                            $deudasPendientes = $property->debts ? $property->debts->where('estado', 'pendiente') : collect();
                            $totalDeudas += $deudasPendientes->sum('monto_pendiente');
                            
                            if($deudasPendientes->count() > 0) {
                                $propiedadesConProblemas++;
                            }
                        }
                    }
                @endphp
                
                @if(isset($totalDeudas) && $totalDeudas > 0)
                    mensaje += "Total pendiente: Bs. {{ number_format($totalDeudas, 2) }}%0A";
                    mensaje += "Número de propiedades con deuda: {{ $propiedadesConProblemas }}%0A";
                @else
                    mensaje += "Estado: Al día - Sin deudas pendientes%0A";
                @endif
                
                mensaje += "Por favor indíqueme cómo puedo realizar el pago.";
                
                const url = `https://wa.me/${telefono}?text=${mensaje}`;
                window.open(url, '_blank');
            @endif
        }
    </script>
</x-app-layout>