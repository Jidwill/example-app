<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ $user->name }}<x-role-badge :user="$user" />
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Joined {{ $user->created_at->format('F Y') }}
                    · {{ $user->recipes_count }} {{ Str::plural('recipe', $user->recipes_count) }}
                    · {{ $user->comments_count }} {{ Str::plural('comment', $user->comments_count) }}
                </p>
            </div>

            {{-- your own page links to where you can change your goal and targets --}}
            @if (auth()->id() === $user->id)
                <a href="{{ route('profile') }}" class="text-sm font-medium text-indigo-700 hover:underline dark:text-indigo-300">Edit my goal and targets</a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            {{-- profile (one to one with users) - older accounts might not have one --}}
            <section class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800" aria-labelledby="profile-heading">
                <h3 id="profile-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Training goal</h3>

                @if ($user->profile)
                    <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Goal</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst($user->profile->goal) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Daily calories</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format($user->profile->daily_calories) }} kcal</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-600 dark:text-gray-400">Daily protein</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $user->profile->daily_protein }}g</dd>
                        </div>
                    </dl>

                    @if ($user->profile->bio)
                        <p class="mt-4 text-gray-800 dark:text-gray-200">{{ $user->profile->bio }}</p>
                    @endif
                @else
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $user->name }} hasn't set a goal yet.</p>
                @endif
            </section>

            <section id="recipes" aria-labelledby="recipes-heading">
                <h3 id="recipes-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Recipes ({{ $recipes->total() }})
                </h3>

                @if ($recipes->isEmpty())
                    <p class="mt-2 text-gray-600 dark:text-gray-400">No recipes posted yet.</p>
                @else
                    <div class="mt-4 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($recipes as $recipe)
                            <x-recipe-card :recipe="$recipe" :show-author="false" />
                        @endforeach
                    </div>

                    <div class="mt-6">
                        {{ $recipes->links() }}
                    </div>
                @endif
            </section>

            <section id="comments" class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800" aria-labelledby="user-comments-heading">
                <h3 id="user-comments-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    Comments ({{ $comments->total() }})
                </h3>

                @forelse ($comments as $comment)
                    <article class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            On
                            <a href="{{ route('recipes.show', $comment->recipe) }}" class="font-medium text-indigo-700 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded dark:text-indigo-300">{{ $comment->recipe->title }}</a>
                            · {{ $comment->created_at->diffForHumans() }}
                        </p>
                        <p class="mt-1 whitespace-pre-line text-gray-800 dark:text-gray-200">{{ $comment->body }}</p>
                    </article>
                @empty
                    <p class="mt-2 text-gray-600 dark:text-gray-400">No comments yet.</p>
                @endforelse

                <div class="mt-6">
                    {{ $comments->links() }}
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
