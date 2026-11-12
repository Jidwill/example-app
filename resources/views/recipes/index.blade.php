<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Recipes
            </h2>
            @can('create', App\Models\Recipe::class)
                <a href="{{ route('recipes.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    Post a recipe
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($recipes->isEmpty())
                <p class="text-gray-700 dark:text-gray-300">No recipes yet. Be the first to post one!</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($recipes as $recipe)
                        <x-recipe-card :recipe="$recipe" />
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $recipes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
