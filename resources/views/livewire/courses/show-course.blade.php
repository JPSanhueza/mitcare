<section class="bg-[#19355C]">
    {{-- MODAL OBLIGATORIO PARA DESCARGAR FICHA --}}
    @if ($showFichaModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center px-4 pb-4 sm:pb-0">
            <div class="absolute inset-0 bg-black/60" wire:click="$set('showFichaModal', false)"></div>

            <div
                class="relative w-full max-w-lg rounded-3xl bg-[#19355C] text-white shadow-2xl border border-white/15
                   max-h-[85dvh] overflow-hidden">

                <div
                    class="p-6 sm:p-8 text-left overflow-y-auto overscroll-contain max-h-[85dvh] [-webkit-overflow-scrolling:touch]">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                                Antes de descargar la ficha
                            </h3>
                            <p class="mt-2 text-white/85">
                                Completa estos datos para continuar con la descarga.
                            </p>
                        </div>

                        <button type="button"
                            class="rounded-full px-3 py-2 bg-white/10 hover:bg-white/15 transition shrink-0"
                            wire:click="$set('showFichaModal', false)">
                            ✕
                        </button>
                    </div>

                    <div class="mt-6 space-y-4">
                        {{-- Nombre --}}
                        <div>
                            <label class="text-sm font-semibold text-white/90">Nombre completo</label>
                            <input type="text" wire:model.defer="ficha_full_name"
                                class="mt-2 w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-1.5
                                   outline-none focus:ring-2 focus:ring-[#2D9CDB] focus:border-[#2D9CDB]"
                                placeholder="Macarena Torres">
                            @error('ficha_full_name')
                                <p class="mt-2 text-sm text-[#F4A834] font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Correo --}}
                        <div>
                            <label class="text-sm font-semibold text-white/90">Correo</label>
                            <input type="email" wire:model.defer="ficha_email"
                                class="mt-2 w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-1.5
                                   outline-none focus:ring-2 focus:ring-[#2D9CDB] focus:border-[#2D9CDB]"
                                placeholder="correo@ejemplo.cl">
                            @error('ficha_email')
                                <p class="mt-2 text-sm text-[#F4A834] font-semibold">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Teléfono --}}
                        <div>
                            <label class="text-sm font-semibold text-white/90">Teléfono</label>

                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-1">
                                    <input type="text" inputmode="numeric" autocomplete="tel-country-code"
                                        wire:model.defer="ficha_phone_country"
                                        x-on:input="
        let v = $event.target.value;
        v = v.replace(/[^\d+]/g, '');   // solo + y dígitos
        v = v.replace(/(?!^)\+/g, '');  // solo un + y solo al inicio
        $event.target.value = v;
    "
                                        class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-1.5
           outline-none focus:ring-2 focus:ring-[#E71F6C] focus:border-[#E71F6C]"
                                        placeholder="+56" />

                                    @error('ficha_phone_country')
                                        <p class="mt-2 text-sm text-[#F4A834] font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <input type="text" inputmode="numeric" pattern="[0-9]*"
                                        autocomplete="tel-national" wire:model.defer="ficha_phone_number"
                                        x-on:input="$event.target.value = $event.target.value.replace(/\D/g, '')"
                                        class="w-full rounded-2xl bg-white/10 border border-white/20 px-4 py-1.5
           outline-none focus:ring-2 focus:ring-[#E71F6C] focus:border-[#E71F6C]"
                                        placeholder="912345678" />


                                    @error('ficha_phone_number')
                                        <p class="mt-2 text-sm text-[#F4A834] font-semibold">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <p class="mt-1 text-xs text-white/70">Ej: +56 912345678</p>
                        </div>
                    </div>

                    <div class="mt-7 flex flex-col sm:flex-row gap-3 sm:justify-end">
                        <button type="button"
                            class="px-6 py-1.5 rounded-full font-bold bg-white/10 hover:bg-white/15 transition"
                            wire:click="$set('showFichaModal', false)">
                            Cancelar
                        </button>

                        <button type="button" wire:click="submitFichaAndDownload" wire:loading.attr="disabled"
                            wire:target="submitFichaAndDownload"
                            class="px-8 py-1.5 rounded-full font-bold shadow-md transition
                               bg-[#F4A834] text-white hover:brightness-110 disabled:opacity-60">
                            <span wire:loading.remove wire:target="submitFichaAndDownload">
                                Descargar ficha
                            </span>

                            <span wire:loading wire:target="submitFichaAndDownload"
                                class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                    fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </span>
                        </button>
                    </div>

                    <div class="h-2"></div>
                </div>
            </div>
        </div>
    @endif


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

                @if ($course->pre_sale)
                    <div
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2
                bg-white/10 border border-white/25 backdrop-blur
                text-white font-semibold tracking-wide shadow-sm">
                        <span class="h-2.5 w-2.5 rounded-full bg-[#F4A834] animate-pulse"></span>
                        <span class="uppercase text-xs sm:text-sm">Preventa</span>
                        <span class="hidden sm:inline text-white/80 font-normal normal-case">
                            Cupos disponibles con acceso al lanzamiento
                        </span>
                    </div>
                @endif


                @if ($course->descripcion)
                    <div class="max-w-4xl mx-auto text-2xl font-normal text-white leading-none">
                        {!! $course->descripcion !!}
                    </div>
                @endif

                <div class="mt-6 flex flex-wrap justify-center gap-4">

                    {{-- RESERVA TU CUPO --}}
                    <button type="button" wire:click="{{ $course->pre_sale ? 'addToCart' : '' }}"
                        @disabled(!$course->pre_sale)
                        class="px-12 py-3 rounded-full font-bold text-sm sm:text-xl shadow-md transition
            {{ $course->pre_sale
                ? 'bg-[#E71F6C] text-white hover:brightness-110 cursor-pointer'
                : 'bg-gray-400 text-white/70  opacity-60' }}">
                        Reserva tu cupo
                    </button>

                    {{-- COMPRA AQUÍ --}}
                    <button type="button" wire:click="{{ !$course->pre_sale ? 'addToCart' : '' }}"
                        @disabled($course->pre_sale)
                        class="px-12 py-3 rounded-full font-bold text-sm sm:text-xl shadow-md transition
            {{ !$course->pre_sale
                ? 'bg-[#2D9CDB] text-white hover:brightness-110 cursor-pointer'
                : 'bg-gray-400 text-white/70  opacity-60' }}">
                        Compra aquí
                    </button>

                    {{-- DESCARGA FICHA (sin cambios de lógica) --}}
                    <button type="button" wire:click="downloadFicha" wire:loading.attr="disabled"
                        wire:target="downloadFicha" @disabled(blank($course->ficha))
                        class="relative px-12 py-3 rounded-full text-white font-bold text-sm sm:text-xl shadow-md transition cursor-pointer
        {{ blank($course->ficha) ? 'bg-gray-400  opacity-60' : 'bg-[#F4A834] hover:brightness-110' }}">
                        <span wire:loading.remove wire:target="downloadFicha">
                            Descarga ficha
                        </span>

                        <span wire:loading wire:target="downloadFicha" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10"
                                    stroke="currentColor" stroke-width="4"></circle>
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
