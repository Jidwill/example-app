<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

/*
 * The bell in the nav bar with the number of unread notifications.
 * wire:poll asks the server for the count every 15 seconds (AJAX), so a new
 * like or comment shows up without refreshing the page. It also updates straight
 * away when notifications are marked as read on the notifications page.
 */
new class extends Component
{
    public int $unread = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('notifications-updated')]
    public function refreshCount(): void
    {
        $this->unread = Auth::user()->unreadNotifications()->count();
    }
}; ?>

<div wire:poll.15s="refreshCount">
    <a href="{{ route('notifications.index') }}" wire:navigate
        aria-label="Notifications, {{ $unread }} unread"
        class="relative inline-flex items-center rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200">
        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
        </svg>

        @if ($unread > 0)
            {{-- the number is for sighted users, the aria-label above already says it --}}
            <span aria-hidden="true"
                class="absolute -right-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1 text-xs font-semibold text-white">
                {{ $unread > 9 ? '9+' : $unread }}
            </span>
        @endif
    </a>
</div>
