@props([
'image' => 'img/fondos/placeholder.jpg',
'imageAlt' => '',
'imagePosition' => 'left', // left | right
'color' => '#31A9E1',
'objectPos' => '[50%_50%]', // foco de la foto: 'object-left', 'object-right', o arbitrario: [70%_50%]
'fixedHeight' => 'min-h-[520px] md:min-h-[650px]', // altura uniforme
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
@endphp

<section class="w-full text-white">
    <div class="grid md:grid-cols-2 {{ $fixedHeight }}">
        {{-- IMAGEN --}}
        <div class="relative order-1 {{ $imgLeft ? 'md:order-1' : 'md:order-2' }} overflow-hidden">
            <img src="{{ asset($image) }}" alt="{{ $imageAlt }}" class="absolute inset-0 w-full h-full object-cover"
                style="object-position: {{ $objectPos }};">

            {{-- botón --}}
            @if($showButton && filled($buttonLabel))
            <div class="hidden md:block absolute left-8 bottom-8">
                <a href="{{ $buttonHref ?: '#' }}" class="{{ $btn }}">{{ $buttonLabel }}</a>
            </div>
            @endif
        </div>

        {{-- PANEL --}}
        <div class="relative order-2 {{ $imgLeft ? 'md:order-2' : 'md:order-1' }}"
            style="background-color: {{ $color }};">
            <div class="max-w-3xl mx-auto px-6 sm:px-12 lg:px-20 py-12 md:py-16">
                @if($icon)
                <img src="{{ asset($icon) }}" alt="" class="{{ $iconClass }} mb-4 opacity-95" loading="lazy">
                @endif

                <h2 class="font-extrabold leading-tight text-4xl md:text-5xl mb-5">{!! $titleHtml !!}</h2>

                <div class="text-white/95 text-lg md:text-xl
                leading-relaxed text-justify [hyphens:auto]">
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
    </div>
</section>
