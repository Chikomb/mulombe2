<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>AKROS | Survey</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
<div class="bg-bg_light min-h-screen flex items-center justify-center px-16">
    <div class="relative w-full max-w-lg">
        <div class="absolute top-0 -left-4 w-72 h-72 bg-green-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute top-0 -right-4 w-72 h-72 bg-yellow-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-green-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
        <div class="relative space-y-4 flex flex-col items-center justify-center">
            <div class="w-full lg:px-10"> <div class="flex flex-col items-center justify-center">
                    <a href="/">
                        <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                    </a>
                </div>
                <div class="text-sm text-center">
                    Examining Digital Tools to Collect Community-level Behavioral and Social Drivers (BeSD) Data: A feasibility pilot in Zambia
                </div>

                <div class="w-full mt-6 px-6 py-8 bg-gray-50 shadow-md overflow-hidden sm:rounded-lg">
                    {{ $slot }}
                </div></div>
        </div>
    </div>
</div>
</body>
</html>
