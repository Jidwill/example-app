<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Edit recipe
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- HTML forms can only send GET or POST, so @method('PUT') adds a hidden
                 _method field and Laravel treats it as a PUT to recipes.update --}}
            <form method="POST" action="{{ route('recipes.update', $recipe) }}" enctype="multipart/form-data" novalidate class="space-y-6 rounded-lg bg-white p-6 shadow-sm dark:bg-gray-800">
                @csrf
                @method('PUT')

                @include('recipes.partials.form', ['recipe' => $recipe])

                <div class="flex items-center gap-4">
                    <x-primary-button>Save changes</x-primary-button>
                    <a href="{{ route('recipes.show', $recipe) }}" class="text-sm text-gray-700 hover:underline dark:text-gray-300">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
