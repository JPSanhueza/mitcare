<section id="cursos" class="max-w-7xl mx-auto px-4 py-10">
    {{-- Título / subtítulo --}}
    <div class="text-center mb-10">
        <h2 class="text-3xl sm:text-4xl font-bold text-[#19355C]">{{ $title }}</h2>
        <p class="text-base sm:text-xl font-semibold text-[#19355C]/70 mt-1">{{ $subtitle }}</p>
    </div>

    {{-- Grid de tarjetas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
        @forelse ($courses as $course)
        <article class="flex flex-col items-center group">
            <div class="relative w-full">
                <div class="aspect-square w-full overflow-hidden group-hover:scale-95
                    transition-transform duration-300">
                    <a href="{{ route('courses.show', $course->slug) }}">
                        <img src="{{ $course->image_url }}" alt="{{ $course->nombre }}"
                            class="w-full h-full object-cover" loading="lazy">
                    </a>
                </div>
            </div>

            {{-- Título del curso --}}
            <h3 class="my-6 sm:text-center text-base sm:text-lg font-bold
                text-[#19355C] leading-relaxed text-justify [hyphens:auto]">
                {{ $course->nombre }}
            </h3>

            {{-- Botón Más información (solo linkea por ahora) --}}
            <div>
                <a href="{{ route('courses.show', $course->slug) }}" class="inline-block bg-[#E71F6C] hover:bg-[#c41659]
                    text-white font-bold text-base sm:text-lg px-4 md:px-8 py-2">
                    Más información
                </a>
            </div>
        </article>
        @empty
        <div class="col-span-full text-center text-gray-500">No hay cursos disponibles por ahora.</div>
        @endforelse
    </div>
</section>
