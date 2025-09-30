<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'OTEC MITCARE' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="//unpkg.com/alpinejs" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('img/layout/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('img/layout/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('img/layout/favicon-16x16.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="text-black">

    <livewire:cart-fab key="cart-fab" />

    {{-- Header con navegación --}}
    <x-layouts.header />
    <!-- Botón flotante de WhatsApp -->

    {{-- Contenido principal --}}
    <main class="bg-white">
        {{ $slot }}
    </main>
    <a href="https://wa.me/56965901501" target="_blank"
    class="fixed bottom-5 right-5 z-[9999] w-14 h-14 bg-green-500 rounded-full flex items-center justify-center shadow-lg hover:bg-green-600 transition-colors">
    <img src="https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg" 
        alt="WhatsApp" 
        class="w-7 h-7">
    </a>
    {{-- Footer --}}
    <x-footer />

</body>

</html>
