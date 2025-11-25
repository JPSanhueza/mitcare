<x-layouts.app>

    <div class="min-h-[60vh] flex items-center justify-center bg-slate-50 py-10">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-[#19355C] mb-6 text-center">
                Acceso a certificados
            </h1>

            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-2 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-2 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('student.login.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        RUT
                    </label>
                    <input type="text" name="rut" value="{{ old('rut') }}" placeholder="12.345.678-5"
                        class="w-full rounded-lg border-gray-300 focus:border-[#19355C] focus:ring-[#19355C]" required>
                    @error('rut')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-data="{ show: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña
                    </label>

                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password"
                            class="w-full rounded-lg border-gray-300 focus:border-[#19355C] focus:ring-[#19355C] pr-10"
                            required>

                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-sm text-gray-500 hover:text-gray-700">
                            <span x-show="!show">Ver</span>
                            <span x-show="show">Ocultar</span>
                        </button>
                    </div>

                    <p class="mt-1 text-xs text-gray-500">
                        Recuerda: 6 primeros dígitos de tu RUT (sin puntos ni guion) + 2 primeras letras de tu nombre.
                    </p>

                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>


                <button type="submit"
                    class="w-full inline-flex justify-center rounded-xl px-4 py-2.5 bg-[#19355C] text-white font-semibold hover:bg-[#142843] transition">
                    Iniciar sesión
                </button>
            </form>

            <div class="mt-4 text-center">
                <a href="{{ route('student.password.request') }}" class="text-sm text-[#19355C] hover:underline">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </div>
    </div>

</x-layouts.app>
