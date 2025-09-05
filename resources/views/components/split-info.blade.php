@props([
    'image' => 'img/fondos/placeholder.jpg',
    'imageAlt' => '',
    'imagePosition' => 'left', // left | right
    'color' => '#31A9E1',
    'objectPos' => '50% 50%',  // usa formato CSS normal
    'fixedHeight' => 'md:min-h-[650px]', // altura uniforme solo en md+
    'icon' => null,
    'iconClass' => 'h-16 md:h-20 lg:h-24 w-auto',
    'titleHtml' => 'Título',
    'textHtml' => 'Contenido',
    'buttonLabel' => 'Ver cursos',
    'buttonHref' => '#',
    'showButton' => true,
])

@php
    $imgLeft = ($imagePosition === 'left');
    $btn = 'inline-flex items-center rounded-md px-6 py-3 font-bold text-white
            bg-[#ff2d7a] hover:bg-[#e82670] transition shadow-md
            focus:outline-none focus:ring-2 focus:ring-white/60';

    // Orden en desktop (md+). En mobile: texto primero, imagen abajo.
    $imgMdOrder   = $imgLeft ? 'md:order-1' : 'md:order-2';
    $panelMdOrder = $imgLeft ? 'md:order-2' : 'md:order-1';
@endphp

<section class="w-full text-white">
    <div class="grid md:grid-cols-2 {{ $fixedHeight }}">
        {{-- PANEL (arriba en mobile) --}}
        <div class="order-1 {{ $panelMdOrder }}" style="background-color: {{ $color }};">
            <div class="max-w-3xl mx-auto px-3 sm:px-12 lg:px-20 py-12 md:py-16">
                @if($icon)
                    <img src="{{ asset($icon) }}" alt="" class="{{ $iconClass }} mb-4 opacity-95" loading="lazy">
                @endif

                <h2 class="font-extrabold leading-tight text-[25px] sm:text-4xl md:text-5xl mb-5">{!! $titleHtml !!}</h2>

                <div class="text-white/95 text-lg md:text-xl leading-relaxed text-justify [hyphens:auto]">
                    {!! $textHtml !!}
                </div>

                {{-- botón mobile --}}
                @if($showButton && filled($buttonLabel))
                    <div class="mt-8 md:hidden">
                        <a href="{{ $buttonHref ?: '#' }}" class="{{ $btn }}">{{ $buttonLabel }}</a>
                    </div>
                @endif
            </div>
        </div>

        {{-- IMAGEN (abajo en mobile, columna en md+) --}}
        <div class="relative order-2 {{ $imgMdOrder }} overflow-hidden">
            <img src="{{ asset($image) }}" alt="{{ $imageAlt }}"
                 class="w-full h-64 sm:h-80 object-cover md:absolute md:inset-0 md:h-full"
                 style="object-position: {{ $objectPos }};">

            {{-- botón sobre la imagen solo en desktop --}}
            @if($showButton && filled($buttonLabel))
                <div class="hidden md:block absolute left-8 bottom-8">
                    <a href="{{ $buttonHref ?: '#' }}" class="{{ $btn }}">{{ $buttonLabel }}</a>
                </div>
            @endif
        </div>
    </div>
</section>
