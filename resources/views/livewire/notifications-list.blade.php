<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Volt\Component;
use Livewire\WithPagination;

/*
 * The notifications page: everything that's happened to your recipes and comments,
 * newest first. Marking them as read happens without a page reload.
 */
new class extends Component
{
    use WithPagination;

    #[Computed]
    public function notifications()
    {
        return Auth::user()->notifications()->paginate(15);
    }

    /**
     * Mark one as read and go to the recipe (and the comment, if it's about one).
     */
    public function open(string $id): void
    {
        $notification = $this->findNotification($id);
        $notification->markAsRead();

        $url = route('recipes.show', $notification->data['recipe_id']);

        if ($notification->data['comment_id'] ?? null) {
            $url .= '#comment-'.$notification->data['comment_id'];
        }

        $this->redirect($url, navigate: true);
    }

    public function markAsRead(string $id): void
    {
        $this->findNotification($id)->markAsRead();
        $this->dispatch('notifications-updated'); // tells the bell to update its number
    }

    public function markAllAsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->dispatch('notifications-updated');
    }

    /**
     * Only your own notifications, so nobody can mark someone else's by sending their id.
     */
    private function findNotification(string $id)
    {
        return Auth::user()->notifications()->findOrFail($id);
    }
}; ?>

<div class="rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ $this->notifications->total() }} {{ Str::plural('notification', $this->notifications->total()) }}
        </h3>

        @if (Auth::user()->unreadNotifications()->exists())
            <x-secondary-button wire:click="markAllAsRead">Mark all as read</x-secondary-button>
        @endif
    </div>

    <ul class="mt-4 divide-y divide-gray-200 dark:divide-gray-700">
        @forelse ($this->notifications as $notification)
            @php($data = $notification->data)
            <li wire:key="notification-{{ $notification->id }}"
                class="flex flex-wrap items-start justify-between gap-3 py-4 {{ $notification->unread() ? 'bg-indigo-50 -mx-3 px-3 rounded dark:bg-indigo-900/30' : '' }}">
                <div class="min-w-0 flex-1">
                    <p class="text-gray-800 dark:text-gray-200">
                        {{-- "New" in words as well as the coloured background, so it doesn't rely on colour alone --}}
                        @if ($notification->unread())
                            <span class="me-1 rounded bg-indigo-600 px-1.5 py-0.5 text-xs font-semibold text-white">New</span>
                        @endif

                        <span class="font-semibold">{{ $data['actor_name'] }}</span>
                        @switch($data['kind'])
                            @case('comment')
                                commented on your recipe <span class="font-semibold">{{ $data['recipe_title'] }}</span>
                                @break
                            @case('recipe_like')
                                liked your recipe <span class="font-semibold">{{ $data['recipe_title'] }}</span>
                                @break
                            @case('comment_like')
                                liked your comment on <span class="font-semibold">{{ $data['recipe_title'] }}</span>
                                @break
                        @endswitch
                    </p>

                    @if ($data['excerpt'])
                        <p class="mt-1 text-sm italic text-gray-600 dark:text-gray-400">"{{ $data['excerpt'] }}"</p>
                    @endif

                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" wire:click="open('{{ $notification->id }}')"
                        class="text-sm font-medium text-indigo-700 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded dark:text-indigo-300">
                        View<span class="sr-only"> {{ $data['recipe_title'] }}</span>
                    </button>

                    @if ($notification->unread())
                        <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                            class="text-sm font-medium text-gray-700 hover:underline focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded dark:text-gray-300">
                            Mark as read
                        </button>
                    @endif
                </div>
            </li>
        @empty
            <li class="py-4 text-gray-600 dark:text-gray-400">
                Nothing yet. You'll get a notification when someone comments on your recipes or likes your recipes and comments.
            </li>
        @endforelse
    </ul>

    <div class="mt-4">
        {{ $this->notifications->links() }}
    </div>
</div>
