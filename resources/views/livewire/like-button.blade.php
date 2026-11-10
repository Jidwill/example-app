<?php

use App\Models\Like;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

/*
 * One like button that works for recipes AND comments, because likes are polymorphic -
 * $likeable can be any model with a likes() morphMany relationship.
 * Clicking it sends an AJAX request (Livewire) instead of reloading the page.
 */
new class extends Component
{
    public Model $likeable;

    public int $count = 0;

    public bool $liked = false;

    /**
     * Work out the count and whether I've liked it once when the button first loads.
     */
    public function mount(Model $likeable): void
    {
        $this->likeable = $likeable;
        $this->count = $likeable->likes()->count();
        $this->liked = $likeable->likes()->where('user_id', Auth::id())->exists();
    }

    /**
     * People can't like their own recipes or comments (same rule as the seeder).
     */
    public function isOwn(): bool
    {
        return $this->likeable->user_id === Auth::id();
    }

    /**
     * Like it, or take the like back if it's already liked.
     */
    public function toggle(): void
    {
        if ($this->isOwn()) {
            return;
        }

        $existing = $this->likeable->likes()->where('user_id', Auth::id())->first();

        if ($existing) {
            $existing->delete();
        } else {
            // user_id isn't fillable on Like, so set the relationship rather than mass assigning
            $like = new Like;
            $like->user()->associate(Auth::user());
            $this->likeable->likes()->save($like);
        }

        // recount rather than +1/-1 so it's right even if someone else liked it meanwhile
        $this->count = $this->likeable->likes()->count();
        $this->liked = ! $existing;
    }
}; ?>

<div class="inline-flex items-center">
    @php
        $what = $likeable instanceof \App\Models\Recipe ? 'recipe' : 'comment';
    @endphp

    @if ($this->isOwn())
        {{-- your own post - just show the count --}}
        <span class="inline-flex items-center gap-1 text-sm text-gray-600 dark:text-gray-400">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.653 16.915l-.005-.003-.019-.01a20.759 20.759 0 01-1.162-.682 22.045 22.045 0 01-2.582-1.9C4.045 12.733 2 10.352 2 7.5a4.5 4.5 0 018-2.828A4.5 4.5 0 0118 7.5c0 2.852-2.044 5.233-3.885 6.82a22.049 22.049 0 01-3.744 2.582l-.019.01-.005.003h-.002a.739.739 0 01-.69.001l-.002-.001z" /></svg>
            {{ $count }} {{ Str::plural('like', $count) }}
        </span>
    @else
        {{-- aria-pressed tells screen readers whether it's currently liked --}}
        <button type="button" wire:click="toggle" wire:loading.attr="disabled"
            aria-pressed="{{ $liked ? 'true' : 'false' }}"
            aria-label="{{ $liked ? 'Unlike' : 'Like' }} this {{ $what }}, {{ $count }} {{ Str::plural('like', $count) }}"
            class="inline-flex items-center gap-1 rounded-full border px-2.5 py-0.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 disabled:opacity-60
                {{ $liked
                    ? 'border-rose-300 bg-rose-50 text-rose-700 dark:border-rose-700 dark:bg-rose-900/40 dark:text-rose-200'
                    : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
            <svg class="h-4 w-4" viewBox="0 0 20 20" aria-hidden="true"
                fill="{{ $liked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                <path d="M9.653 16.915l-.005-.003-.019-.01a20.759 20.759 0 01-1.162-.682 22.045 22.045 0 01-2.582-1.9C4.045 12.733 2 10.352 2 7.5a4.5 4.5 0 018-2.828A4.5 4.5 0 0118 7.5c0 2.852-2.044 5.233-3.885 6.82a22.049 22.049 0 01-3.744 2.582l-.019.01-.005.003h-.002a.739.739 0 01-.69.001l-.002-.001z" />
            </svg>
            {{ $count }}
        </button>
    @endif
</div>
