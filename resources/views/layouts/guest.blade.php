<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title.' - ' : '' }}{{ config('app.name', 'MacroMeals') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">
            <header>
                <a href="/" wire:navigate class="flex flex-col items-center gap-2 rounded focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <x-application-logo class="w-16 h-16 fill-current text-emerald-700 dark:text-emerald-400" />
                    <span class="text-xl font-bold text-gray-900 dark:text-gray-100">MacroMeals</span>
                </a>
            </header>

            {{-- main landmark and a heading that says which page this is (set with #[Title] on each auth page) --}}
            <main class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                <h1 class="mb-4 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $title ?? 'MacroMeals' }}</h1>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
