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

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-2 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('student.login.submit') }}" class="space-y-4">
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

                <div x-data="{ show: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña
                    </label>

                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="password"
                            class="w-full h-11 px-3 rounded-lg border border-gray-300 focus:border-[#19355C] focus:ring-[#19355C] text-gray-800"
                            required>

                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-sm text-gray-500 hover:text-gray-700">
                            <span x-show="!show">Ver</span>
                            <span x-show="show">Ocultar</span>
                        </button>
                    </div>

                    <p class="mt-1 text-xs text-gray-500">
                        Ingresa la contraseña que definiste en el enlace recibido por correo.
                        Si no la recuerdas, usa la opción “¿Olvidaste tu contraseña?”.
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
                <a href="{{ route('student.password.forgot') }}" class="text-sm text-[#19355C] hover:underline">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </div>
    </div>

</x-layouts.app>
