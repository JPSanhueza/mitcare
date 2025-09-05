<footer class="bg-[#0e395e] text-white">
    <div class="max-w-7xl mx-auto px-4 pt-10 pb-5 lg:pt-14 lg:pb-6 text-center lg:text-start">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-center">
            {{-- Columna 1: Logo + redes --}}
            <div class="space-y-2 lg:space-y-4 flex flex-col lg:items-start items-center">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/logos/otec-logo-blanco.png') }}" alt="OTEC Mitcare"
                        class="h-18 sm:h-32 w-auto">
                </div>

                <div class="flex items-center gap-1 mx-7">
                    @php
                    $socialBtn = 'inline-flex h-8 sm:h-10 w-8 sm:w-10 items-center justify-center rounded-full
                    transition cursor-pointer hover:scale-105 transition-transform duration-200';
                    @endphp

                    <img src="{{ asset('img/logos/facebook.png') }}" alt="Facebook" class="{{ $socialBtn }}">

                    <img src="{{ asset('img/logos/instagram.png') }}" alt="Instagram" class="{{ $socialBtn }}">

                    <img src="{{ asset('img/logos/youtube.png') }}" alt="YouTube" class="{{ $socialBtn }}">

                    <img src="{{ asset('img/logos/whatsapp.png') }}" alt="WhatsApp" class="{{ $socialBtn }}">
                </div>
            </div>

            {{-- Columna 2: Dirección + contacto (con separador en desktop a la derecha) --}}
            <div class="lg:border-r lg:border-white/30 lg:pr-10 justify-self-center lg:justify-self-auto">
                <ul class="space-y-5 text-lg leading-7 w-fit mx-auto lg:w-auto lg:mx-0">
                    <li class="flex items-start gap-4 justify-center lg:justify-start">
                        <i class="fa-solid fa-location-dot mt-1 text-2xl"></i>
                        <p>
                            Colo colo 222 Of 412,<br> Concepción.
                        </p>
                    </li>
                    <li class="flex items-center gap-4 justify-center lg:justify-start">
                        <i class="fa-solid fa-phone text-2xl"></i>
                        <a href="tel:+56965901501" class="hover:underline">+56 9 6590 1501</a>
                    </li>
                    <li class="flex items-center gap-4 justify-center lg:justify-start">
                        <i class="fa-regular fa-envelope text-2xl"></i>
                        <a href="mailto:otecmitcare@gmail.com"
                            class="hover:underline break-all">otecmitcare@gmail.com</a>
                    </li>
                </ul>
            </div>

            {{-- Columna 3: Horarios (con separador a la izquierda en desktop) --}}
            <div class="lg:pl-10 border-t lg:border-t-0 border-white/30 pt-10 lg:pt-0">
                <div class="space-y-7">
                    <div>
                        <h4 class="font-semibold text-xl">Horario de atención:</h4>
                        <p class="text-lg mt-1">Lun - Vie de 09:00 - 18:00</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-xl">Horario de colación:</h4>
                        <p class="text-lg mt-1">13:00 a 14:00</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- línea inferior / derechos --}}
        <div class="mt-10 border-t border-white/10 pt-6 text-sm
             text-white/80 flex flex-col md:flex-row gap-3 md:items-center md:justify-between">
            <p>© {{ date('Y') }} OTEC MITCARE. Todos los derechos reservados.</p>
            <p>
                Sitio web desarrollado por
                <a href="https://codari.cl/" target="_blank" rel="noopener noreferrer" class=" hover:text-[#42A8C3]">
                    Codari
                </a>
            </p>
        </div>
    </div>
</footer>
