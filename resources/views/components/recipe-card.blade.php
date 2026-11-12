@props(['recipe', 'showAuthor' => true])

{{--
    One recipe card, used on the recipes page and on user pages.
    Expects the recipe to have user and tags loaded, plus comments_count and likes_count (withCount).
--}}
<article class="flex flex-col overflow-hidden rounded-lg bg-white shadow-sm dark:bg-gray-800">
    {{-- photo, or a plain block so cards without one still line up.
         The placeholder is decorative so screen readers skip it --}}
    @if ($recipe->image_url)
        <img src="{{ $recipe->image_url }}" alt="Photo of {{ $recipe->title }}" class="h-44 w-full object-cover" loading="lazy">
    @else
        <div class="flex h-44 items-center justify-center bg-gray-100 dark:bg-gray-700" aria-hidden="true">
            <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.16-5.16a2.25 2.25 0 013.18 0l5.16 5.16m-1.5-1.5l1.41-1.41a2.25 2.25 0 013.18 0l2.91 2.91M3.75 21h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v13.5a1.5 1.5 0 001.5 1.5z" />
            </svg>
        </div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        {{-- only the owner, coaches and admins ever see hidden ones in a list --}}
        @if ($recipe->is_hidden)
            <p class="mb-2 self-start rounded bg-gray-800 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-white">Hidden</p>
        @endif
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            <a href="{{ route('recipes.show', $recipe) }}" class="hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded">
                {{ $recipe->title }}
            </a>
        </h3>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{-- no need to say who posted it on that person's own page --}}
            @if ($showAuthor)
                by <x-user-link :user="$recipe->user" /> ·
            @endif
            {{ $recipe->created_at->diffForHumans() }}
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
    </div>
</article>
