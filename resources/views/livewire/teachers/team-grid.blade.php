<section id="docentes" class="max-w-7xl mx-auto px-3 py-12">
    {{-- Título / subtítulo --}}
    <div class="text-center mb-10">
        <h2 class="text-3xl sm:text-4xl font-bold text-[#19355C]">{{ $title }}</h2>
        <p class="max-w-5xl mx-auto text-base sm:text-xl font-semibold text-[#19355C]/70 mt-1 leading-relaxed text-justify [hyphens:auto]">{{ $subtitle }}</p>
    </div>

    {{-- Grilla de avatares --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-y-12 place-items-center -mx-2 sm:mx-0">
        @forelse ($teachers as $t)
        <figure class="flex flex-col items-center">
            <div class="w-22 h-26 sm:w-24 sm:h-32 md:w-52 md:h-62 overflow-hidden">
                <img src="{{ $t->foto_url }}" alt="{{ $t->nombre }}" class="w-full h-full object-cover" loading="lazy">
            </div>
            @if($showNames)
            <figcaption class="mt-3 text-sm font-semibold text-[#19355C] text-center">
                {{ $t->nombre }}
            </figcaption>
            @endif
        </figure>
        @empty
        <div class="col-span-full text-center text-gray-500">
            No hay docentes para mostrar por ahora.
        </div>
        @endforelse
    </div>
</section>
