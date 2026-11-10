<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Post a recipe
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- always say method="POST" rather than relying on the default (lecture 2.2) --}}
            <form method="POST" action="{{ route('recipes.store') }}" class="space-y-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                @csrf

                <div>
                    <x-input-label for="title" value="Title" />
                    <x-text-input id="title" name="title" type="text" class="mt-1 block w-full"
                        :value="old('title')" required maxlength="255"
                        aria-describedby="title-error" :aria-invalid="$errors->has('title') ? 'true' : 'false'" />
                    <x-input-error id="title-error" :messages="$errors->get('title')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="description" value="Short description" />
                    <textarea id="description" name="description" rows="2" required maxlength="1000"
                        aria-describedby="description-error" aria-invalid="{{ $errors->has('description') ? 'true' : 'false' }}"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('description') }}</textarea>
                    <x-input-error id="description-error" :messages="$errors->get('description')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="ingredients" value="Ingredients (one per line)" />
                    <textarea id="ingredients" name="ingredients" rows="6" required
                        aria-describedby="ingredients-error" aria-invalid="{{ $errors->has('ingredients') ? 'true' : 'false' }}"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('ingredients') }}</textarea>
                    <x-input-error id="ingredients-error" :messages="$errors->get('ingredients')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="method" value="Method (one step per line)" />
                    <textarea id="method" name="method" rows="6" required
                        aria-describedby="method-error" aria-invalid="{{ $errors->has('method') ? 'true' : 'false' }}"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('method') }}</textarea>
                    <x-input-error id="method-error" :messages="$errors->get('method')" class="mt-2" />
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="prep_minutes" value="Prep time (minutes)" />
                        <x-text-input id="prep_minutes" name="prep_minutes" type="number" min="1" max="600" class="mt-1 block w-full"
                            :value="old('prep_minutes')" required
                            aria-describedby="prep_minutes-error" :aria-invalid="$errors->has('prep_minutes') ? 'true' : 'false'" />
                        <x-input-error id="prep_minutes-error" :messages="$errors->get('prep_minutes')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="servings" value="Servings" />
                        <x-text-input id="servings" name="servings" type="number" min="1" max="20" class="mt-1 block w-full"
                            :value="old('servings', 1)" required
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
                                    :value="old($field)" required
                                    :aria-describedby="$field.'-error'" :aria-invalid="$errors->has($field) ? 'true' : 'false'" />
                                <x-input-error :id="$field.'-error'" :messages="$errors->get($field)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Calories are worked out for you from these.</p>
                </fieldset>

                <fieldset>
                    <legend class="font-medium text-sm text-gray-700 dark:text-gray-300">Tags (optional)</legend>
                    <div class="mt-2 grid gap-2 sm:grid-cols-3">
                        @foreach ($tags as $tag)
                            <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
                                <input type="checkbox" name="tags[]" value="{{ $tag->id }}"
                                    @checked(in_array($tag->id, old('tags', [])))
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900">
                                {{ $tag->name }}
                            </label>
                        @endforeach
                    </div>
                    {{-- 'tags*' matches the tags array and each tags.0, tags.1 etc, flatten turns them into one list --}}
                    <x-input-error :messages="Arr::flatten($errors->get('tags*'))" class="mt-2" />
                </fieldset>

                <div class="flex items-center gap-4">
                    <x-primary-button>Post recipe</x-primary-button>
                    <a href="{{ route('recipes.index') }}" class="text-sm text-gray-700 hover:underline dark:text-gray-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
