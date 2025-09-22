<nav x-data="{ open:false }" class="sticky top-0 z-50 bg-[#19355C] text-white">
    <div class="max-w-7xl mx-auto px-2 sm:px-4">
        <div class="h-22 flex items-center justify-between gap-2">
            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                <img src="{{ asset('img/logos/otec-logo-blanco.png') }}" alt="OTEC Mitcare" class="h-10 sm:h-16 w-auto">
            </a>

            <!-- Links (desktop) -->
            <ul class="hidden lg:flex items-center gap-12 text-lg md:text-xl">
                <li>
                    <a href="{{ route('home') }}" class="hover:opacity-75">
                        Inicio
                    </a>
                </li>
                <li>
                    <a href="/#quienes-somos" class="hover:opacity-75">
                        Quienes somos
                    </a>
                </li>
                <li>
                    <a href="/#cursos" class="hover:opacity-75">
                        Cursos
                    </a>
                </li>
                <li>
                    <a href="/#docentes" class="hover:opacity-75">
                        Docentes
                    </a>
                </li>
                <li>
                    <a href="/#certificaciones" class="hover:opacity-75">
                        Certificaciones
                    </a>
                </li>
            </ul>

            <!-- Botón Contacto (desktop) -->
            <div class="hidden lg:block">
                <a href="/#contacto" class="inline-flex items-center rounded-2xl px-4 py-2 text-lg
                font-semibold bg-[#E71F6C] hover:bg-[#c41659] transition shadow-md">
                    Contacto
                </a>
            </div>

            <div class="hidden lg:block">
                <a href="https://aulavirtual.otecmitcare.cl/" target="_blank" class="inline-flex items-center rounded-2xl
                px-3 py-2 text-lg font-semibold bg-[#47A8DF] hover:bg-[#269ade] transition shadow-md">
                    Aula Virtual
                </a>
            </div>

            <!-- Hamburguesa (móvil) -->
            <button @click="open = !open" class="lg:hidden inline-flex items-center justify-center h-10 w-10">
                <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg x-show="open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Panel móvil -->
    <div x-show="open" x-transition.origin.top class="lg:hidden border-t border-white/10">
        <div class="px-4 py-3 space-y-2 text-base">
            <a href="{{ route('home') }}" class="block py-2 hover:opacity-80">Inicio</a>
            <a href="/#quienes-somos" class="block py-2 hover:opacity-80">Quienes somos</a>
            <a href="/#cursos" class="block py-2 hover:opacity-80">Cursos</a>
            <a href="/#docentes" class="block py-2 hover:opacity-80">Docentes</a>
            <a href="/#certificaciones" class="block py-2 hover:opacity-80">Certificaciones</a>

            <a href="/#contacto" class="mt-2 inline-flex w-full justify-center
            rounded-full px-5 py-2.5 font-semibold bg-[#E71F6C] hover:bg-[#c41659] transition shadow-md">
                Contacto
            </a>
            <a href="https://aulavirtual.otecmitcare.cl/" class="mt-2 inline-flex w-full justify-center
            rounded-full px-5 py-2.5 font-semibold bg-[#47A8DF] hover:bg-[#269ade] transition shadow-md">
                Aula Virtual
            </a>
        </div>
    </div>
</nav>
