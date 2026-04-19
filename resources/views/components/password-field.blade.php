@props([
    'name',
    'id' => null,
    'value' => null,
    'autocomplete' => 'current-password',
    'required' => false,
])

@php($fieldId = $id ?? $name)

<div class="relative mt-1" x-data="{ show: false }">
    <input
        id="{{ $fieldId }}"
        name="{{ $name }}"
        :type="show ? 'text' : 'password'"
        @if ($value !== null) value="{{ $value }}" @endif
        autocomplete="{{ $autocomplete }}"
        @if ($required) required @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-md border-gray-300 pr-24 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100',
        ]) }}
    />
    <button
        type="button"
        class="absolute inset-y-0 right-2 my-auto h-8 rounded px-2 text-xs font-medium text-blue-600 hover:bg-gray-100 dark:text-blue-400 dark:hover:bg-gray-800"
        @click.prevent="show = !show"
        tabindex="-1"
    >
        <span x-show="!show">Покажи</span>
        <span x-show="show" x-cloak>Скрий</span>
    </button>
</div>
