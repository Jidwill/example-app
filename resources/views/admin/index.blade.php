<x-app-layout>
    <x-slot name="title">Admin</x-slot>

    <x-slot name="header">
        <h1 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Admin
        </h1>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <livewire:admin.manage-roles />
            <livewire:admin.manage-tags />
        </div>
    </div>
</x-app-layout>
