@props(['recipe', 'compact' => false])

{{--
    How much of the viewer's daily targets one serving covers, using the goal and targets
    from their profile (Profile page > Training goal). auth()->user()->profile is only
    loaded once per page, so lots of cards don't mean lots of queries.
--}}
@php
    $profile = auth()->user()?->profile;

    $fits = $profile ? [
        ['label' => 'Protein', 'amount' => $recipe->protein, 'target' => $profile->daily_protein, 'unit' => 'g', 'bar' => 'bg-sky-600'],
        ['label' => 'Calories', 'amount' => $recipe->calories, 'target' => $profile->daily_calories, 'unit' => ' kcal', 'bar' => 'bg-amber-500'],
    ] : [];

    foreach ($fits as $i => $fit) {
        $fits[$i]['percent'] = $fit['target'] > 0 ? (int) round($fit['amount'] / $fit['target'] * 100) : 0;
    }
@endphp

@if ($compact)
    @if ($profile)
        <p {{ $attributes->merge(['class' => 'text-xs font-medium text-sky-800 dark:text-sky-300']) }}>
            {{ $fits[0]['percent'] }}% of your daily protein
        </p>
    @endif
@else
    <section {{ $attributes->merge(['class' => 'rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800']) }} aria-labelledby="fit-heading">
        <h2 id="fit-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">How one serving fits your day</h2>

        @if ($profile)
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Based on your {{ $profile->goal }} targets.
                <a href="{{ route('profile') }}" class="text-emerald-700 underline hover:no-underline dark:text-emerald-300">Change them</a>
            </p>

            <div class="mt-4 space-y-4">
                @foreach ($fits as $fit)
                    <div>
                        {{-- the words say everything; the bar underneath is just a picture of it --}}
                        <p class="flex flex-wrap justify-between gap-2 text-sm text-gray-800 dark:text-gray-200">
                            <span class="font-medium">{{ $fit['label'] }}</span>
                            <span>{{ $fit['amount'] }}{{ $fit['unit'] }} is {{ $fit['percent'] }}% of your daily {{ number_format($fit['target']) }}{{ $fit['unit'] }}</span>
                        </p>
                        <div class="mt-1 h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700" aria-hidden="true">
                            <div class="h-2.5 rounded-full {{ $fit['bar'] }}" style="width: {{ min(100, $fit['percent']) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('profile') }}" class="text-emerald-700 underline hover:no-underline dark:text-emerald-300">Set your daily targets</a>
                to see how much of your day this recipe covers.
            </p>
        @endif
    </section>
@endif
