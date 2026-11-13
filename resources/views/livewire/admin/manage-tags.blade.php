<?php

use App\Models\Tag;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;

/*
 * Admins add, rename and delete the tags people can put on recipes.
 * Uses the manage-tags gate from AppServiceProvider (admins only).
 */
new class extends Component
{
    public string $newName = '';

    public ?int $editingId = null;

    public string $editName = '';

    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('manage-tags');
    }

    #[Computed]
    public function tags()
    {
        return Tag::withCount('recipes')->orderBy('name')->get();
    }

    public function addTag(): void
    {
        Gate::authorize('manage-tags');

        $this->validate(
            ['newName' => ['required', 'string', 'max:30', Rule::unique('tags', 'name')]],
            attributes: ['newName' => 'tag name'],
        );

        Tag::create(['name' => trim($this->newName)]);

        $this->status = "Added the {$this->newName} tag.";
        $this->reset('newName');
    }

    public function startEditing(int $tagId): void
    {
        $tag = Tag::findOrFail($tagId);

        $this->resetValidation();
        $this->editingId = $tag->id;
        $this->editName = $tag->name;
    }

    public function saveEdit(): void
    {
        Gate::authorize('manage-tags');

        $this->validate(
            // unique, but ignoring the tag being renamed
            ['editName' => ['required', 'string', 'max:30', Rule::unique('tags', 'name')->ignore($this->editingId)]],
            attributes: ['editName' => 'tag name'],
        );

        Tag::findOrFail($this->editingId)->update(['name' => trim($this->editName)]);

        $this->status = 'Tag renamed.';
        $this->reset('editingId', 'editName');
    }

    public function cancelEditing(): void
    {
        $this->reset('editingId', 'editName');
        $this->resetValidation();
    }

    public function deleteTag(int $tagId): void
    {
        Gate::authorize('manage-tags');

        // the recipe_tag rows go too because of cascadeOnDelete on the pivot table.
        // The recipes themselves are untouched, they just lose this tag
        $tag = Tag::findOrFail($tagId);
        $tag->delete();

        $this->status = "Deleted the {$tag->name} tag.";
    }
}; ?>

<section class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800" aria-labelledby="tags-heading">
    <h2 id="tags-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Tags</h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">The tags members can put on their recipes. Deleting one removes it from every recipe.</p>

    <p role="status" class="sr-only">{{ $status }}</p>

    <form wire:submit="addTag" class="mt-4 flex flex-wrap items-end gap-3" novalidate>
        <div>
            <x-input-label for="new-tag" value="New tag" />
            <x-text-input id="new-tag" wire:model="newName" maxlength="30" class="mt-1 block w-56"
                aria-describedby="new-tag-error" :aria-invalid="$errors->has('newName') ? 'true' : 'false'" />
        </div>
        <x-primary-button>Add tag</x-primary-button>
    </form>
    <x-input-error id="new-tag-error" :messages="$errors->get('newName')" class="mt-2" />

    <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
        @foreach ($this->tags as $tag)
            <li wire:key="tag-{{ $tag->id }}" class="flex flex-wrap items-center justify-between gap-3 py-3">
                @if ($editingId === $tag->id)
                    <form wire:submit="saveEdit" class="flex flex-wrap items-center gap-3" novalidate>
                        <label for="edit-tag-{{ $tag->id }}" class="sr-only">New name for {{ $tag->name }}</label>
                        <x-text-input id="edit-tag-{{ $tag->id }}" wire:model="editName" maxlength="30" x-init="$el.focus()" class="w-56"
                            aria-describedby="edit-tag-error" :aria-invalid="$errors->has('editName') ? 'true' : 'false'" />
                        <x-primary-button>Save</x-primary-button>
                        <x-secondary-button wire:click="cancelEditing">Cancel</x-secondary-button>
                        <x-input-error id="edit-tag-error" :messages="$errors->get('editName')" class="w-full" />
                    </form>
                @else
                    <p class="text-gray-900 dark:text-gray-100">
                        {{ $tag->name }}
                        <span class="text-sm text-gray-600 dark:text-gray-400">· {{ $tag->recipes_count }} {{ Str::plural('recipe', $tag->recipes_count) }}</span>
                    </p>
                    <div class="flex items-center gap-4">
                        <button type="button" wire:click="startEditing({{ $tag->id }})" class="text-sm font-medium text-indigo-700 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded dark:text-indigo-300">
                            Rename<span class="sr-only"> {{ $tag->name }}</span>
                        </button>
                        <button type="button" wire:click="deleteTag({{ $tag->id }})" wire:confirm="Delete the {{ $tag->name }} tag? It will be removed from {{ $tag->recipes_count }} {{ Str::plural('recipe', $tag->recipes_count) }}."
                            class="text-sm font-medium text-red-700 hover:underline focus:outline-none focus:ring-2 focus:ring-red-500 rounded dark:text-red-400">
                            Delete<span class="sr-only"> {{ $tag->name }}</span>
                        </button>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
</section>
