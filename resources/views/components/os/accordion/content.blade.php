@aware([
    'id' => null,
    'exclusive' => false,
    'variant' => 'card',
])

@props([
    'id' => null,
    'exclusive' => false,
    'variant' => null,
])

@php
    $id = $id ?? $__data['id'] ?? null;
    $exclusive = (bool) ($exclusive ?? $__data['exclusive'] ?? false);
    $effectiveVariant = $variant ?? $__data['variant'] ?? 'card';

    $isOpenExpr = $exclusive ? "activeId === '{$id}'" : "localOpen";

    $contentClasses = match ($effectiveVariant) {
        'plain', 'flush' => 'px-4 pb-3.5 pt-1 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed',
        default => 'border-t border-black/[0.05] dark:border-white/[0.05] bg-neutral-50/50 dark:bg-black/15 px-4 py-3.5 text-xs sm:text-sm text-neutral-600 dark:text-neutral-300 leading-relaxed',
    };
@endphp

<div
    class="grid transition-all duration-200 ease-out"
    :class="{{ $isOpenExpr }} ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0 pointer-events-none'"
    data-slot="content"
    data-os-accordion-content="true"
    data-os-accordion-content="true"
>
    <div class="overflow-hidden">
        <div {{ $attributes->class($contentClasses) }}>
            {{ $slot }}
        </div>
    </div>
</div>
