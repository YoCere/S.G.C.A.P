
<x-app-layout>
    <!-- Hero Section Nosotros -->
    <div class="hero-gradient text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl lg:text-5xl font-bold mb-4">SOBRE NOSOTROS</h1>
                <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                    Conoce la historia y trayectoria del Comité de Agua de La Grampa, 
                    sirviendo a nuestra comunidad desde 1988
                </p>
            </div>
        </div>
    </div>

    <!-- Historia Section -->
<div class="bg-white py-16">
    <div class="max-w-7xl mx-auto px-6 lg:px-12">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        
        <!-- Imagen o logo -->
        <div class="flex justify-center lg:justify-end">
          <div class="bg-blue-50 rounded-2xl p-4 shadow-lg">
            <img 
              src="{{ asset('images/logo.png') }}" 
              alt="Logo Comité de Agua La Grampa"
              class="max-w-[350px] lg:max-w-[450px] w-full h-auto rounded-xl object-contain mx-auto"
            >
          </div>
        </div>
  
        <!-- Contenido histórico -->
        <div class="space-y-6 text-center lg:text-left">
          <h2 class="text-3xl font-bold text-gray-900">NUESTRA HISTORIA</h2>
  
          <div class="space-y-4 text-gray-600 leading-relaxed text-justify">
            <p>
              El <strong>Comité de Agua de la comunidad de "La Grampa"</strong> fue fundado en el año 
              <strong class="text-blue-600">1988</strong>, formalmente aperturado bajo la supervisión del 
              <strong>Notario de Fe Pública J. Fridel Mendoza Coria</strong>.
            </p>
  
            <p>
              Desde su creación, hemos desempeñado un papel fundamental en la prestación de servicios 
              esenciales para la comunidad, consolidándonos como un pilar en el desarrollo y bienestar 
              de la región.
            </p>
  
            <p>
              A lo largo de <strong>más de tres décadas</strong>, nuestra institución ha demostrado 
              un compromiso inquebrantable con la mejora continua y la satisfacción de las necesidades 
              de nuestros aproximadamente <strong>600 clientes</strong>.
            </p>
  
            <p>
              Con una trayectoria marcada por la dedicación y el esfuerzo, hemos logrado superar 
              diversos desafíos, adaptándonos a los cambios y evolucionando para ofrecer un servicio 
              de calidad que impacta positivamente en la vida de cientos de familias y contribuye 
              al progreso de nuestra comunidad.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
  

    <!-- Estadísticas -->
    <div class="bg-blue-600 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl font-bold mb-2">600+</div>
                    <div class="text-blue-100">Clientes Activos</div>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2">100%</div>
                    <div class="text-blue-100">Cobertura</div>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2">24/7</div>
                    <div class="text-blue-100">Atención</div>
                </div>
                <div>
                    <div class="text-3xl font-bold mb-2">1988</div>
                    <div class="text-blue-100">Desde</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipo de Trabajo -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">NUESTRO COMPROMISO</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Trabajamos incansablemente para garantizar el acceso al agua potable 
                    y mejorar la calidad de vida en nuestra comunidad
                </p>
            </div>
            
            <div class="bg-blue-50 rounded-2xl p-8 max-w-4xl mx-auto">
                <div class="text-center">
                    <h3 class="text-2xl font-bold text-blue-900 mb-4">¿Necesitas contactarnos?</h3>
                    <p class="text-blue-800 mb-6">
                        Estamos aquí para servirte y resolver cualquier inquietud sobre nuestro servicio
                    </p>
                    <a href="{{ url('/contacto') }}" 
                       class="inline-block bg-blue-600 text-white px-8 py-3 rounded-lg font-semibold hover:bg-blue-700 transition duration-300">
                        CONTÁCTANOS
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>