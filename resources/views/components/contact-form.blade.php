{{-- resources/views/components/contacto.blade.php (o directo en tu view) --}}
<section id="contacto" class="py-12 md:py-16">
  <div class="max-w-7xl mx-auto px-6 lg:px-10">

    {{-- Título con ícono --}}
    <div class="flex items-center gap-3 mb-6 md:mb-8">
      <img src="{{ asset('img/icons/contacto.png') }}" alt="" class="h-9 md:h-12 w-auto">
    </div>

    {{-- Mensajes flash (opcional) --}}
    @if(session('ok'))
      <div class="mb-6 rounded-lg bg-emerald-600/10 text-emerald-800 px-4 py-3">
        {{ session('ok') }}
      </div>
    @endif

    @php
      $input = 'w-full rounded-xl bg-[#0e3252] text-white placeholder-white/70
                px-4 md:px-5 py-3 md:py-4 outline-none focus:ring-2 focus:ring-white/60';
      $label = 'block text-[#0e3252] font-semibold mb-2';
      $error = 'mt-2 text-sm text-rose-500';
    @endphp

    <form method="POST" action="{{ route('contact.send') }}" class="space-y-6">
      @csrf

      {{-- fila 1 --}}
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <label for="nombre" class="{{ $label }}">Nombre completo</label>
          <input id="nombre" name="nombre" type="text" required
                 value="{{ old('nombre') }}" placeholder="Escribe tu nombre"
                 class="{{ $input }}">
          @error('nombre') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="empresa" class="{{ $label }}">Empresa</label>
          <input id="empresa" name="empresa" type="text"
                 value="{{ old('empresa') }}" placeholder="Opcional"
                 class="{{ $input }}">
          @error('empresa') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- fila 2 --}}
      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <label for="email" class="{{ $label }}">Email</label>
          <input id="email" name="email" type="email" required
                 value="{{ old('email') }}" placeholder="tu@correo.com"
                 class="{{ $input }}">
          @error('email') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div>
          <label for="telefono" class="{{ $label }}">Teléfono</label>
          <input id="telefono" name="telefono" type="tel"
                 value="{{ old('telefono') }}" placeholder="+56 9 1234 5678"
                 class="{{ $input }}">
          @error('telefono') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>
      </div>

      {{-- curso de interés --}}
      <div>
        <label for="curso" class="{{ $label }}">Curso de interés</label>
        <input id="curso" name="curso" type="text"
               value="{{ old('curso') }}" placeholder="Nombre del curso"
               class="{{ $input }}">
        @error('curso') <p class="{{ $error }}">{{ $message }}</p> @enderror
      </div>

      {{-- mensaje --}}
      <div>
        <label for="mensaje" class="{{ $label }}">Personaliza tu mensaje</label>
        <textarea id="mensaje" name="mensaje" rows="6" required
                  placeholder="Cuéntanos en qué podemos ayudarte…"
                  class="{{ $input }} resize-y"></textarea>
        @error('mensaje') <p class="{{ $error }}">{{ $message }}</p> @enderror
      </div>

      {{-- honeypot simple anti-spam (opcional) --}}
      <div class="hidden">
        <label for="website">Website</label>
        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
      </div>

      {{-- botón --}}
      <button type="submit"
              class="w-full inline-flex items-center justify-center gap-3 rounded-xl
                     bg-[#ff2d7a] hover:bg-[#e82670] text-white font-bold
                     px-5 py-4 transition shadow-md">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M22 2L11 13"/>
          <path d="M22 2l-7 20-4-9-9-4 20-7z"/>
        </svg>
        Enviar solicitud
      </button>
    </form>
  </div>
</section>
