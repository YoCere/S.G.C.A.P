<x-app-layout>
    <!-- Hero Section -->
    <div class="hero-gradient text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="space-y-6">
                    
                    <h1 class="text-4xl lg:text-5xl font-bold leading-tight">
                        COMITE DE AGUA<br>
                        <span class="text-cyan-200">COMUNIDAD INDEPENDENCIA LA GRAMPA</span>
                    </h1>
                    <p class="text-xl text-blue-100 leading-relaxed">
                        Servicio confiable y continuo de agua potable para las familias de 
                        <strong>La Grampa</strong>. Trabajamos por el bienestar de nuestra comunidad.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <a href="{{ route('consultar-deuda') }}" 
                        class="w-full border-2 border-white text-white py-4 px-8 rounded-lg font-bold text-center block hover:bg-white hover:text-blue-600 transition duration-300">
                            CONSULTA TU DEUDA
                        </a>
                        <a href="#servicios" 
                           class="border-2 border-white text-white px-8 py-4 rounded-lg font-bold text-lg text-center hover:bg-white hover:text-blue-600 transition duration-300">
                            CONOCER MÁS
                        </a>
                    </div>
                </div>
                <div class="relative">
                    <div class="bg-white/20 backdrop-blur-sm rounded-2xl p-8 transform rotate-1">
                        <div class="bg-white rounded-xl p-6 text-gray-800 shadow-2xl">
                            <h3 class="font-bold text-xl mb-4 text-blue-600">INFORMACIÓN RÁPIDA</h3>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center border-b pb-2">
                                    <span class="font-medium">Clientes Activos</span>
                                    <span class="font-bold text-blue-600">600+</span>
                                </div>
                                <div class="flex justify-between items-center border-b pb-2">
                                    <span class="font-medium">Cobertura</span>
                                    <span class="font-bold text-green-600">100%</span>
                                </div>
                                <div class="flex justify-between items-center border-b pb-2">
                                    <span class="font-medium">Atención</span>
                                    <span class="font-bold text-blue-600">24/7</span>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="font-medium">Desde</span>
                                    <span class="font-bold text-blue-600">1988</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Servicios Section -->
    <div id="servicios" class="bg-gray-50 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">NUESTROS SERVICIOS</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Brindamos servicios de calidad para garantizar el acceso al agua potable en toda la comunidad
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="service-card bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Alta de Nuevos Usuarios</h3>
                    <p class="text-gray-600">
                        Registro y conexión de nuevos usuarios al servicio de agua potable de la comunidad.
                    </p>
                </div>
                
                <div class="service-card bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Gestión de Cobros</h3>
                    <p class="text-gray-600">
                        Sistema eficiente para el control y gestión de pagos del servicio de agua.
                    </p>
                </div>
                
                <div class="service-card bg-white rounded-xl p-6 shadow-lg border border-gray-200">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Mantenimiento</h3>
                    <p class="text-gray-600">
                        Reparación y mantenimiento de la infraestructura hidráulica de la comunidad.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contacto Section -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">CONTÁCTENOS</h2>
                <p class="text-xl text-gray-600">Estamos aquí para servirle</p>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <div class="bg-blue-50 rounded-xl p-8">
                    <h3 class="text-2xl font-bold text-blue-900 mb-6">INFORMACIÓN DE CONTACTO</h3>
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
                                <p class="text-gray-600">Comunidad La Grampa, Yacuiba - Bolivia</p>
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
                                <p class="text-gray-600">Central:77817297</p>
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
                                <p class="text-gray-600">Lunes a Viernes: 02:00 PM - 06:00 PM</p>
                                <p class="text-gray-600">Sábados: 8:00 AM - 12:00 PM</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 rounded-xl p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">ACCESO AL SISTEMA</h3>
                    <p class="text-gray-600 mb-6">
                        Acceda a nuestro sistema en línea para gestionar su cuenta, consultar pagos y realizar trámites.
                    </p>
                    <div class="space-y-4">
                        @auth
                            <a href="{{ route('dashboard') }}" 
                               class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold text-center block hover:bg-blue-700 transition duration-300">
                                IR A MI CUENTA
                            </a>
                        @else
                            <a href="{{ route('login') }}" 
                               class="w-full bg-blue-600 text-white py-3 px-6 rounded-lg font-semibold text-center block hover:bg-blue-700 transition duration-300">
                                INICIAR SESIÓN
                            </a>
                            
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <x-footer />
</x-app-layout>