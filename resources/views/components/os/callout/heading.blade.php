@props([
    'icon' => null,
    'iconVariant' => 'mini',
])

<div {{ $attributes->class('font-semibold text-xs sm:text-[13px] tracking-tight text-[var(--callout-heading)] flex items-center gap-2') }} data-slot="heading">
    @if (is_string($icon) && $icon !== '')
        <flux:icon :name="$icon" :variant="$iconVariant" class="size-4 shrink-0 text-[var(--callout-icon)]" />
    @elseif ($icon)
        {{ $icon }}
    @endif

    {{ $slot }}
</div>
