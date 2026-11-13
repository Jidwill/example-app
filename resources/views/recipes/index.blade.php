<x-app-layout>
    <x-slot name="title">Recipes</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Recipes
            </h1>
            @can('create', App\Models\Recipe::class)
                <a href="{{ route('recipes.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-700 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                    Post a recipe
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @php($filtering = collect($filters)->except('sort')->filter()->isNotEmpty())

            {{-- GET form, so the filters end up in the URL and can be bookmarked or shared --}}
            <form method="GET" action="{{ route('recipes.index') }}" role="search" aria-label="Filter recipes"
                class="mb-6 grid gap-4 rounded-lg bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5 lg:items-end dark:bg-gray-800">
                <div>
                    <x-input-label for="filter-tag" value="Tag" />
                    <select id="filter-tag" name="tag"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="">All tags</option>
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->id }}" @selected(($filters['tag'] ?? null) == $tag->id)>{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <x-input-label for="filter-min-protein" value="Min protein (g)" />
                    <x-text-input id="filter-min-protein" name="min_protein" type="number" min="0" max="500" placeholder="e.g. 30"
                        class="mt-1 block w-full" :value="$filters['min_protein'] ?? ''" />
                </div>

                <div>
                    <x-input-label for="filter-max-calories" value="Max calories (kcal)" />
                    <x-text-input id="filter-max-calories" name="max_calories" type="number" min="50" max="5000" placeholder="e.g. 600"
                        class="mt-1 block w-full" :value="$filters['max_calories'] ?? ''" />
                </div>

                <div>
                    <x-input-label for="filter-sort" value="Sort by" />
                    <select id="filter-sort" name="sort"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                        <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>Newest</option>
                        <option value="protein" @selected(($filters['sort'] ?? null) === 'protein')>Most protein</option>
                        <option value="lean" @selected(($filters['sort'] ?? null) === 'lean')>Most protein per calorie</option>
                    </select>
                </div>

                <div class="flex items-center gap-3">
                    <x-primary-button>Apply</x-primary-button>
                    @if ($filtering || ($filters['sort'] ?? 'newest') !== 'newest')
                        <a href="{{ route('recipes.index') }}" class="text-sm text-gray-700 hover:underline dark:text-gray-300">Clear</a>
                    @endif
                </div>
            </form>

            {{-- role="status" so screen readers hear the new count after filtering --}}
            <p role="status" class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                {{ $recipes->total() }} {{ Str::plural('recipe', $recipes->total()) }}{{ $filtering ? ' match your filters' : '' }}
            </p>

            @if ($recipes->isEmpty())
                <p class="text-gray-700 dark:text-gray-300">
                    @if ($filtering)
                        Nothing matches those filters. Try a lower protein or higher calorie limit, or <a href="{{ route('recipes.index') }}" class="text-emerald-700 underline dark:text-emerald-300">clear them</a>.
                    @else
                        No recipes yet. Be the first to post one!
                    @endif
                </p>
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
