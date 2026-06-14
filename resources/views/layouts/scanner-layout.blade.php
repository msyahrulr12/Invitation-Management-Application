<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ $title ?? config('app.name') . ' — Scanner' }}</title>

    <script src="{{ asset('js/html5-qrcode.min.js') }}" type="text/javascript"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

    <style>
        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-950 text-white min-h-screen">
    {{ $slot }}

    @livewireScripts
</body>

</html>