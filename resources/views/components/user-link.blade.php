@props(['user'])

{{-- a user's name linking to their page (/users/{id}), plus their Coach or Admin badge --}}
<a href="{{ route('users.show', $user) }}" {{ $attributes->merge(['class' => 'font-medium text-gray-900 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-600 rounded dark:text-gray-100']) }}>{{ $user->name }}</a><x-role-badge :user="$user" />
