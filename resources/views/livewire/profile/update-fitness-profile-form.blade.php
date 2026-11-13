<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

/*
 * Lets you set your goal, daily targets and bio, which show on your user page.
 * Sits on Breeze's Profile page next to the name/email and password forms.
 */
new class extends Component
{
    public string $goal = 'maintain';

    // strings because that's what form boxes send - the validation checks they're whole numbers
    // (an int property would just ignore an empty box instead of showing an error)
    public string $daily_calories = '2500';

    public string $daily_protein = '150';

    public string $bio = '';

    /**
     * Fill the form from the current profile, if there is one.
     */
    public function mount(): void
    {
        $profile = Auth::user()->profile;

        if ($profile) {
            $this->goal = $profile->goal;
            $this->daily_calories = (string) $profile->daily_calories;
            $this->daily_protein = (string) $profile->daily_protein;
            $this->bio = $profile->bio ?? '';
        }
    }

    public function updateFitnessProfile(): void
    {
        $validated = $this->validate([
            'goal' => ['required', Rule::in(['bulk', 'cut', 'maintain'])],
            'daily_calories' => ['required', 'integer', 'min:1000', 'max:6000'],
            'daily_protein' => ['required', 'integer', 'min:30', 'max:400'],
            'bio' => ['nullable', 'string', 'max:500'],
        ], attributes: [
            'daily_calories' => 'daily calories',
            'daily_protein' => 'daily protein',
        ]);

        // updateOrCreate covers older accounts that were made before profiles were automatic
        Auth::user()->profile()->updateOrCreate([], [
            ...$validated,
            'bio' => $validated['bio'] ?: null,
        ]);

        $this->dispatch('fitness-profile-updated');
    }
}; ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Training goal</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Your goal and daily targets. These show on your page, which other members see when they click your name.
        </p>
    </header>

    <form wire:submit="updateFitnessProfile" class="mt-6 space-y-6" novalidate>
        <fieldset>
            <legend class="font-medium text-sm text-gray-700 dark:text-gray-300">Goal</legend>
            <div class="mt-2 flex flex-wrap gap-4">
                @foreach (['bulk' => 'Bulk', 'cut' => 'Cut', 'maintain' => 'Maintain'] as $value => $label)
                    <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
                        <input type="radio" wire:model="goal" name="goal" value="{{ $value }}"
                            class="border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-600 dark:border-gray-700 dark:bg-gray-900">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('goal')" />
        </fieldset>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <x-input-label for="daily_calories" value="Daily calories (kcal)" />
                <x-text-input wire:model="daily_calories" id="daily_calories" type="number" min="1000" max="6000" class="mt-1 block w-full" required
                    aria-describedby="daily_calories-error" :aria-invalid="$errors->has('daily_calories') ? 'true' : 'false'" />
                <x-input-error id="daily_calories-error" class="mt-2" :messages="$errors->get('daily_calories')" />
            </div>

            <div>
                <x-input-label for="daily_protein" value="Daily protein (g)" />
                <x-text-input wire:model="daily_protein" id="daily_protein" type="number" min="30" max="400" class="mt-1 block w-full" required
                    aria-describedby="daily_protein-error" :aria-invalid="$errors->has('daily_protein') ? 'true' : 'false'" />
                <x-input-error id="daily_protein-error" class="mt-2" :messages="$errors->get('daily_protein')" />
            </div>
        </div>

        <div>
            <x-input-label for="bio" value="Bio (optional)" />
            <textarea wire:model="bio" id="bio" rows="3" maxlength="500"
                aria-describedby="bio-error" aria-invalid="{{ $errors->has('bio') ? 'true' : 'false' }}"
                placeholder="e.g. Training for my first powerlifting meet."
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
            <x-input-error id="bio-error" class="mt-2" :messages="$errors->get('bio')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Save</x-primary-button>

            <x-action-message class="me-3" on="fitness-profile-updated">
                Saved.
            </x-action-message>
        </div>
    </form>
</section>
