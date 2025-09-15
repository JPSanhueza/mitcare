{{-- Sección Testimonio + CTA final --}}
<section class="relative text-white overflow-hidden">
    {{-- Fondo --}}
    <img src="{{ asset('img/fondos/testimonios.png') }}" alt="Profesionales revisando tablet"
        class="absolute inset-0 w-full h-full object-cover" style="object-position: 50% 50%;">

    {{-- TESTIMONIO --}}
    <div class="relative max-w-6xl mx-auto px-3 py-12 md:py-20">
        <div class="flex flex-col items-center text-center gap-4">
            <img src="{{ asset('img/icons/estrellas.png') }}" alt="5 estrellas" class="h-8 md:h-10 w-auto" />

            <p class="max-w-5xl text-[#1A3A50] md:text-[#274A64] text-lg md:text-xl font-medium leading-relaxed text-justify [hyphens:auto]">
                "Me encantó la experiencia en OTEC Mitcare. El curso fue claro, actualizado y con un enfoque muy
                práctico.
                Los docentes tienen un nivel excelente y siempre estuvieron dispuestos a responder nuestras dudas.
                Me voy con herramientas reales para aplicar en mi trabajo y con muchas ganas de seguir perfeccionándome
                con ellos."
            </p>

            {{-- Autora --}}
            <div class="flex flex-col items-center mt-2 w-full max-w-4xl">
                <span class="text-[#143956] text-xl md:text-2xl font-extrabold text-center block w-full">
                    Camila R., Fonoaudióloga
                </span>
            </div>
        </div>
        <div class="flex justify-end w-full">
            <img src="{{ asset('img/logos/google.png') }}" alt="Google" class="h-10 md:h-12 w-auto" />
        </div>
    </div>

    {{-- CTA inferior --}}
    <div class="relative max-w-6xl mx-auto px-3 py-12 md:py-16">
        <div class="grid place-items-center text-center">
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold tracking-tight">
                ¿Listo para potenciar tu desarrollo profesional?
            </h2>

            <p class="mt-4 text-lg md:text-2xl leading-relaxed text-justify [hyphens:auto]">
                Inscríbete hoy en nuestros cursos certificados y da el siguiente paso en tu especialidad.
            </p>
            <p class="text-lg md:text-2xl font-bold leading-relaxed text-justify [hyphens:auto]">
                Nuestro equipo docente está aquí para acompañarte.
            </p>

            {{-- Redes sociales --}}
            <div class="mt-6 flex items-center gap-1">
                @php
                $socialBtn = 'inline-flex h-8 sm:h-10 w-8 sm:w-10 items-center justify-center rounded-full
                transition cursor-pointer hover:scale-105 transition-transform duration-200';
                @endphp

                <a href="https://facebook.com" target="_blank" aria-label="Facebook">
                    <img src="{{ asset('img/logos/facebook.png') }}" alt="Facebook" class="{{ $socialBtn }}">
                </a>

                <a href="https://instagram.com" target="_blank" aria-label="Instagram">
                    <img src="{{ asset('img/logos/instagram.png') }}" alt="Instagram" class="{{ $socialBtn }}">
                </a>

                <a href="https://youtube.com" target="_blank" aria-label="YouTube">
                    <img src="{{ asset('img/logos/youtube.png') }}" alt="YouTube" class="{{ $socialBtn }}">
                </a>

                <a href="https://wa.me/56965901501" target="_blank" aria-label="WhatsApp">
                    <img src="{{ asset('img/logos/whatsapp.png') }}" alt="WhatsApp" class="{{ $socialBtn }}">
                </a>
            </div>
        </div>
</section>
