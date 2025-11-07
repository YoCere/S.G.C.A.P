<head><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<footer class="bg-gray-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div>
                <h4 class="text-lg font-bold mb-4">COMITÉ DE AGUA LA GRAMPA</h4>
                <p class="text-gray-400">
                    Servicio confiable de agua potable para la comunidad de La Grampa, Yacuiba.
                </p>
            </div>
            
            <div>
                <h4 class="text-lg font-bold mb-4">ENLACES RÁPIDOS</h4>
                <ul class="space-y-2 text-gray-400">
                    <li><a href="{{ url('/') }}" class="hover:text-white transition duration-300">Inicio</a></li>
                    <li><a href="{{ url('/nosotros') }}" class="hover:text-white transition duration-300">Nosotros</a></li>
                    <li><a href="{{ url('/servicios') }}" class="hover:text-white transition duration-300">Servicios</a></li>
                    <li><a href="{{ url('/tarifas') }}" class="hover:text-white transition duration-300">Tarifas</a></li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-lg font-bold mb-4">CONTACTO</h4>
                <ul class="space-y-2 text-gray-400">
                    <li class="flex items-start space-x-2">
                        <svg class="w-4 h-4 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span>La Grampa, Yacuiba - Bolivia</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <svg class="w-4 h-4 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>Whatsapp: 77817297</span>
                    </li>
                    <li class="flex items-start space-x-2">
                        <svg class="w-4 h-4 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>comiteaguagrampa@yacuiba.bo</span>
                    </li>
                </ul>
            </div>
            
            <div>
                <h4 class="text-lg font-bold mb-4">DESARROLLADO POR</h4>
                <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-lg p-4 text-center shadow-lg">
                    <!-- Información del desarrollador -->
                    <div class="mb-3">
                        <p class="text-white font-bold text-sm mb-1">Jose Alfredo Cerezo Rios</p>
                        <a href="https://github.com/YoCere" target="_blank" class="text-white text-xl hover:text-gray-300 transition duration-300">
                            <i class="fab fa-github"></i>
                        </a>
                        <a href="https://www.linkedin.com/in/jose-alfredo-cerezo-rios-a380a2282/" target="_blank"
                        class="text-white text-2xl hover:text-blue-400 transition duration-300">
                            <i class="fab fa-linkedin"></i>
                        </a>
                        <p class="text-blue-100 text-xs">Desarrollador</p>
                    </div>
                    
                    
                    <!-- Información de la universidad -->
                    <div class="border-t border-blue-400 pt-3">
                        <p class="text-white font-semibold text-xs mb-2">
                            UNIVERSIDAD AUTÓNOMA<br>
                            "JUAN MISAEL SARACHO"
                        </p>
                        <p class="text-blue-100 text-xs mb-2">
                            FIRNT<br>
                            Carrera de Ingeniería Informática
                        </p>
                        <a href="https://www.uajms.edu.bo/" 
                           target="_blank" 
                           class="text-blue-200 hover:text-white text-xs underline transition duration-300">
                            www.uajms.edu.bo
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="border-t border-gray-700 mt-8 pt-8 text-center">
            <p class="text-gray-400">
                Copyright © {{ date('Y') }} Comité de Agua La Grampa - Todos los Derechos Reservados.
            </p>
            <p class="text-gray-500 text-sm mt-2">
                Sistema desarrollado como proyecto académico - Universidad Autónoma "Juan Misael Saracho"
            </p>
        </div>
    </div>
</footer>