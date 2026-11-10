@props(['recipe'])

{{-- macros per serving - used on the recipe cards and the recipe page so it's only written once --}}
<ul {{ $attributes->merge(['class' => 'flex flex-wrap gap-2 text-xs font-medium']) }} aria-label="Macros per serving">
    <li class="rounded-full bg-amber-100 px-2.5 py-1 text-amber-900 dark:bg-amber-900/50 dark:text-amber-100">{{ $recipe->calories }} kcal</li>
    <li class="rounded-full bg-sky-100 px-2.5 py-1 text-sky-900 dark:bg-sky-900/50 dark:text-sky-100">{{ $recipe->protein }}g protein</li>
    <li class="rounded-full bg-emerald-100 px-2.5 py-1 text-emerald-900 dark:bg-emerald-900/50 dark:text-emerald-100">{{ $recipe->carbs }}g carbs</li>
    <li class="rounded-full bg-rose-100 px-2.5 py-1 text-rose-900 dark:bg-rose-900/50 dark:text-rose-100">{{ $recipe->fat }}g fat</li>
</ul>
