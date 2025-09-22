<section>
    <div class="grid grid-cols-1 lg:grid-cols-2 overflow-hidden shadow-lg py-1 bg-[#47A8DF]">

        {{-- LADO IZQUIERDO: bloque sólido con info principal --}}
        <div class="bg-[#19355C] text-white p-8 md:p-12 flex flex-col justify-start">
            <div>
                <h1 class="text-3xl md:text-5xl font-extrabold leading-tight">
                    {{ $course->nombre }}
                </h1>

                @if($course->subtitulo)
                <p class="mt-6 text-white/90 text-base md:text-lg max-w-prose">
                    {!! $course->subtitulo !!}
                </p>
                @endif
            </div>

            <div class="mt-10 flex items-center gap-4 flex-wrap">
                {{-- Precio como chip --}}
                <span
                    class="inline-flex items-center px-5 py-3 rounded-full bg-[#ff0b78] text-white text-xl font-bold shadow">
                    {{ '$' . number_format($course->price, 0, ',', '.') }}
                </span>

                {{-- CTA --}}
                <button wire:click="addToCart"
                    class="inline-flex items-center px-6 py-3 rounded-full bg-[#41a8d8] text-white font-bold hover:brightness-110 transition">
                    Agregar al carrito
                </button>
            </div>
        </div>

        {{-- LADO DERECHO: imagen de fondo con overlay y texto --}}
        <div class="relative flex items-center justify-center bg-[#0e3654]">
            <div class="w-full aspect-square relative">
                {{-- Imagen cuadrada --}}
                <img src="{{ $imageUrl }}" alt="{{ $course->nombre }}" class="w-full h-full object-cover rounded-none">

                {{-- Overlay --}}
                <div class="absolute inset-0 bg-[#19355C]/60"></div>

                {{-- Texto encima --}}
                <div class="absolute inset-0 flex flex-col p-10 md:p-22 text-xl gap-10 text-white">
                    @if($course->descripcion)
                    <p class="text-white/90">
                        {!! $course->descripcion !!}
                    </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
