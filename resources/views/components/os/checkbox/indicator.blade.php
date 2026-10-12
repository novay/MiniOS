@props([
    'class' => null,
])

@php
    $classes = Flux::classes()
        ->add('shrink-0 size-[1.125rem] rounded-[4px] flex justify-center items-center transition-all duration-150 select-none')
        ->add('text-xs text-white')
        ->add('shadow-[0_1px_2px_rgba(0,0,0,0.06)] dark:shadow-[0_1px_2px_rgba(0,0,0,0.3)]')
        ->add('border border-zinc-300 dark:border-white/15')
        ->add('bg-white dark:bg-white/10')
        ->add('[ui-checkbox[disabled]_&]:opacity-50 [ui-checkbox[disabled]_&]:cursor-not-allowed [ui-checkbox[disabled]_&]:shadow-none')
        ->add('[ui-checkbox[data-checked]_&]:border-transparent [ui-checkbox[data-indeterminate]_&]:border-transparent')
        ->add('[ui-checkbox[data-checked]_&]:bg-[var(--color-accent,#0067c0)] [ui-checkbox[data-indeterminate]_&]:bg-[var(--color-accent,#0067c0)]')
        ->add('[ui-checkbox[data-checked]:not([data-indeterminate])_&>svg:first-child]:block [ui-checkbox[data-indeterminate]_&>svg:last-child]:block')
        ->add($class);
@endphp

<div {{ $attributes->class($classes) }} data-os-checkbox-indicator>
    {{-- Check Icon (shown when checked and not indeterminate) --}}
    <svg class="os-checkbox-check hidden size-3 text-white stroke-[2.75]" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M3.25 8.25l3.25 3.5 6.25-7.5" />
    </svg>

    {{-- Indeterminate Minus/Dash Icon (shown when indeterminate) --}}
    <svg class="os-checkbox-dash hidden size-3 text-white stroke-[2.75]" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-linecap="round" aria-hidden="true">
        <path d="M3.5 8h9" />
    </svg>
</div>
