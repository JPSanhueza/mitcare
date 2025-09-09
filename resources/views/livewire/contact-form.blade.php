<section id="contacto" class="py-6 md:py-8">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-12">

        {{-- Título con ícono --}}
        <div class="flex items-center gap-3 mb-6 md:mb-8">
            <img src="{{ asset('img/icons/contacto.png') }}" alt="" class="h-9 md:h-12 w-auto">
        </div>

        {{-- === TOAST (popup) === --}}
        <div x-data="{ open:false, type:'success', title:'', message:'' }" @toast.window="
        type   = $event.detail.type   || 'success';
        title  = $event.detail.title  || '¡Listo!';
        message= $event.detail.message|| '';
        open   = true; setTimeout(() => open=false, 4500)
      " class="fixed z-[60] right-6 bottom-6 md:top-6 md:bottom-auto">
            <div x-show="open" x-transition.duration.200ms
                class="flex items-start gap-3 rounded-xl shadow-xl px-4 py-3 text-white relative"
                :class="type === 'success' ? 'bg-[#47A8DF]' : 'bg-[#E71F6C]'">
                <svg x-show="type==='success'" class="h-6 w-6 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <path d="M22 4 12 14.01l-3-3" />
                </svg>
                <svg x-show="type!=='success'" class="h-6 w-6 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M12 9v4m0 4h.01" />
                    <path
                        d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                </svg>
                <div>
                    <p class="font-semibold" x-text="title"></p>
                    <p class="text-white/90" x-text="message"></p>
                </div>
                <button class="absolute top-2 right-2/3 md:right-2 text-white/80 hover:text-white"
                    @click="open=false">✕</button>
            </div>
        </div>
        {{-- === /TOAST === --}}

        @php
        $input = 'w-full rounded-xl bg-[#19355C] text-white placeholder-white/70
        px-4 md:px-5 py-3 md:py-4 outline-none focus:ring-2 focus:ring-white/60';
        $label = 'block text-[#19355C] font-semibold mb-2';
        $error = 'mt-2 text-sm text-[#E71F6C]';
        $btn = 'w-full inline-flex items-center justify-center gap-3 rounded-xl
        bg-[#E71F6C] hover:bg-[#c41659] text-white font-bold px-5 py-4
        transition shadow-md disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer';
        @endphp

        <form wire:submit.prevent="send" class="space-y-6">

            {{-- fila 1 --}}
            <div class="grid md:grid-cols-2 gap-6 ">
                <div>
                    <label for="nombre" class="{{ $label }}">Nombre completo</label>
                    <input id="nombre" type="text" wire:model.defer="nombre" class="{{ $input }}"
                        placeholder="Escribe tu nombre">
                    @error('nombre') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="empresa" class="{{ $label }}">Empresa</label>
                    <input id="empresa" type="text" wire:model.defer="empresa" class="{{ $input }}"
                        placeholder="Nombre de tu empresa">
                    @error('empresa') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- fila 2 --}}
            <div class="grid md:grid-cols-2 gap-6">
                <div>
                    <label for="email" class="{{ $label }}">Email</label>
                    <input id="email" type="email" wire:model.defer="email" class="{{ $input }}"
                        placeholder="tu@correo.com">
                    @error('email') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="telefono" class="{{ $label }}">Teléfono</label>
                    <input id="telefono" type="tel" wire:model.defer="telefono" class="{{ $input }}"
                        placeholder="+56 9 1234 5678">
                    @error('telefono') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- curso --}}
            <div>
                <label for="curso" class="{{ $label }}">Curso de interés</label>
                <input id="curso" type="text" wire:model.defer="curso" class="{{ $input }}"
                    placeholder="Nombre del curso">
                @error('curso') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            {{-- mensaje --}}
            <div>
                <label for="mensaje" class="{{ $label }}">Personaliza tu mensaje</label>
                <textarea id="mensaje" rows="6" wire:model.defer="mensaje" class="{{ $input }} resize-y"
                    placeholder="Cuéntanos en qué podemos ayudarte…"></textarea>
                @error('mensaje') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>

            {{-- honeypot --}}
            <div class="hidden">
                <label for="website">Website</label>
                <input id="website" type="text" wire:model="website" autocomplete="off">
            </div>

            {{-- botón --}}
            <button type="submit" class="{{ $btn }}" wire:loading.attr="disabled">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none"
                    stroke="currentColor" stroke-width="2">
                    <path d="M22 2L11 13" />
                    <path d="M22 2l-7 20-4-9-9-4 20-7z" />
                </svg>
                <span wire:loading.remove>Enviar solicitud</span>
                <span wire:loading>Enviando…</span>
            </button>
        </form>
    </div>
</section>
