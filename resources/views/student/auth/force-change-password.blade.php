<x-layouts.app>

    <div class="min-h-[60vh] flex items-center justify-center bg-slate-50 py-10">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-[#19355C] mb-2 text-center">
                Cambia tu contraseña
            </h1>

            <p class="mb-6 text-sm text-gray-600 text-center">
                Hola {{ $student->nombre }} {{ $student->apellido }}.<br>
                Por seguridad, debes definir una nueva contraseña personal antes de continuar.
            </p>

            @if (session('success'))
                <div class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-2 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('student.password.force.submit') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nueva contraseña
                    </label>
                    <input
                        type="password"
                        name="new_password"
                        class="w-full rounded-lg border-gray-300 focus:border-[#19355C] focus:ring-[#19355C]"
                        required
                    >
                    @error('new_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar nueva contraseña
                    </label>
                    <input
                        type="password"
                        name="new_password_confirmation"
                        class="w-full rounded-lg border-gray-300 focus:border-[#19355C] focus:ring-[#19355C]"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex justify-center rounded-xl px-4 py-2.5 bg-[#19355C] text-white font-semibold hover:bg-[#142843] transition"
                >
                    Guardar y continuar
                </button>
            </form>
        </div>
    </div>

</x-layouts.app>
