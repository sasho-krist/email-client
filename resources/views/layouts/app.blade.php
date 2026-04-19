<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@include('partials.document-title')</title>
        <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml" sizes="any">
        <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @stack('styles')

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col font-sans antialiased text-gray-900 dark:text-slate-100">
        <x-cookie-consent />

        <div class="flex min-h-screen flex-1 flex-col bg-slate-100 bg-gradient-to-b dark:from-slate-950 dark:via-slate-950 dark:to-blue-950/35">
            @include('layouts.navigation')

            @isset($header)
                <header class="border-b border-slate-200/80 bg-white/90 shadow-sm backdrop-blur dark:border-blue-950/40 dark:bg-slate-900/85">
                    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main class="flex-1">
                {{ $slot }}
            </main>

            <x-site-footer />
        </div>

        @stack('scripts')
    </body>
</html>
