@aware([
    'variant' => 'card',
    'exclusive' => false,
])

@props([
    'id' => null,
    'heading' => null,
    'description' => null,
    'icon' => null,
    'badge' => null,
    'badgeVariant' => 'neutral',
    'expanded' => false,
    'disabled' => false,
    'variant' => null,
])

@php
    $id ??= 'os-acc-'.\Illuminate\Support\Str::random(8);
    $effectiveVariant = $variant ?? $__data['variant'] ?? 'card';

    $itemClasses = match ($effectiveVariant) {
        'plain', 'flush' => 'relative py-0 bg-transparent transition-colors',
        default => 'relative rounded-lg border border-black/[0.08] border-b-black/[0.14] dark:border-white/[0.08] dark:border-b-black/40 bg-white/75 dark:bg-[#202020]/75 backdrop-blur-md shadow-[0_1px_2px_rgba(0,0,0,0.03)] dark:shadow-[0_1px_3px_rgba(0,0,0,0.25)] transition-all overflow-hidden',
    };

    $isOpenExpr = $exclusive ? "activeId === '{$id}'" : "localOpen";
@endphp

<div
    {{ $attributes->class([
        $itemClasses,
        'opacity-50 pointer-events-none' => $disabled,
    ]) }}
    x-data="{
        itemId: '{{ $id }}',
        localOpen: {{ $expanded ? 'true' : 'false' }},
        isDisabled: {{ $disabled ? 'true' : 'false' }},
        init() {
            @if ($expanded && $exclusive)
                this.$nextTick(() => {
                    $dispatch('os-toggle-exclusive', { id: this.itemId });
                });
            @endif
        }
    }"
    :data-open="{{ $isOpenExpr }} ? 'true' : null"
    data-os-accordion-item="true"
    data-os-accordion-item="true"
>
    @if ($heading)
        <os:accordion.heading
            :id="$id"
            :exclusive="$exclusive"
            :disabled="$disabled"
            :icon="$icon"
            :description="$description"
            :badge="$badge"
            :badgeVariant="$badgeVariant"
        >
            {{ $heading }}
        </os:accordion.heading>

        <os:accordion.content
            :id="$id"
            :exclusive="$exclusive"
            :variant="$effectiveVariant"
        >
            {{ $slot }}
        </os:accordion.content>
    @else
        {{ $slot }}
    @endif
</div>
