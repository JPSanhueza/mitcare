<x-layouts.app>

    <div class="min-h-[60vh] flex items-center justify-center bg-slate-50 py-10">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6 sm:p-8 text-center">
            <h1 class="text-2xl font-bold text-[#19355C] mb-4">
                Enlace no válido o expirado
            </h1>

            <p class="text-sm text-gray-600 mb-4">
                El enlace que utilizaste para definir tu contraseña ya no es válido.
                Puedes solicitar uno nuevo ingresando tu correo.
            </p>

            <a href="{{ route('student.password.forgot') }}"
                class="inline-flex items-center justify-center px-4 py-2.5 bg-[#19355C] text-white rounded-xl text-sm font-semibold hover:bg-[#142843] transition">
                Solicitar nuevo enlace
            </a>
        </div>
    </div>

</x-layouts.app>
