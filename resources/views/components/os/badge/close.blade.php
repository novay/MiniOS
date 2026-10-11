@props([
    'iconVariant' => 'micro',
    'icon' => 'x-mark',
])

@php
    $iconVariant = $attributes->pluck('icon:variant') ?? $iconVariant;
    $classes = 'p-0.5 -my-1 -me-1 ms-1 inline-flex items-center justify-center rounded-sm opacity-60 hover:opacity-100 hover:bg-black/5 dark:hover:bg-white/10 transition-opacity cursor-pointer';
@endphp

<button type="button" {{ $attributes->class($classes) }} data-os-badge-close aria-label="Close">
    @if (is_string($icon) && $icon !== '')
        <flux:icon :name="$icon" :variant="$iconVariant" class="size-3.5" />
    @elseif ($icon)
        {{ $icon }}
    @endif
</button>
