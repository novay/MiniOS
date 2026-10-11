@props([
    'kbd' => null,
])

@php
$classes = [
    'relative z-50 inline-flex items-center gap-1.5 m-0 px-2.5 py-1.5',
    'rounded-md',
    'text-xs font-normal leading-normal select-none',
    'bg-[#fbfbfb]/95 text-neutral-800 border border-black/[0.08] border-b-black/[0.14] shadow-[0_4px_14px_rgba(0,0,0,0.10),0_1px_2px_rgba(0,0,0,0.06)]',
    'dark:bg-[#2c2c2c]/95 dark:text-neutral-100 dark:border-white/[0.10] dark:border-b-white/[0.06] dark:shadow-[0_8px_24px_rgba(0,0,0,0.5),0_1px_3px_rgba(0,0,0,0.3)]',
    'backdrop-blur-md',
    'transition-opacity duration-150 ease-out',
];
@endphp

<div popover="manual" {{ $attributes->class($classes) }} data-flux-tooltip-content data-fluent-tooltip-content="true">
    {{ $slot }}

    @if ($kbd)
        <kbd class="ms-1.5 inline-flex items-center px-1.5 py-0.5 text-[10px] font-sans font-normal tracking-wide rounded-[3px] bg-black/5 dark:bg-white/10 text-neutral-500 dark:text-neutral-400 border border-black/5 dark:border-white/5 select-none shadow-2xs">
            {{ $kbd }}
        </kbd>
    @endif
</div>
