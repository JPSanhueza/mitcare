<x-layouts.app>

    <div class="min-h-[60vh] flex items-center justify-center bg-slate-50 py-10">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-[#19355C] mb-6 text-center">
                Define tu contraseña
            </h1>

            <p class="text-sm text-gray-600 mb-4">
                Crea una contraseña para acceder a tus certificados.
            </p>

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-100 text-red-800 px-4 py-2 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('student.password.update') }}" class="space-y-4">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Correo
                    </label>
                    <input type="email" value="{{ $email }}" disabled
                        class="w-full h-11 px-3 rounded-lg border border-gray-200 bg-gray-50 text-gray-500">
                </div>

                <div x-data="{ show: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nueva contraseña
                    </label>

                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" name="new_password"
                            class="w-full h-11 px-3 rounded-lg border border-gray-300 focus:border-[#19355C] focus:ring-[#19355C] text-gray-800"
                            required>

                        <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-sm text-gray-500 hover:text-gray-700">
                            <span x-show="!show">Ver</span>
                            <span x-show="show">Ocultar</span>
                        </button>
                    </div>
                </div>


                <div x-data="{ showConfirm: false }">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar contraseña
                    </label>

                    <div class="relative">
                        <input :type="showConfirm ? 'text' : 'password'" name="new_password_confirmation"
                            class="w-full h-11 px-3 rounded-lg border border-gray-300 focus:border-[#19355C] focus:ring-[#19355C] text-gray-800"
                            required>

                        <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute inset-y-0 right-0 px-3 flex items-center text-sm text-gray-500 hover:text-gray-700">
                            <span x-show="!showConfirm">Ver</span>
                            <span x-show="showConfirm">Ocultar</span>
                        </button>
                    </div>
                </div>
                

                <button type="submit"
                    class="w-full inline-flex justify-center rounded-xl px-4 py-2.5 bg-[#19355C] text-white font-semibold hover:bg-[#142843] transition">
                    Guardar contraseña
                </button>
            </form>
        </div>
    </div>

</x-layouts.app>
