@props([
    'user',
    'size' => 'h-9 w-9',
])

@php($initial = mb_strtoupper(mb_substr($user->firstName(), 0, 1)))

@if ($user->avatarPublicUrl())
    <img
        {{ $attributes->merge(['class' => $size.' shrink-0 rounded-full object-cover ring-2 ring-blue-600/25 dark:ring-blue-400/30']) }}
        src="{{ $user->avatarPublicUrl() }}"
        alt=""
    />
@else
    <span
        {{ $attributes->merge(['class' => $size.' flex shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-blue-700 text-sm font-semibold text-white shadow-sm ring-2 ring-blue-600/20 dark:from-blue-600 dark:to-blue-900 dark:ring-blue-500/25']) }}
        aria-hidden="true"
    >{{ $initial }}</span>
@endif
