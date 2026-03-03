<head><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<footer class="bg-gray-800 text-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
           
            
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