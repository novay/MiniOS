@props([
    'unfiltered' => [],
    'title' => __('Menu tidak ditemukan'),
    'subtitle' => __('Coba kata kunci lain'),
    'icon' => 'magnifying-glass',
])

@php
    $keywordsJson = json_encode(array_values(array_map('strtolower', (array) $unfiltered)));
@endphp

<div
    x-cloak
    x-show="!sidebarCollapsed && menuSearch && !{{ $keywordsJson }}.some(k => k.includes(menuSearch.toLowerCase().trim()))"
    {{ $attributes->merge([
        'class' => 'py-6 px-2 text-center text-xs text-neutral-400 dark:text-neutral-500 select-none'
    ]) }}
>
    @if ($icon)
        <flux:icon :name="$icon" class="mx-auto size-5 mb-1.5 text-neutral-300 dark:text-neutral-600" />
    @endif
    <span class="block font-medium text-neutral-600 dark:text-neutral-300">{{ $title }}</span>
    @if ($subtitle)
        <span class="block text-[11px] text-neutral-400 dark:text-neutral-500 mt-0.5">{{ $subtitle }}</span>
    @endif
    {{ $slot }}
</div>
