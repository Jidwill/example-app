<x-app-layout>
    <x-slot name="title">Home</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Welcome back, {{ $user->name }}
            </h1>
            <a href="{{ route('recipes.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                Post a recipe
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            {{-- quick summary - each box links to where you'd go next --}}
            <section aria-labelledby="summary-heading">
                <h2 id="summary-heading" class="sr-only">Your summary</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <a href="{{ route('users.show', $user) }}" class="rounded-lg bg-white p-5 shadow-sm hover:ring-2 hover:ring-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Your recipes and comments</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $user->recipes_count }} · {{ $user->comments_count }}</p>
                        <p class="mt-1 text-sm text-indigo-700 dark:text-indigo-300">See your page</p>
                    </a>

                    <a href="{{ route('notifications.index') }}" class="rounded-lg bg-white p-5 shadow-sm hover:ring-2 hover:ring-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Unread notifications</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $unread }}</p>
                        <p class="mt-1 text-sm text-indigo-700 dark:text-indigo-300">Open notifications</p>
                    </a>

                    <a href="{{ route('profile') }}" class="rounded-lg bg-white p-5 shadow-sm hover:ring-2 hover:ring-indigo-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800">
                        <p class="text-sm text-gray-600 dark:text-gray-400">Your daily targets</p>
                        @if ($user->profile)
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $user->profile->daily_protein }}g · {{ number_format($user->profile->daily_calories) }} kcal</p>
                            <p class="mt-1 text-sm text-indigo-700 dark:text-indigo-300">{{ ucfirst($user->profile->goal) }} goal - change it</p>
                        @else
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">Not set</p>
                            <p class="mt-1 text-sm text-indigo-700 dark:text-indigo-300">Set your goal</p>
                        @endif
                    </a>
                </div>
            </section>

            <section aria-labelledby="latest-heading">
                <div class="flex items-center justify-between gap-4">
                    <h2 id="latest-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Latest recipes</h2>
                    <a href="{{ route('recipes.index') }}" class="text-sm font-medium text-indigo-700 hover:underline dark:text-indigo-300">Browse all recipes</a>
                </div>

                @if ($latest->isEmpty())
                    <p class="mt-2 text-gray-600 dark:text-gray-400">No recipes yet.</p>
                @else
                    <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($latest as $recipe)
                            <x-recipe-card :recipe="$recipe" :heading-level="3" />
                        @endforeach
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
