<section class="bg-[#19355C]">
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
                    <button type="button" wire:click="addToCart"
                        class="px-12 py-3 rounded-full bg-[#E71F6C] text-white font-bold text-sm sm:text-xl
                                   shadow-md hover:brightness-110 transition cursor-pointer">
                        Reserva tu cupo
                    </button>

                    <button type="button" wire:click="addToCart"
                        class="px-12 py-3 rounded-full bg-[#2D9CDB] text-white font-bold text-sm sm:text-xl
                                   shadow-md hover:brightness-110 transition cursor-pointer">
                        Compra aquí
                    </button>

                    <button type="button" wire:click="downloadFicha" wire:loading.attr="disabled"
                        wire:target="downloadFicha" @disabled(blank($course->ficha))
                        class="relative px-12 py-3 rounded-full text-white font-bold text-sm sm:text-xl shadow-md transition cursor-pointer
        {{ blank($course->ficha) ? 'bg-gray-400 cursor-not-allowed opacity-60' : 'bg-[#F4A834] hover:brightness-110' }}">
                        <!-- Texto normal -->
                        <span wire:loading.remove wire:target="downloadFicha">
                            Descarga ficha
                        </span>

                        <!-- Estado cargando -->
                        <span wire:loading wire:target="downloadFicha" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z">
                                </path>
                            </svg>
                        </span>
                    </button>
                </div>

                @php
                    $teachersLabel = match (strtolower((string) $course->teachers_type)) {
                        'nacional', 'nacionales' => 'nacionales',
                        'internacional', 'internacionales' => 'internacionales',
                        default => $course->teachers_type ? $course->teachers_type : 'nacionales',
                    };

                    $modalityLabel = match (strtolower((string) $course->modality)) {
                        'online' => 'Asincrónica',
                        default => $course->modality ? ucfirst($course->modality) : 'Por definir',
                    };
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
</section>
