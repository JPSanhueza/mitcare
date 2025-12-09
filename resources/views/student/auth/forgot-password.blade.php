<x-layouts.app>

    <div class="min-h-[60vh] flex items-center justify-center bg-slate-50 py-10">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-[#19355C] mb-6 text-center">
                Recuperar acceso
            </h1>

            <p class="text-sm text-gray-600 mb-4">
                Ingresa el correo con el que fuiste inscrito. Si existe en el sistema,
                te enviaremos un enlace para definir una nueva contraseña.
            </p>

            @if (session('status'))
                <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-2 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-2 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('student.password.send-link') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="tu-correo@ejemplo.cl"
                        class="w-full h-11 px-3 rounded-lg border border-gray-300 focus:border-[#19355C] focus:ring-[#19355C] text-gray-800"
                        required>
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full inline-flex justify-center rounded-xl px-4 py-2.5 bg-[#19355C] text-white font-semibold hover:bg-[#142843] transition">
                    Enviar enlace
                </button>

                <div class="mt-4 text-center">
                    <a href="{{ route('student.login') }}" class="text-sm text-[#19355C] hover:underline">
                        Volver al inicio de sesión
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
