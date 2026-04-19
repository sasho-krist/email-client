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

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col font-sans text-gray-900 antialiased dark:bg-slate-950 dark:text-slate-100">
        <x-cookie-consent />

        @include('layouts.navigation')

        <div class="flex flex-1 flex-col bg-slate-100 bg-gradient-to-b pt-6 dark:from-slate-950 dark:via-slate-950 dark:to-blue-950/40 sm:justify-center sm:pt-0">
            <div class="flex flex-1 flex-col items-center px-6 sm:pt-0">
                <div class="mt-6 sm:mb-6">
                    <a href="{{ route('home') }}" class="block transition hover:opacity-90">
                        <x-application-logo class="mx-auto h-20 w-20 drop-shadow-lg" />
                    </a>
                </div>

                <div class="{{ ($wide ?? false) ? 'w-full max-w-5xl' : 'w-full sm:max-w-md' }} overflow-hidden rounded-xl border border-slate-200/90 bg-white shadow-lg ring-1 ring-slate-900/5 dark:border-blue-900/45 dark:bg-slate-900/90 dark:shadow-black/40 dark:ring-blue-950/30">
                    <div class="{{ ($wide ?? false) ? 'px-8 py-10 sm:px-12' : 'px-6 py-4' }}">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>

        <x-site-footer />
    </body>
</html>
