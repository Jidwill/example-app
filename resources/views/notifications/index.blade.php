<x-app-layout>
    <x-slot name="title">Notifications</x-slot>

    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Notifications
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <livewire:notifications-list />
        </div>
    </div>
</x-app-layout>
