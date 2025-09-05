{{-- resources/views/partials/hero.blade.php (o pégalo directo en tu vista) --}}
<section class="relative overflow-hidden">
    {{-- Fondo + overlay --}}
    <div aria-hidden="true" class="absolute inset-0">
        <img
            src="{{ asset('img/fondos/hero-otec.png') }}"
            alt=""
            class="w-full h-full object-cover object-[70%_50%]"
        >
    </div>

    {{-- Contenido --}}
    <div class="relative max-w-7xl mx-auto px-3 sm:px-6">
        <div class="min-h-[520px] md:min-h-[640px] flex items-center">
            <div class="text-white max-w-3xl">
                <h1 class="font-extrabold tracking-tight leading-tight
                           text-2xl sm:text-3xl md:text-5xl">
                    Transforma tu<br class="hidden sm:block">
                    carrera con formación<br class="hidden sm:block">
                    especializada.
                </h1>

                <p class="mt-6 text-lg md:text-xl leading-relaxed">
                    <span class="font-bold">Formación certificada,</span> docentes internacionales
                    con trayectoria clínica y acceso a herramientas de última tecnología y vanguardia.
                </p>

                <p class="mt-6 text-lg md:text-xl leading-relaxed">
                    Contáctanos y descubre el poder de aprender con Otec Mitcare.
                </p>

                <div class="mt-8 mb-8 sm:mb-0">
                    <button type="button"
                            class="inline-flex items-center rounded-md px-8 py-3 text-lg font-bold
                                   text-white bg-[#E71F6C] hover:bg-[#c41659] transition shadow-md
                                   focus:outline-none focus:ring-2 focus:ring-white/60 cursor-pointer">
                        Ver cursos
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
