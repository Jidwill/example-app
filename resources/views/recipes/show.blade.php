<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $recipe->title }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Posted by {{ $recipe->user->name }} · {{ $recipe->created_at->diffForHumans() }}
        </p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <a href="{{ route('recipes.index') }}" class="text-sm text-indigo-700 hover:underline dark:text-indigo-300">&larr; Back to all recipes</a>

            <section class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                <p class="text-gray-800 dark:text-gray-200">{{ $recipe->description }}</p>

                <x-macro-badges :recipe="$recipe" class="mt-4" />

                <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                    {{ $recipe->prep_minutes }} mins
                    · serves {{ $recipe->servings }}
                    · {{ $recipe->likes_count }} {{ Str::plural('like', $recipe->likes_count) }}
                </p>

                @if ($recipe->tags->isNotEmpty())
                    <ul class="mt-3 flex flex-wrap gap-1.5 text-xs" aria-label="Tags">
                        @foreach ($recipe->tags as $tag)
                            <li class="rounded bg-gray-100 px-2 py-0.5 text-gray-700 dark:bg-gray-700 dark:text-gray-200">{{ $tag->name }}</li>
                        @endforeach
                    </ul>
                @endif
            </section>

            <div class="grid gap-6 md:grid-cols-2">
                <section class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Ingredients</h3>
                    {{-- ingredients and method are saved one per line, so split them into list items --}}
                    <ul class="mt-3 list-disc ps-5 space-y-1 text-gray-800 dark:text-gray-200">
                        @foreach (preg_split('/\R/', $recipe->ingredients, -1, PREG_SPLIT_NO_EMPTY) as $ingredient)
                            <li>{{ $ingredient }}</li>
                        @endforeach
                    </ul>
                </section>

                <section class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Method</h3>
                    <ol class="mt-3 list-decimal ps-5 space-y-2 text-gray-800 dark:text-gray-200">
                        @foreach (preg_split('/\R/', $recipe->method, -1, PREG_SPLIT_NO_EMPTY) as $step)
                            <li>{{ $step }}</li>
                        @endforeach
                    </ol>
                </section>
            </div>

            <section class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Comments ({{ $recipe->comments->count() }})
                </h3>

                {{-- posting comments gets added later as a Livewire component --}}
                @forelse ($recipe->comments as $comment)
                    <article class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $comment->user->name }}</span>
                            · {{ $comment->created_at->diffForHumans() }}
                            · {{ $comment->likes_count }} {{ Str::plural('like', $comment->likes_count) }}
                        </p>
                        <p class="mt-1 text-gray-800 dark:text-gray-200">{{ $comment->body }}</p>
                    </article>
                @empty
                    <p class="mt-3 text-gray-600 dark:text-gray-400">No comments yet.</p>
                @endforelse
            </section>
        </div>
    </div>
</x-app-layout>
