<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Post a recipe
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- always say method="POST" rather than relying on the default (lecture 2.2),
                 enctype is needed or the photo doesn't get sent, and novalidate turns off the
                 browser's own pop-ups so Laravel's validation messages show instead
                 (the required attributes stay so screen readers still know which fields are needed) --}}
            <form method="POST" action="{{ route('recipes.store') }}" enctype="multipart/form-data" novalidate class="space-y-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                @csrf

                @include('recipes.partials.form')

                <div class="flex items-center gap-4">
                    <x-primary-button>Post recipe</x-primary-button>
                    <a href="{{ route('recipes.index') }}" class="text-sm text-gray-700 hover:underline dark:text-gray-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
