<x-app-layout>
    <!-- Hero Section Contacto -->
    <div class="hero-gradient text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl lg:text-5xl font-bold mb-4">CONTÁCTANOS</h1>
                <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                    Estamos aquí para servirte. No dudes en contactarnos para cualquier consulta o solicitud
                </p>
            </div>
        </div>
    </div>

    <!-- Información de Contacto y Formulario -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Información de Contacto -->
                <div class="space-y-8">
                    <div>
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">INFORMACIÓN DE CONTACTO</h2>
                        <p class="text-gray-600 mb-8">
                            Estamos comprometidos con brindarte la mejor atención. Puedes contactarnos 
                            a través de cualquiera de los siguientes medios:
                        </p>
                    </div>

                   <!-- Detalles de Contacto -->
<div class="space-y-4">
    <div class="flex items-start space-x-4">
        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <h4 class="font-semibold text-gray-900">Dirección Central</h4>
            <p class="text-gray-600">{{ $settings['contact_address'] ?? 'No configurado' }}</p>
        </div>
    </div>
    
    <div class="flex items-start space-x-4">
        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
        </div>
        <div>
            <h4 class="font-semibold text-gray-900">Teléfonos</h4>
            <p class="text-gray-600">Central: {{ $settings['contact_phone'] ?? 'No configurado' }}</p>
        </div>
    </div>
    
    <div class="flex items-start space-x-4">
        <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <h4 class="font-semibold text-gray-900">Horario de Atención</h4>
            <p class="text-gray-600">{{ $settings['schedule_weekdays'] ?? 'No configurado' }}</p>
            <p class="text-gray-600">{{ $settings['schedule_saturday'] ?? '' }}</p>
        </div>
    </div>
</div>
            
                </div>

                <!-- Formulario de Contacto -->
                <div class="bg-gray-50 rounded-xl p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">ENVÍANOS UN MENSAJE</h2>
                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-gray-700 mb-2">Nombre Completo *</label>
                                <input type="text" id="nombre" name="nombre" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300">
                            </div>
                            <div>
                                <label for="telefono" class="block text-sm font-medium text-gray-700 mb-2">Teléfono *</label>
                                <input type="tel" id="telefono" name="telefono" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300">
                            </div>
                        </div>
                        
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Correo Electrónico *</label>
                            <input type="email" id="email" name="email" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300">
                        </div>

                        <div>
                            <label for="asunto" class="block text-sm font-medium text-gray-700 mb-2">Asunto *</label>
                            <select id="asunto" name="asunto" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300">
                                <option value="">Selecciona un asunto</option>
                                <option value="consulta">Consulta general</option>
                                <option value="servicio">Solicitud de servicio</option>
                                <option value="reclamo">Reclamo</option>
                                <option value="sugerencia">Sugerencia</option>
                                <option value="emergencia">Emergencia</option>
                            </select>
                        </div>

                        <div>
                            <label for="mensaje" class="block text-sm font-medium text-gray-700 mb-2">Mensaje *</label>
                            <textarea id="mensaje" name="mensaje" rows="5" required 
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-300"
                                      placeholder="Describe tu consulta o solicitud..."></textarea>
                        </div>

                        <button type="submit" 
                                class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                            ENVIAR MENSAJE
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mapa -->
    <div class="bg-white py-16 border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">NUESTRA UBICACIÓN</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Visítanos en nuestra oficina central en la comunidad de La Grampa
                </p>
            </div>
            
            <!-- Mapa placeholder -->
            <div class="bg-gray-100 rounded-xl h-96 flex items-center justify-center">
                <div class="text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-gray-500">Mapa de ubicación del Comité de Agua La Grampa</p>
                    <p class="text-gray-400 text-sm mt-2">Comunidad La Grampa, Yacuiba - Bolivia</p>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Final -->
    <div class="bg-blue-600 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold mb-4">¿Prefieres llamarnos directamente?</h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Estamos disponibles para atenderte telefónicamente durante nuestro horario de atención
            </p>
            <div class="flex flex-col sm:flex-row justify-center items-center space-y-4 sm:space-y-0 sm:space-x-6">
                <a href="tel:+59146642211" 
                   class="inline-flex items-center bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    LLAMAR AHORA
                </a>
                <span class="text-blue-200">o</span>
                <a href="{{ route('servicios') }}" 
                   class="inline-flex items-center border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">
                    CONOCER SERVICIOS
                </a>
            </div>
        </div>
    </div>
</x-app-layout>