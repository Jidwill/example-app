@props(['user'])

{{-- small label next to coach and admin names, members don't get one --}}
@if ($user->isAdmin())
    <span {{ $attributes->merge(['class' => 'ms-1 rounded bg-purple-100 px-1.5 py-0.5 text-xs font-medium text-purple-900 dark:bg-purple-900/50 dark:text-purple-100']) }}>Admin</span>
@elseif ($user->isCoach())
    <span {{ $attributes->merge(['class' => 'ms-1 rounded bg-emerald-100 px-1.5 py-0.5 text-xs font-medium text-emerald-900 dark:bg-emerald-900/50 dark:text-emerald-100']) }}>Coach</span>
@endif
