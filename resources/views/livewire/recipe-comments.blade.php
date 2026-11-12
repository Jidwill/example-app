<?php

use App\Models\Comment;
use App\Models\Recipe;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

/*
 * The comments section on a recipe page. Posting, editing and deleting all happen
 * through Livewire (AJAX), so the page never reloads.
 * Permissions come from CommentPolicy, the same as everywhere else:
 * members manage their own comments, coaches and admins can manage anyone's.
 */
new class extends Component
{
    public Recipe $recipe;

    #[Validate('required|string|min:2|max:1000', as: 'comment')]
    public string $body = '';

    // the comment being edited (null when nothing is being edited)
    public ?int $editingId = null;

    public string $editBody = '';

    // short message read out by screen readers after an action
    public string $status = '';

    /**
     * Comments newest first, with who wrote them. Computed so it's only
     * queried once per request and refreshes after each action.
     */
    #[Computed]
    public function comments()
    {
        return $this->recipe->comments()->with('user')->latest()->get();
    }

    public function addComment(): void
    {
        // CommentPolicy@create - you can comment on any recipe you're allowed to see
        $this->authorize('create', [Comment::class, $this->recipe]);

        $validated = $this->validate();

        // only body is fillable, so the user and recipe are set through the relationships
        $comment = new Comment(['body' => $validated['body']]);
        $comment->user()->associate(Auth::user());
        $this->recipe->comments()->save($comment);

        $this->reset('body');
        $this->status = 'Comment posted.';
    }

    public function startEditing(int $commentId): void
    {
        $comment = $this->findComment($commentId);
        $this->authorize('update', $comment);

        $this->resetValidation();
        $this->editingId = $comment->id;
        $this->editBody = $comment->body;
    }

    public function saveEdit(): void
    {
        $comment = $this->findComment($this->editingId);
        $this->authorize('update', $comment);

        $this->validate(
            ['editBody' => 'required|string|min:2|max:1000'],
            attributes: ['editBody' => 'comment'],
        );

        $comment->update(['body' => $this->editBody]);

        $this->cancelEditing();
        $this->status = 'Comment updated.';
    }

    public function cancelEditing(): void
    {
        $this->reset('editingId', 'editBody');
        $this->resetValidation('editBody');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->findComment($commentId);
        $this->authorize('delete', $comment);

        // the Comment model deletes its likes too (see Comment::booted)
        $comment->delete();

        if ($this->editingId === $commentId) {
            $this->cancelEditing();
        }

        $this->status = 'Comment deleted.';
    }

    /**
     * Only look for comments on THIS recipe, so someone can't send another
     * recipe's comment id and edit that instead.
     */
    private function findComment(?int $commentId): Comment
    {
        return $this->recipe->comments()->findOrFail($commentId);
    }
}; ?>

<section class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800" aria-labelledby="comments-heading">
    <h3 id="comments-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
        Comments ({{ $this->comments->count() }})
    </h3>

    {{-- screen readers announce this when it changes, e.g. "Comment posted." --}}
    <p role="status" class="sr-only">{{ $status }}</p>

    @can('create', [App\Models\Comment::class, $recipe])
        <form wire:submit="addComment" class="mt-4">
            <label for="new-comment" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Add a comment</label>
            <textarea id="new-comment" wire:model="body" rows="3" maxlength="1000"
                aria-describedby="new-comment-error" aria-invalid="{{ $errors->has('body') ? 'true' : 'false' }}"
                placeholder="Tried it? Swapped something? Let people know."
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
            <x-input-error id="new-comment-error" :messages="$errors->get('body')" class="mt-2" />

            <div class="mt-3 flex items-center gap-3">
                <x-primary-button wire:loading.attr="disabled" wire:target="addComment">Post comment</x-primary-button>
                <span wire:loading wire:target="addComment" class="text-sm text-gray-600 dark:text-gray-400">Posting...</span>
            </div>
        </form>
    @endcan

    @forelse ($this->comments as $comment)
        <article wire:key="comment-{{ $comment->id }}" id="comment-{{ $comment->id }}" class="scroll-mt-24 mt-4 border-t border-gray-200 pt-4 dark:border-gray-700">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    <x-user-link :user="$comment->user" />
                    · {{ $comment->created_at->diffForHumans() }}
                    {{-- so it's obvious when a comment was changed, e.g. by a coach --}}
                    @if ($comment->updated_at->gt($comment->created_at))
                        · <span class="italic">edited</span>
                    @endif
                </p>

                <div class="flex items-center gap-3">
                    <livewire:like-button :likeable="$comment" :key="'like-comment-'.$comment->id" />

                    @if ($editingId !== $comment->id)
                        @can('update', $comment)
                            <button type="button" wire:click="startEditing({{ $comment->id }})"
                                class="text-sm font-medium text-indigo-700 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded dark:text-indigo-300">
                                Edit<span class="sr-only"> comment by {{ $comment->user->name }}</span>
                            </button>
                        @endcan
                    @endif

                    @can('delete', $comment)
                        <button type="button" wire:click="deleteComment({{ $comment->id }})"
                            wire:confirm="Delete this comment?"
                            class="text-sm font-medium text-red-700 hover:underline focus:outline-none focus:ring-2 focus:ring-red-500 rounded dark:text-red-400">
                            Delete<span class="sr-only"> comment by {{ $comment->user->name }}</span>
                        </button>
                    @endcan
                </div>
            </div>

            @if ($editingId === $comment->id)
                <form wire:submit="saveEdit" class="mt-2">
                    <label for="edit-comment-{{ $comment->id }}" class="sr-only">Edit comment</label>
                    {{-- x-init moves keyboard focus into the box when editing starts --}}
                    <textarea id="edit-comment-{{ $comment->id }}" wire:model="editBody" rows="3" maxlength="1000" x-init="$el.focus()"
                        aria-describedby="edit-comment-error" aria-invalid="{{ $errors->has('editBody') ? 'true' : 'false' }}"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300"></textarea>
                    <x-input-error id="edit-comment-error" :messages="$errors->get('editBody')" class="mt-2" />

                    <div class="mt-2 flex items-center gap-3">
                        <x-primary-button wire:loading.attr="disabled" wire:target="saveEdit">Save</x-primary-button>
                        <x-secondary-button wire:click="cancelEditing">Cancel</x-secondary-button>
                    </div>
                </form>
            @else
                <p class="mt-1 whitespace-pre-line text-gray-800 dark:text-gray-200">{{ $comment->body }}</p>
            @endif
        </article>
    @empty
        <p class="mt-4 text-gray-600 dark:text-gray-400">No comments yet. Be the first!</p>
    @endforelse
</section>
