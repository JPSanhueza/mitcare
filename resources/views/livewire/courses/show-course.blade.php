<section>
    <div class="grid grid-cols-1 lg:grid-cols-2 overflow-hidden shadow-lg py-1 bg-[#47A8DF]">
        <div class="bg-[#19355C] text-white p-8 md:p-12 flex flex-col justify-start">
            <div>
                <h1 class="text-3xl md:text-5xl font-extrabold leading-tight">
                    {!! $course->nombre !!}
                </h1>
                @if ($course->subtitulo)
                    <p class="mt-6 text-white/90 text-base md:text-lg max-w-prose">
                        {!! $course->subtitulo !!}
                    </p>
                @endif
            </div>
            <div class="mt-10 flex items-center gap-4 flex-wrap">
                <span
                    class="inline-flex items-center px-5 py-3 rounded-full bg-[#ff0b78] text-white text-xl font-bold shadow">
                    {{ '$' . number_format($course->price, 0, ',', '.') }}
                </span>
                <button wire:click="addToCart"
                    class="inline-flex items-center px-6 py-3 rounded-full bg-[#41a8d8] text-white font-bold hover:brightness-110 transition">
                    Agregar al carrito
                </button>
            </div>

            @php
                $onsite = isset($course->modality) && in_array(strtolower($course->modality), ['presencial', 'mixto']);
            @endphp

            @if ($onsite && ($course->start_at || $course->end_at || $course->location))
                <div class="mt-6 space-y-2 text-sm md:text-base">
                    @if ($course->start_at)
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" />
                            </svg>
                            <span><span class="font-semibold">Inicio:</span>
                                {{ \Illuminate\Support\Carbon::parse($course->start_at)->timezone(config('app.timezone'))->translatedFormat('d
                                                                                        \\de F Y') }}
                            </span>
                        </div>
                    @endif
                    @if ($course->end_at)
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z" />
                            </svg>
                            <span><span class="font-semibold">Término:</span>
                                {{ \Illuminate\Support\Carbon::parse($course->end_at)->timezone(config('app.timezone'))->translatedFormat('d
                                                                                        \\de F Y') }}
                            </span>
                        </div>
                    @endif

                    @if ($course->location)
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 22s7-5.373 7-12A7 7 0 0 0 5 10c0 6.627 7 12 7 12z" />
                            </svg>
                            <span><span class="font-semibold">Dirección:</span> {{ $course->location }}</span>
                        </div>
                    @endif
                </div>
            @endif
        </div>
        <div class="relative flex items-center justify-center bg-[#0e3654]">
            <div class="w-full aspect-square relative">
                <img src="{{ $imageUrl }}" alt="{{ $course->nombre }}"
                    class="w-full h-full object-cover rounded-none">
                <div class="absolute inset-0 bg-[#19355C]/60"></div>
                <div class="absolute inset-0 flex flex-col p-10 md:p-22 text-xl gap-10 text-white">
                    @if ($course->descripcion)
                        <p class="text-white/90">
                            {!! $course->descripcion !!}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- <section class="bg-[#19355C]">
    <div class="mx-auto">
        <div class="relative overflow-hidden">

            <div class="absolute inset-0">
                <img src="{{ $imageUrl }}" alt="{{ $course->nombre }}" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-[#19355C]/75"></div>
            </div>

            <div
                class="relative px-6 sm:px-10 lg:px-16 py-6 sm:py-0 sm:h-[90svh] justify-center
                       flex flex-col items-center text-center text-white gap-6">

                <h1 class="course-title">
                    {!! $course->nombre !!}
                </h1>

                @if ($course->descripcion)
                    <div class="max-w-4xl mx-auto text-2xl font-normal text-white leading-none">
                        {!! $course->descripcion !!}
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap justify-center gap-4">
                    <button type="button"
                        class="px-12 py-3 rounded-full bg-[#E71F6C] text-white font-bold text-sm sm:text-xl
                                   shadow-md hover:brightness-110 transition cursor-pointer">
                        Reserva tu cupo
                    </button>

                    <button type="button" wire:click="addToCart"
                        class="px-12 py-3 rounded-full bg-[#2D9CDB] text-white font-bold text-sm sm:text-xl
                                   shadow-md hover:brightness-110 transition cursor-pointer">
                        Compra aquí
                    </button>

                    <button type="button"
                        class="px-12 py-3 rounded-full bg-[#F4A834] text-white font-bold text-sm sm:text-xl
                                   shadow-md hover:brightness-110 transition cursor-pointer">
                        Descarga ficha
                    </button>
                </div>

                @php
                    $teachersLabel = match (strtolower((string) $course->teachers_type)) {
                        'nacional', 'nacionales' => 'nacionales',
                        'internacional', 'internacionales' => 'internacionales',
                        default => $course->teachers_type ? $course->teachers_type : 'nacionales',
                    };

                    $modalityLabel = $course->modality ? ucfirst($course->modality) : 'Por definir';
                @endphp

                <div class="mt-8 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 justify-center gap-3 lg:gap-6">
                    <div
                        class="bg-white text-[#19355C] rounded-3xl px-6 py-3 min-w-[100px] sm:min-w-[170px]
                               flex flex-col items-center text-sm sm:text-base shadow">
                        <span class="font-semibold">Duración:</span>
                        <span class="font-extrabold">
                            {{ (int) $course->total_hours }} horas
                        </span>
                    </div>

                    <div
                        class="bg-white text-[#19355C] rounded-3xl px-6 py-3 min-w-[100px] sm:min-w-[170px]
                               flex flex-col items-center text-sm sm:text-base shadow">
                        <span class="font-semibold">Valor:</span>
                        <span class="font-extrabold">
                            {{ '$' . number_format($course->price, 0, ',', '.') }}
                        </span>
                    </div>

                    <div
                        class="bg-white text-[#19355C] rounded-3xl px-6 py-3 min-w-[100px] sm:min-w-[170px]
                               flex flex-col items-center text-sm sm:text-base shadow">
                        <span class="font-semibold">Docentes</span>
                        <span class="font-extrabold lowercase first-letter:uppercase">
                            {{ $teachersLabel }}
                        </span>
                    </div>

                    <div
                        class="bg-white text-[#19355C] rounded-3xl px-6 py-3 min-w-[100px] sm:min-w-[170px]
                               flex flex-col items-center text-sm sm:text-base shadow">
                        <span class="font-semibold">Curso</span>
                        <span class="font-extrabold">Certificado</span>
                    </div>

                    <div
                        class="bg-white text-[#19355C] rounded-3xl px-6 py-3 min-w-[100px] sm:min-w-[170px]
                               flex flex-col items-center text-sm sm:text-base shadow">
                        <span class="font-semibold">Modalidad</span>
                        <span class="font-extrabold lowercase first-letter:uppercase">
                            {{ $modalityLabel }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section> --}}
