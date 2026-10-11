@props([
    'variant' => 'card',
    'exclusive' => false,
    'transition' => true,
    'size' => 'base',
])

@php
    $classes = match ($variant) {
        'plain', 'flush' => 'w-full divide-y divide-black/[0.08] dark:divide-white/[0.08] border-y border-black/[0.08] dark:border-white/[0.08]',
        default => 'w-full space-y-2',
    };
@endphp

<div
    {{ $attributes->class($classes) }}
    x-data="{
        activeId: null,
        exclusive: {{ $exclusive ? 'true' : 'false' }},
        variant: '{{ $variant }}',
        transition: {{ $transition ? 'true' : 'false' }},
        toggleExclusive(id) {
            this.activeId = (this.activeId === id) ? null : id;
        }
    }"
    @os-toggle-exclusive.stop="toggleExclusive($event.detail.id)"
    data-os-accordion="true"
    data-os-accordion="true"
>
    {{ $slot }}
</div>
