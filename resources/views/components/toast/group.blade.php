@props([
    'position' => null,
])

@php
    $position = $position ?? (function_exists('os_setting') ? os_setting('notifications.position', 'bottom end') : 'bottom end');

    $positionClasses = match ($position) {
        'top end' => 'top-9 right-4 items-end',
        'top start' => 'top-9 left-4 items-start',
        'bottom start' => 'bottom-16 sm:bottom-20 left-4 items-start',
        default => 'bottom-16 sm:bottom-20 right-4 items-end',
    };
@endphp

<div
    class="fixed z-[9999] pointer-events-none select-none flex flex-col {{ $positionClasses }}"
    :class="{
        'bottom-16 sm:bottom-20': (settings?.dock?.position ?? 'bottom') === 'bottom',
        'bottom-4': (settings?.dock?.position ?? 'bottom') !== 'bottom',
    }"
    aria-live="polite"
    wire:ignore
    @mouseenter="pauseAllToasts()"
    @mouseleave="resumeAllToasts()"
    {{ $attributes }}
>
    {{ $slot }}
</div>
