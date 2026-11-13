<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MacroMeals') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    {{-- simple home page for guests, logged in users get sent to the dashboard instead --}}
    <body class="font-sans antialiased bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
        <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:rounded focus:bg-white focus:px-4 focus:py-2 focus:text-gray-900">Skip to main content</a>

        <main id="main" class="flex min-h-screen flex-col items-center justify-center px-6 text-center">
            <x-application-logo class="h-16 w-16 fill-current text-emerald-700 dark:text-emerald-400" />

            <h1 class="mt-4 text-3xl font-semibold">MacroMeals</h1>

            <p class="mt-3 max-w-md text-gray-700 dark:text-gray-300">
                Gym-friendly recipes with the macros worked out. Share your own and see what other people are cooking.
            </p>

            <div class="mt-6 flex gap-3">
                <a href="{{ route('login') }}" class="rounded-md bg-emerald-700 px-5 py-2 font-semibold text-white hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">Log in</a>
                <a href="{{ route('register') }}" class="rounded-md border border-gray-300 bg-white px-5 py-2 font-semibold text-gray-900 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100">Register</a>
            </div>

            <p class="mt-12 text-sm text-gray-600 dark:text-gray-400">
                CSC348 coursework project. Food data from Open Food Facts.
            </p>
        </main>
    </body>
</html>
