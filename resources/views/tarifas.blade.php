<x-app-layout>
    <!-- Hero Section Tarifas -->
    <div class="hero-gradient text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl lg:text-5xl font-bold mb-4">TARIFAS Y PLANES</h1>
                <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                    Conoce nuestras tarifas transparentes y accesibles para el servicio de agua potable
                </p>
            </div>
        </div>
    </div>

    <!-- Tarifas Principales -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($tarifas->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($tarifas as $index => $tarifa)
                <div class="bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden hover:shadow-xl transition duration-300 
                    {{ $index == 1 ? 'border-2 border-blue-500 transform hover:scale-105' : '' }}">
                    
                    <!-- Header de la tarifa -->
                    <div class="bg-blue-600 text-white py-6 text-center relative">
                        @if($index == 1 && $tarifas->count() > 1)
                        <div class="absolute -top-3 -right-3 bg-orange-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            POPULAR
                        </div>
                        @endif
                        <h3 class="text-2xl font-bold">{{ $tarifa->nombre }}</h3>
                        <div class="mt-2">
                            <span class="text-3xl font-bold">Bs. {{ number_format($tarifa->precio_mensual, 2) }}</span>
                            <span class="text-blue-100">/mes</span>
                        </div>
                    </div>
                    
                    <!-- Contenido de la tarifa -->
                    <div class="p-6">
                        <div class="text-gray-600 leading-relaxed">
                            {{ $tarifa->descripcion ?? 'Plan de servicio de agua potable' }}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="text-center py-12">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No hay tarifas disponibles</h3>
                <p class="text-gray-600">Actualmente no hay tarifas activas para mostrar.</p>
            </div>
            @endif
        </div>
    </div>

    
    <!-- CTA Section -->
    <div class="bg-blue-600 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl font-bold mb-4">¿Necesitas más información?</h2>
            <p class="text-xl text-blue-100 mb-8 max-w-2xl mx-auto">
                Nuestro equipo está disponible para resolver todas tus dudas sobre tarifas y servicios
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="{{ route('contacto') }}" 
                   class="inline-block bg-white text-blue-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition duration-300">
                    CONTACTARNOS
                </a>
                <a href="{{ route('servicios') }}" 
                   class="inline-block border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-blue-600 transition duration-300">
                    VER SERVICIOS
                </a>
            </div>
        </div>
    </div>
</x-app-layout>