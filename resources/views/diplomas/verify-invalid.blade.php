<x-layouts.app>

    <div class="max-w-5xl mx-auto px-4 py-10">
        {{-- Encabezado --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-[#19355C]">
                Verificación de certificado
            </h1>

            <p class="text-sm text-red-600 mt-1 font-semibold">
                No se ha encontrado ningún diploma asociado al código ingresado.
            </p>
        </div>

        {{-- Mensaje de error en tarjeta similar a las tuyas --}}
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-gray-600">
            <p class="mb-3">
                Código de verificación consultado:
            </p>

            <p class="font-mono text-sm bg-white inline-block px-3 py-1 rounded border border-gray-200 mb-4">
                {{ $code }}
            </p>

            <p class="text-sm mb-2">
                Por favor verifica que el código esté correctamente escrito o que el código QR
                haya sido escaneado sin errores.
            </p>

            @if (config('mail.from.address'))
                <p class="text-sm font-bold">
                    Si crees que se trata de un error, contacta al OTEC en
                    <a href="mailto:{{ config('mail.from.address') }}" class="text-[#19355C] underline">
                        {{ config('mail.from.address') }}
                    </a>.
                </p>
            @endif
        </div>
    </div>

</x-layouts.app>
