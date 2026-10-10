@props([
    'label' => null,
    'keywords' => null,
])

@php
    $searchTarget = strtolower(($label ?? '') . ' ' . ($keywords ?? ''));
@endphp

@if ($label)
    <div
        x-show="!sidebarCollapsed && (!menuSearch || {{ json_encode($searchTarget) }}.includes(menuSearch.toLowerCase().trim()))"
        {{ $attributes->merge(['class' => 'px-2.5 pt-4 pb-1 select-none']) }}
    >
        <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
            {{ $label }}
        </span>
    </div>
    <div x-show="sidebarCollapsed" class="my-2 border-t border-neutral-200/80 dark:border-white/5"></div>
@else
    <div
        {{ $attributes->merge(['class' => 'my-2 border-t border-neutral-200/80 dark:border-white/5']) }}
    ></div>
@endif
