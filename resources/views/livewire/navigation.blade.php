<nav class="relative bg-gray-800/50 after:pointer-events-none after:absolute after:inset-x-0 after:bottom-0 after:h-px after:bg-white/10" x-data="{open: false}">
    <div class="mx-auto max-w-7xl px-2 sm:px-6 lg:px-8">
        <div class="relative flex h-16 items-center justify-between">
            <!-- Mobile menu button-->
            <div class="absolute inset-y-0 left-0 flex items-center sm:hidden">
                <button x-on:click="open = true" type="button" class="relative inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-white/5 hover:text-white focus:outline-2 focus:-outline-offset-1 focus:outline-indigo-500">
                    <span class="absolute -inset-0.5"></span>
                    <span class="sr-only">Abrir menú principal</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6" :class="open ? 'hidden' : 'block'">
                        <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6" :class="open ? 'block' : 'hidden'">
                        <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </button>
            </div>
            <div class="flex flex-1 items-center justify-center sm:items-stretch sm:justify-start">
                <a href="{{ route('welcome') }}" class="flex shrink-0 items-center">
                    <img src="{{ asset('images/just_logo.png') }}" alt="Comité de Agua La Grampa - Yacuiba" class="h-8 w-auto" />
                </a>
                <div class="hidden sm:ml-6 sm:block">
                    <div class="flex space-x-4">
                        <a href="{{ route('welcome') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('welcome') ? 'bg-gray-950/50 text-white' : '' }}">INICIO</a>
                        <a href="{{ route('consultar-deuda') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('consultar-deuda') ? 'bg-gray-950/50 text-white' : '' }}">CONSULTAR DEUDA</a>
                        <a href="{{ route('tarifas') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('tarifas') ? 'bg-gray-950/50 text-white' : '' }}">TARIFAS</a></a>
                        <a href="{{ route('nosotros') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('nosotros') ? 'bg-gray-950/50 text-white' : '' }}">NOSOTROS</a>
                        <a href="{{ route('servicios') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('servicios') ? 'bg-gray-950/50 text-white' : '' }}">SERVICIOS</a>
                        <a href="{{ route('contacto') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('contacto') ? 'bg-gray-950/50 text-white' : '' }}">CONTACTO</a>
                    </div>
                </div>
            </div>
            
            <!-- Right side: Login/User menu + University Logo -->
            <div class="flex items-center space-x-4">
                @auth
                <div class="flex items-center space-x-4">
                    <button type="button" class="relative rounded-full p-1 text-gray-400 hover:text-white focus:outline-2 focus:outline-offset-2 focus:outline-indigo-500">
                        <span class="absolute -inset-1.5"></span>
                        <span class="sr-only">Ver notificaciones</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6">
                            <path d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </button>
  
                    <!-- Profile dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <!-- Botón -->
                        <button @click="open = !open" type="button"
                            class="relative flex rounded-full focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500">
                            <img src="{{ auth()->user()->profile_photo_url }}"
                                alt="User" class="size-8 rounded-full bg-gray-800 outline -outline-offset-1 outline-white/10" />
                        </button>
                    
                        <!-- Menú -->
                        <div x-show="open" @click.away="open = false" x-transition x-cloak
                            class="absolute right-0 z-50 mt-2 w-48 origin-top-right rounded-md bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5">
                            <a href="{{ route('profile.show') }}"
                                class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white">Tu perfil</a>
                            <a href="{{ route('admin.home') }}"
                                class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white">Dashboard</a>
  
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 hover:bg-white/5 hover:text-white">Ingresar</a>
                </div>
                @endauth
                
                <!-- University Logo -->
                <div class="hidden md:flex items-center">
                    <a href="https://www.uajms.edu.bo/firnt/" target="_blank" rel="noopener noreferrer" class="flex items-center hover:opacity-100 transition-opacity">
                        <img src="{{ asset('images/uajms-logo.png') }}" alt="Universidad Autónoma Juan Misael Saracho" class="h-10 w-auto opacity-80 hover:opacity-100 transition-opacity" />
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="block sm:hidden" x-show="open" x-transition x-cloak>
        <div class="space-y-1 px-2 pt-2 pb-3 bg-gray-800 border-t border-gray-700">
            <a href="{{ route('welcome') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('welcome') ? 'bg-gray-950/50 text-white' : '' }}">INICIO</a>
            <a href="{{ route('consultar-deuda') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('consultar-deuda') ? 'bg-gray-950/50 text-white' : '' }}">CONSULTAR DEUDA</a>
            <a href="{{ route('tarifas') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('tarifas') ? 'bg-gray-950/50 text-white' : '' }}">TARIFAS</a>
            <a href="{{ route('nosotros') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('nosotros') ? 'bg-gray-950/50 text-white' : '' }}">NOSOTROS</a>
            <a href="{{ route('servicios') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('servicios') ? 'bg-gray-950/50 text-white' : '' }}">SERVICIOS</a>
            <a href="{{ route('contacto') }}" class="block rounded-md px-3 py-2 text-base font-medium text-gray-300 hover:bg-white/5 hover:text-white {{ request()->routeIs('contacto') ? 'bg-gray-950/50 text-white' : '' }}">CONTACTO</a>
            
            <!-- University Logo in mobile menu -->
            <div class="pt-4 border-t border-gray-700 flex justify-center">
                <a href="https://www.uajms.edu.bo/firnt/" target="_blank" rel="noopener noreferrer" class="flex items-center">
                    <img src="{{ asset('images/uajms-logo.png') }}" alt="Universidad Autónoma Juan Misael Saracho" class="h-10 w-auto opacity-80" />
                </a>
            </div>
        </div>
    </div>
</nav>