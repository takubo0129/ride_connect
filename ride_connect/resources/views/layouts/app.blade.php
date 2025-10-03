<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900">
        <div class="relative flex min-h-screen flex-col">
            @include('layouts.navigation')

            @isset($header)
                <header class="relative z-30 border-b border-slate-200/70 bg-white/80 backdrop-blur">
                    <div class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-8 sm:px-6 md:px-8 lg:px-12">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="relative z-20 flex-1">
                {{ $slot }}
            </main>
        </div>

        @stack('scripts')
    </body>
</html>
