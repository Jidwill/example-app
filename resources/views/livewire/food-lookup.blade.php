<?php

use App\Contracts\FoodLookup;
use App\Services\FoodLookup\FoodItem;
use App\Services\FoodLookup\FoodLookupException;
use Livewire\Volt\Component;

/*
 * "Work out the macros" box on the recipe form. Search for an ingredient, say how many grams
 * you use, and it adds up the totals. "Fill in the macros" then sends the totals to the
 * recipe form, which divides them by the number of servings.
 *
 * search() asks for a FoodLookup in its parameters - the service container sees that and
 * passes in whatever FoodLookupServiceProvider bound (the real Open Food Facts API by default).
 * This component never creates the service or knows which one it's using.
 */
new class extends Component
{
    public string $query = '';

    public string $grams = '100';

    /** @var list<array<string, mixed>> search results (arrays, because Livewire can't keep objects) */
    public array $results = [];

    /** @var list<array<string, mixed>> ingredients added so far, with their grams */
    public array $tally = [];

    public string $source = '';

    public ?string $error = null;

    public bool $searched = false;

    public function search(FoodLookup $foods): void
    {
        $this->validate(
            ['query' => 'required|string|min:2|max:80'],
            attributes: ['query' => 'food'],
        );

        $this->error = null;
        $this->results = [];

        try {
            $this->results = array_map(fn (FoodItem $item) => $item->toArray(), $foods->search($this->query));
        } catch (FoodLookupException $e) {
            // the API being down shouldn't break the page - just say what happened
            $this->error = $e->getMessage();
        }

        $this->source = $foods->sourceName();
        $this->searched = true;
    }

    public function add(int $index): void
    {
        $this->validate(
            ['grams' => 'required|integer|min:1|max:5000'],
            attributes: ['grams' => 'amount'],
        );

        $food = $this->results[$index] ?? null;

        if (! $food) {
            return;
        }

        // the results are per 100g, so scale them to the amount used
        $factor = (int) $this->grams / 100;

        $this->tally[] = [
            'name' => $food['name'],
            'grams' => (int) $this->grams,
            'kcal' => round($food['kcal'] * $factor),
            'protein' => round($food['protein'] * $factor, 1),
            'carbs' => round($food['carbs'] * $factor, 1),
            'fat' => round($food['fat'] * $factor, 1),
        ];
    }

    public function remove(int $index): void
    {
        unset($this->tally[$index]);
        $this->tally = array_values($this->tally);
    }

    /**
     * Totals for everything in the tally (the whole recipe, not per serving).
     */
    public function totals(): array
    {
        return [
            'kcal' => round(array_sum(array_column($this->tally, 'kcal'))),
            'protein' => round(array_sum(array_column($this->tally, 'protein'))),
            'carbs' => round(array_sum(array_column($this->tally, 'carbs'))),
            'fat' => round(array_sum(array_column($this->tally, 'fat'))),
        ];
    }

    /**
     * Sends the totals to the recipe form (a browser event that Alpine picks up in the form).
     */
    public function fillRecipe(): void
    {
        if ($this->tally === []) {
            return;
        }

        $this->dispatch('fill-macros',
            ...$this->totals(),
            ingredients: array_map(fn ($item) => "{$item['grams']}g {$item['name']}", $this->tally),
        );
    }
}; ?>

<div class="rounded-md border border-gray-200 p-4 dark:border-gray-700">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Search for each ingredient, enter how many grams the whole recipe uses, and add it. Then fill in the macros
        and they'll be divided by the number of servings.
    </p>

    {{-- not a <form>, because this sits inside the recipe form. Enter is caught so it doesn't submit the recipe --}}
    <div class="mt-3 grid gap-3 sm:grid-cols-[1fr_8rem_auto] sm:items-end">
        <div>
            <x-input-label for="food-search" value="Food" />
            <x-text-input id="food-search" type="search" wire:model="query" wire:keydown.enter.prevent="search"
                class="mt-1 block w-full" placeholder="e.g. chicken breast" autocomplete="off"
                aria-describedby="food-search-error" :aria-invalid="$errors->has('query') ? 'true' : 'false'" />
        </div>
        <div>
            <x-input-label for="food-grams" value="Amount (g)" />
            <x-text-input id="food-grams" type="number" min="1" max="5000" wire:model="grams" x-on:keydown.enter.prevent
                class="mt-1 block w-full"
                aria-describedby="food-grams-error" :aria-invalid="$errors->has('grams') ? 'true' : 'false'" />
        </div>
        <x-secondary-button type="button" wire:click="search" wire:loading.attr="disabled" wire:target="search">
            Search
        </x-secondary-button>
    </div>
    <x-input-error id="food-search-error" :messages="$errors->get('query')" class="mt-2" />
    <x-input-error id="food-grams-error" :messages="$errors->get('grams')" class="mt-2" />

    <div aria-live="polite" class="mt-3">
        <p wire:loading wire:target="search" class="text-sm text-gray-600 dark:text-gray-400">Searching...</p>

        @if ($error)
            <p role="alert" class="rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-900/40 dark:text-amber-100">
                {{ $error }} You can still type the macros in yourself.
            </p>
        @elseif ($searched && $results === [])
            <p class="text-sm text-gray-600 dark:text-gray-400">No foods with macros found. Try a simpler search, like "oats".</p>
        @endif
    </div>

    @if ($results !== [])
        <ul class="mt-2 divide-y divide-gray-200 dark:divide-gray-700" aria-label="Search results">
            @foreach ($results as $index => $food)
                <li wire:key="result-{{ $index }}" class="flex flex-wrap items-center justify-between gap-2 py-2">
                    <div class="text-sm">
                        <p class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $food['name'] }}@if ($food['brand']) <span class="font-normal text-gray-600 dark:text-gray-400">({{ $food['brand'] }})</span>@endif
                        </p>
                        <p class="text-gray-600 dark:text-gray-400">
                            Per 100g: {{ round($food['kcal']) }} kcal · {{ $food['protein'] }}g protein · {{ $food['carbs'] }}g carbs · {{ $food['fat'] }}g fat
                        </p>
                    </div>
                    <x-secondary-button type="button" wire:click="add({{ $index }})">
                        Add {{ $grams }}g<span class="sr-only"> of {{ $food['name'] }}</span>
                    </x-secondary-button>
                </li>
            @endforeach
        </ul>
        <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">Results from {{ $source }}.</p>
    @endif

    @if ($tally !== [])
        @php($totals = $this->totals())
        <div class="mt-4 rounded-md bg-gray-50 p-3 dark:bg-gray-900">
            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">Whole recipe so far</p>
            <ul class="mt-1 space-y-1 text-sm text-gray-700 dark:text-gray-300">
                @foreach ($tally as $index => $item)
                    <li wire:key="tally-{{ $index }}" class="flex items-center justify-between gap-2">
                        <span>{{ $item['grams'] }}g {{ $item['name'] }} - {{ $item['protein'] }}g protein</span>
                        <button type="button" wire:click="remove({{ $index }})" class="text-red-700 hover:underline dark:text-red-400">
                            Remove<span class="sr-only"> {{ $item['name'] }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">
                Total: {{ $totals['kcal'] }} kcal · {{ $totals['protein'] }}g protein · {{ $totals['carbs'] }}g carbs · {{ $totals['fat'] }}g fat
            </p>
            <x-primary-button type="button" wire:click="fillRecipe" class="mt-3">Fill in the macros</x-primary-button>
        </div>
    @endif
</div>
