{{--
    The recipe form fields, shared by create.blade.php and edit.blade.php.
    $recipe is null when creating, so old() falls back to the recipe's current values when editing.
--}}
@php
    $recipe ??= null;
    // after a failed submit use what was ticked, otherwise the recipe's current tags
    $checkedTags = $errors->any() ? old('tags', []) : ($recipe?->tags->pluck('id')->all() ?? []);
    $textareaClasses = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
@endphp

<div>
    <x-input-label for="title" value="Title" />
    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
        :value="old('title', $recipe?->title)" required maxlength="255"
        aria-describedby="title-error" :aria-invalid="$errors->has('title') ? 'true' : 'false'" />
    <x-input-error id="title-error" :messages="$errors->get('title')" class="mt-2" />
</div>

<div>
    <x-input-label for="description" value="Short description" />
    <textarea id="description" name="description" rows="2" required maxlength="1000"
        aria-describedby="description-error" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
        class="{{ $textareaClasses }}">{{ old('description', $recipe?->description) }}</textarea>
    <x-input-error id="description-error" :messages="$errors->get('description')" class="mt-2" />
</div>

<div>
    <x-input-label for="ingredients" value="Ingredients (one per line)" />
    <textarea id="ingredients" name="ingredients" rows="6" required
        aria-describedby="ingredients-error" aria-invalid="{{ $errors->has('ingredients') ? 'true' : 'false' }}"
        class="{{ $textareaClasses }}">{{ old('ingredients', $recipe?->ingredients) }}</textarea>
    <x-input-error id="ingredients-error" :messages="$errors->get('ingredients')" class="mt-2" />
</div>

<div>
    <x-input-label for="method" value="Method (one step per line)" />
    <textarea id="method" name="method" rows="6" required
        aria-describedby="method-error" aria-invalid="{{ $errors->has('method') ? 'true' : 'false' }}"
        class="{{ $textareaClasses }}">{{ old('method', $recipe?->method) }}</textarea>
    <x-input-error id="method-error" :messages="$errors->get('method')" class="mt-2" />
</div>

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <x-input-label for="prep_minutes" value="Prep time (minutes)" />
        <x-text-input id="prep_minutes" name="prep_minutes" type="number" min="1" max="600" class="mt-1 block w-full"
            :value="old('prep_minutes', $recipe?->prep_minutes)" required
            aria-describedby="prep_minutes-error" :aria-invalid="$errors->has('prep_minutes') ? 'true' : 'false'" />
        <x-input-error id="prep_minutes-error" :messages="$errors->get('prep_minutes')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="servings" value="Servings" />
        <x-text-input id="servings" name="servings" type="number" min="1" max="20" class="mt-1 block w-full"
            :value="old('servings', $recipe?->servings ?? 1)" required
            aria-describedby="servings-error" :aria-invalid="$errors->has('servings') ? 'true' : 'false'" />
        <x-input-error id="servings-error" :messages="$errors->get('servings')" class="mt-2" />
    </div>
</div>

<fieldset>
    <legend class="font-medium text-sm text-gray-700 dark:text-gray-300">Macros per serving (grams)</legend>
    <div class="mt-2 grid gap-4 sm:grid-cols-3">
        {{-- the three macro boxes are the same apart from the name, so loop over them --}}
        @foreach (['protein' => 'Protein', 'carbs' => 'Carbs', 'fat' => 'Fat'] as $field => $label)
            <div>
                <x-input-label :for="$field" :value="$label" />
                <x-text-input :id="$field" :name="$field" type="number" min="0" max="500" class="mt-1 block w-full"
                    :value="old($field, $recipe?->$field)" required
                    :aria-describedby="$field.'-error'" :aria-invalid="$errors->has($field) ? 'true' : 'false'" />
                <x-input-error :id="$field.'-error'" :messages="$errors->get($field)" class="mt-2" />
            </div>
        @endforeach
    </div>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Calories are worked out for you from these.</p>
</fieldset>

{{-- "Don't know the macros?" helper. The food-lookup component sends totals for the whole recipe
     as a browser event, and this Alpine code divides them by the servings box and fills in the
     protein, carbs and fat boxes above. It also adds the ingredients to the ingredients list --}}
<details x-data="{
        message: '',
        fill(totals) {
            const servings = Math.max(1, parseInt(document.getElementById('servings').value) || 1);
            for (const field of ['protein', 'carbs', 'fat']) {
                document.getElementById(field).value = Math.round(totals[field] / servings);
            }
            const ingredients = document.getElementById('ingredients');
            ingredients.value = [ingredients.value.trim(), ...totals.ingredients].filter(Boolean).join('\n');
            this.message = `Macros filled in for ${servings} ${servings === 1 ? 'serving' : 'servings'}, and the ingredients were added to the list.`;
        }
    }" x-on:fill-macros.window="fill($event.detail)">
    <summary class="cursor-pointer text-sm font-medium text-indigo-700 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded dark:text-indigo-300">
        Don't know the macros? Work them out from your ingredients
    </summary>
    <div class="mt-3">
        <livewire:food-lookup />
    </div>
    {{-- read out by screen readers after the boxes are filled in --}}
    <p role="status" x-text="message" class="mt-2 text-sm text-green-700 dark:text-green-400"></p>
</details>

<div>
    <x-input-label for="image" value="Photo (optional, JPG, PNG or WebP, up to 2MB)" />

    @if ($recipe?->image_url)
        <div class="mt-2 flex items-center gap-4">
            <img src="{{ $recipe->image_url }}" alt="Current photo of {{ $recipe->title }}" class="h-20 w-28 rounded object-cover">
            <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
                <input type="checkbox" name="remove_image" value="1" @checked(old('remove_image'))
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                Remove current photo
            </label>
        </div>
    @endif

    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
        aria-describedby="image-error" aria-invalid="{{ $errors->has('image') ? 'true' : 'false' }}"
        class="mt-2 block w-full text-sm text-gray-700 dark:text-gray-300 file:me-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-gray-800 hover:file:bg-gray-200 dark:file:bg-gray-700 dark:file:text-gray-100">
    @if ($recipe?->image_url)
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Choosing a new photo replaces the current one.</p>
    @endif
    <x-input-error id="image-error" :messages="$errors->get('image')" class="mt-2" />
</div>

<fieldset>
    <legend class="font-medium text-sm text-gray-700 dark:text-gray-300">Tags (optional)</legend>
    <div class="mt-2 grid gap-2 sm:grid-cols-3">
        @foreach ($tags as $tag)
            <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                    @checked(in_array($tag->id, $checkedTags))
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                {{ $tag->name }}
            </label>
        @endforeach
    </div>
    {{-- 'tags*' matches the tags array and each tags.0, tags.1 etc, flatten turns them into one list --}}
    <x-input-error :messages="Arr::flatten($errors->get('tags*'))" class="mt-2" />
</fieldset>
