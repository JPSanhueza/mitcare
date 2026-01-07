<section id="cursos" class="bg-[#19355C]">
    <div class=" max-w-7xl mx-auto px-4 py-10">
        <div class="text-center mb-10">
            <h2 class="text-3xl sm:text-4xl font-bold text-white">{{ $title }}</h2>
            <p class="text-base sm:text-xl font-semibold text-white/70 mt-1">{{ $subtitle }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
            @forelse ($courses as $course)
                <article
                    class="bg-white rounded-[28px] shadow-md overflow-hidden flex flex-col h-full ring-3 ring-white lg:min-w-sm mx-auto">

                    <a href="{{ route('courses.show', $course->slug) }}" class="block">
                        <img src="{{ $course->image_url }}" alt="{!! $course->nombre !!}"
                            class="w-full h-56 object-cover">
                    </a>

                    <div class="px-6 pt-5 pb-6 flex flex-col flex-1">

                        <h3 class="course-title-list">
                            {!! $course->nombre !!}
                        </h3>

                        @if ($course->descripcion)
                            <div class="mt-3 text-xs sm:text-sm text-[#19355C] text-center leading-relaxed">
                                {!! $course->descripcion !!}
                            </div>
                        @endif

                        <div class="flex-1"></div>

                        <div class="mt-5 flex items-center justify-center gap-2 text-sm sm:text-base text-[#19355C]">
                            <span
                                class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-[#E71F6C] text-white">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4"
                                    fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="8" />
                                    <path d="M12 8v4l2 2" />
                                </svg>
                            </span>
                            <span class="font-semibold">Duración:</span>
                            <span class="font-semibold">{{ sprintf('%02d', $course->total_hours) }} horas</span>
                        </div>

                        <div class="mt-6 flex gap-3">
                            <a href="{{ route('courses.show', $course->slug) }}"
                                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-full
                      bg-[#E71F6C] text-white text-xs sm:text-sm font-bold tracking-wide
                      hover:bg-[#c41659] transition">
                                MAS DETALLES
                            </a>

                            <button type="button" wire:click="addToCart({{ $course->id }})"
                                @disabled($course->pre_sale)
                                class="flex-1 inline-flex items-center justify-center px-4 py-2.5 rounded-full
           text-xs sm:text-sm font-bold tracking-wide transition
           {{ !$course->pre_sale
               ? 'bg-[#47A8DF] text-white hover:bg-[#1b7fb7]'
               : 'bg-gray-400 text-white/70 opacity-60 cursor-not-allowed' }}">
                                COMPRA AQUÍ
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full text-center text-gray-500">
                    No hay cursos disponibles por ahora.
                </div>
            @endforelse
        </div>
    </div>
</section>
