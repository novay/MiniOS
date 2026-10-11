@aware([
    'id' => null,
    'exclusive' => false,
    'disabled' => false,
])

@props([
    'id' => null,
    'exclusive' => false,
    'disabled' => false,
    'icon' => null,
    'description' => null,
    'badge' => null,
    'badgeVariant' => 'neutral',
])

@php
    $id = $id ?? $__data['id'] ?? null;
    $exclusive = (bool) ($exclusive ?? $__data['exclusive'] ?? false);
    $disabled = (bool) ($disabled ?? $__data['disabled'] ?? false);

    $isOpenExpr = $exclusive ? "activeId === '{$id}'" : "localOpen";
    $toggleExpr = $exclusive
        ? "!isDisabled && \$dispatch('os-toggle-exclusive', { id: '{$id}' })"
        : "!isDisabled && (localOpen = !localOpen)";
@endphp

<button
    type="button"
    @click="{{ $toggleExpr }}"
    :disabled="isDisabled"
    :aria-expanded="{{ $isOpenExpr }} ? 'true' : 'false'"
    {{ $attributes->class([
        'group/accordion-heading flex items-center justify-between w-full text-left font-sans transition-all duration-150 select-none cursor-pointer',
        'px-4 py-3 min-h-[46px]',
        'hover:bg-black/[0.02] dark:hover:bg-white/[0.04] active:bg-black/[0.04] dark:active:bg-white/[0.02]',
        'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-accent,#0067c0)] focus-visible:ring-offset-1 dark:focus-visible:ring-offset-[#1c1c1c]',
        'disabled:opacity-50 disabled:cursor-not-allowed',
    ]) }}
    data-os-accordion-heading="true"
    data-os-accordion-heading="true"
>
    <div class="flex items-start gap-3 min-w-0 flex-1">
        @if ($icon)
            <div class="shrink-0 text-neutral-500 dark:text-neutral-400 group-hover/accordion-heading:text-neutral-800 dark:group-hover/accordion-heading:text-neutral-100 transition-colors">
                <os:icon :name="$icon" class="size-5" />
            </div>
        @endif

        <div class="min-w-0 flex-1">
            <div class="text-[13px] sm:text-sm font-medium text-neutral-800 dark:text-neutral-100 leading-snug">
                {{ $slot }}
            </div>

            @if ($description)
                <div class="text-[13px] sm:text-sm text-neutral-500 dark:text-neutral-400 font-normal mt-0.5 leading-normal">
                    {{ $description }}
                </div>
            @endif
        </div>

        @if ($badge)
            <div class="shrink-0 me-2">
                <os:badge :variant="$badgeVariant" size="xs">{{ $badge }}</os:badge>
            </div>
        @endif
    </div>

    {{-- Windows 11 Fluent Chevron Indicator --}}
    <div class="shrink-0 ms-3 size-6 rounded-[4px] flex items-center justify-center text-neutral-400 dark:text-neutral-500 group-hover/accordion-heading:text-neutral-700 dark:group-hover/accordion-heading:text-neutral-200 transition-colors">
        <svg
            class="size-3.5 transition-transform duration-200 ease-out"
            :class="{{ $isOpenExpr }} ? 'rotate-180' : 'rotate-0'"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2.25"
            stroke-linecap="round"
            stroke-linejoin="round"
        >
            <polyline points="6 9 12 15 18 9"></polyline>
        </svg>
    </div>
</button>
