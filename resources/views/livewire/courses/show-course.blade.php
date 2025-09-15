<section class="max-w-6xl mx-auto px-4 py-10 grid grid-cols-1 lg:grid-cols-2 gap-10">
    {{-- Imagen --}}
    <div>
        <div class="aspect-square w-full overflow-hidden rounded-xl shadow">
            <img src="{{ $imageUrl }}" alt="{{ $course->nombre }}" class="w-full h-full object-cover">
        </div>
    </div>

    {{-- Datos --}}
    <div>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0e3654]">{{ $course->nombre }}</h1>
        <p class="mt-3 text-[#0e3654]/80 ">{!! $course->descripcion !!}</p>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div class="p-4 rounded-lg bg-slate-50">
                <div class="font-semibold text-slate-700">Modalidad</div>
                <div class="capitalize">{{ $course->modality }}</div>
            </div>

            @if($course->start_at)
            <div class="p-4 rounded-lg bg-slate-50">
                <div class="font-semibold text-slate-700">Inicio</div>
                <div>{{ $course->start_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif

            @if($course->end_at)
            <div class="p-4 rounded-lg bg-slate-50">
                <div class="font-semibold text-slate-700">Término</div>
                <div>{{ $course->end_at->format('d/m/Y H:i') }}</div>
            </div>
            @endif

            @if($course->location && in_array($course->modality, ['presencial','mixto']))
            <div class="p-4 rounded-lg bg-slate-50">
                <div class="font-semibold text-slate-700">Ubicación</div>
                <div>{{ $course->location }}</div>
            </div>
            @endif
        </div>

        {{-- Precio y CTA --}}
        <div class="mt-8 flex items-end justify-between gap-6">
            <div>
                <div class="text-sm text-slate-500">Precio</div>
                <div class="text-3xl font-extrabold text-[#0e3654]">
                    {{ '$' . number_format($course->price, 0, ',', '.') }} CLP
                </div>
                @if($course->capacity)
                    <div class="mt-1 text-xs text-slate-500">Cupos: {{ $course->capacity }}</div>
                @endif
            </div>

            <button
                wire:click="startCheckout"
                class="inline-flex items-center justify-center px-6 py-3 rounded-lg bg-[#ff0b78] text-white font-bold hover:brightness-110 transition"
            >
                Comprar
            </button>
        </div>

        {{-- Enlaces externos opcionales --}}
        @if($course->external_url)
            <div class="mt-6">
                <a href="{{ $course->external_url }}" target="_blank" class="text-sm text-blue-700 underline">
                    Más información externa
                </a>
            </div>
        @endif
    </div>
</section>
