<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Recipes
            </h2>
            <a href="{{ route('recipes.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                Post a recipe
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($recipes->isEmpty())
                <p class="text-gray-700 dark:text-gray-300">No recipes yet. Be the first to post one!</p>
            @else
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($recipes as $recipe)
                        <article class="flex flex-col rounded-lg bg-white p-5 shadow-sm dark:bg-gray-800">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                <a href="{{ route('recipes.show', $recipe) }}" class="hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">
                                    {{ $recipe->title }}
                                </a>
                            </h3>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                by {{ $recipe->user->name }} · {{ $recipe->created_at->diffForHumans() }}
                            </p>

                            <p class="mt-3 text-sm text-gray-700 dark:text-gray-300 line-clamp-2">
                                {{ $recipe->description }}
                            </p>

                            <x-macro-badges :recipe="$recipe" class="mt-4" />

                            @if ($recipe->tags->isNotEmpty())
                                <ul class="mt-3 flex flex-wrap gap-1.5 text-xs" aria-label="Tags">
                                    @foreach ($recipe->tags as $tag)
                                        <li class="rounded bg-gray-100 px-2 py-0.5 text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $tag->name }}</li>
                                    @endforeach
                                </ul>
                            @endif

                            {{-- mt-auto pushes this to the bottom so the cards line up --}}
                            <p class="mt-auto pt-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $recipe->prep_minutes }} mins
                                · {{ $recipe->comments_count }} {{ Str::plural('comment', $recipe->comments_count) }}
                                · {{ $recipe->likes_count }} {{ Str::plural('like', $recipe->likes_count) }}
                            </p>
                        </article>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $recipes->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
