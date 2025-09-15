<section id="certificaciones" class="w-full text-white">
    <div class="grid md:grid-cols-2 sm:min-h-[520px] md:min-h-[650px]">

        {{-- Panel de texto (izquierda) --}}
        <div class="bg-[#0e3252]">
            <div class="max-w-3xl mx-auto px-3 sm:px-8 lg:px-20 py-12 md:py-16">
                <h2 class="font-extrabold leading-tight text-[23px] sm:text-4xl md:text-5xl mb-6">
                    Certificaciones<br>Otec Mitcare Spa
                </h2>

                <div class="text-white/95 text-lg md:text-xl leading-relaxed text-justify [hyphens:auto]">
                    MITCARE cuenta con certificaciones nacionales e internacionales que respaldan nuestros procesos
                    formativos.
                    <br><br>
                    <strong>NCh 2728:2015</strong><br>
                    que dan funcionamiento de 2 años, es aquí que todo OTEC debe pasar por auditorías extensas para
                    poder estar vigente en nuestro territorio.
                </div>

                <div class="mt-10">
                    <a href="#cursos"
                       class="inline-flex items-center rounded-md px-6 py-3 font-bold text-white
                              bg-[#E71F6C] hover:bg-[#c41659] transition shadow-md
                              focus:outline-none focus:ring-2 focus:ring-white/60">
                        Ver cursos
                    </a>
                </div>
            </div>
        </div>

        {{-- Imagen desktop (a la derecha) --}}
        <div class="relative overflow-hidden hidden md:block">
            <img src="{{ asset('img/fondos/certificaciones.png') }}" alt="Persona mostrando diploma"
                 class="absolute inset-0 w-full h-full object-cover"
                 style="object-position: 20%;">
            {{-- Logos en desktop --}}
            <img src="{{ asset('img/logos/logos-inn-confidence.png') }}"
                 alt="Confidence Certification e INN Chile"
                 class="absolute left-1/5 -translate-x-1/2 w-28 lg:w-32 opacity-90 pointer-events-none select-none"
                 style="bottom:3rem;">
        </div>
    </div>

    {{-- Imagen en mobile (debajo del panel) --}}
    <div class="block md:hidden relative overflow-hidden">
        <img src="{{ asset('img/fondos/certificaciones.png') }}" alt="Persona mostrando diploma"
             class="w-full h-72 object-cover"
             style="object-position: 20%;">
        {{-- Logos en mobile --}}
        <div class="absolute bottom-6 left-1/2 -translate-x-1/2">
            <img src="{{ asset('img/logos/logos-inn-confidence.png') }}"
                 alt="Confidence Certification e INN Chile"
                 class="w-24 opacity-90 pointer-events-none select-none">
        </div>
    </div>
</section>
