<x-layouts.app>

    <div class="min-h-[60vh] flex items-center justify-center bg-slate-50 py-10">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8">
            <h1 class="text-2xl font-bold text-[#19355C] mb-6 text-center">
                Recuperar contraseña
            </h1>

            <form method="POST" action="{{ route('student.password.reset') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        RUT
                    </label>
                    <input type="text" name="rut" value="{{ old('rut') }}" placeholder="12.345.678-5"
                        class="w-full h-11 px-3 rounded-lg border border-gray-300 focus:border-[#19355C] focus:ring-[#19355C] text-gray-800"
                        required>
                    @error('rut')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nueva contraseña
                    </label>
                    <input type="password" name="new_password"
                        class="w-full h-11 px-3 rounded-lg border border-gray-300 focus:border-[#19355C] focus:ring-[#19355C] text-gray-800"
                        required>
                    @error('new_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmar nueva contraseña
                    </label>
                    <input type="password" name="new_password_confirmation"
                        class="w-full h-11 px-3 rounded-lg border border-gray-300 focus:border-[#19355C] focus:ring-[#19355C] text-gray-800"
                        required>
                </div>

                <button type="submit"
                    class="w-full inline-flex justify-center rounded-xl px-4 py-2.5 bg-[#19355C] text-white font-semibold hover:bg-[#142843] transition">
                    Actualizar contraseña
                </button>
            </form>
        </div>
    </div>

</x-layouts.app>
