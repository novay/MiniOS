@props([
    'variant' => 'outline',
    'size' => 'base',
    'icon' => null,
    'iconLeading' => null,
    'iconTrailing' => null,
    'iconVariant' => null,
    'type' => 'button',
    'href' => null,
    'loading' => null,
    'align' => 'center',
    'square' => false,
    'color' => null,
    'inset' => null,
    'kbd' => null,
    'disabled' => false,
    'tooltip' => null,
    'tooltipPosition' => 'top',
    'tooltipKbd' => null,
])

@php
    $iconLeading = $icon ?? $iconLeading ?? $attributes->pluck('icon:leading');
    $iconTrailing ??= $attributes->pluck('icon:trailing');
    $iconVariant ??= $attributes->pluck('icon:variant');
    $tooltip ??= $attributes->pluck('tooltip');
    $tooltipPosition = $attributes->pluck('tooltip:position') ?? $tooltipPosition;
    $tooltipKbd ??= $attributes->pluck('tooltip:kbd');

    // Windows 11 Fluent Kit Design Dimensions
    $sizeClasses = match ($size) {
        'xs' => $square
            ? 'size-6 p-0 rounded-[4px]'
            : 'h-6 px-2 text-[11px] rounded-[4px] gap-1.5',
        'sm' => $square
            ? 'size-7.5 p-0 rounded-md'
            : 'h-7.5 px-2.5 text-xs rounded-md gap-1.5',
        'lg' => $square
            ? 'size-10 p-0 rounded-md'
            : 'h-10 px-4 text-sm rounded-md gap-2.5',
        default => $square
            ? 'size-8.5 p-0 rounded-md'
            : 'h-8.5 px-3.5 text-xs sm:text-[13px] rounded-md gap-2',
    };

    $iconSize = match ($size) {
        'xs' => 'size-3',
        'sm' => 'size-3.5',
        'lg' => 'size-4.5',
        default => 'size-4',
    };

    // Windows 11 Fluent Kit Visual Variants
    $variantClasses = match ($variant) {
        // Accent Button (Fluent Primary)
        'primary' => 'bg-[var(--color-accent)] text-white border border-black/10 border-b-black/30 dark:border-white/10 dark:border-b-black/40 shadow-[inset_0_1px_0_rgba(255,255,255,0.25),0_1px_2px_rgba(0,0,0,0.15)] hover:brightness-105 hover:shadow-[inset_0_1px_0_rgba(255,255,255,0.3),0_2px_4px_rgba(0,0,0,0.2)] active:brightness-95',
        // Standard Surface Button with elevated bottom border (Fluent Secondary)
        'outline' => 'bg-white/80 hover:bg-neutral-50 active:bg-neutral-100 text-neutral-800 border border-black/[0.12] border-b-black/[0.22] shadow-[0_1px_2px_rgba(0,0,0,0.04)] dark:bg-[#2d2d2d] dark:hover:bg-[#383838] dark:active:bg-[#262626] dark:text-neutral-100 dark:border-white/[0.09] dark:border-b-white/[0.05] dark:shadow-[0_1px_2px_rgba(0,0,0,0.2)]',
        // Filled Surface (Fluent Soft)
        'filled' => 'bg-neutral-200/70 hover:bg-neutral-200 active:bg-neutral-300/80 text-neutral-800 border border-transparent dark:bg-white/[0.08] dark:hover:bg-white/[0.12] dark:active:bg-white/[0.05] dark:text-neutral-200',
        // Critical / Destructive Button
        'danger' => 'bg-[#c42b1c] hover:bg-[#b82717] active:bg-[#a92314] text-white border border-black/10 border-b-black/30 dark:border-white/10 dark:border-b-black/40 shadow-[inset_0_1px_0_rgba(255,255,255,0.2),0_1px_2px_rgba(0,0,0,0.12)]',
        // Subtle Button (Fluent ToolBar / Link)
        'subtle' => 'bg-transparent hover:bg-black/[0.05] active:bg-black/[0.03] text-neutral-500 hover:text-neutral-900 border border-transparent dark:text-neutral-400 dark:hover:text-white dark:hover:bg-white/[0.06] dark:active:bg-white/[0.03]',
        // Ghost Button
        'ghost' => 'bg-transparent hover:bg-black/[0.05] active:bg-black/[0.03] text-neutral-800 border border-transparent dark:hover:bg-white/[0.07] dark:active:bg-white/[0.04] dark:text-neutral-200',
        // White Card Surface
        'white' => 'bg-white hover:bg-neutral-50 active:bg-neutral-100 text-neutral-900 border border-black/10 border-b-black/20 shadow-2xs dark:bg-[#323232] dark:hover:bg-[#3a3a3a] dark:text-white dark:border-white/10',
        default => 'bg-white/80 hover:bg-neutral-50 active:bg-neutral-100 text-neutral-800 border border-black/[0.12] border-b-black/[0.22] shadow-[0_1px_2px_rgba(0,0,0,0.04)] dark:bg-[#2d2d2d] dark:hover:bg-[#383838] dark:text-neutral-100 dark:border-white/[0.09]',
    };

    // Color Theme Overrides
    $colorClasses = match ($color) {
        'emerald' => '[--color-accent:#10b981] !text-white',
        'indigo' => '[--color-accent:#6366f1] !text-white',
        'sky' => '[--color-accent:#0ea5e9] !text-white',
        'amber' => '[--color-accent:#f59e0b] !text-neutral-950',
        'rose' => '[--color-accent:#f43f5e] !text-white',
        'purple' => '[--color-accent:#a855f7] !text-white',
        default => '',
    };

    // Alignment
    $alignClasses = match ($align) {
        'start' => 'justify-start',
        'end' => 'justify-end',
        default => 'justify-center',
    };

    $classes = [
        'relative inline-flex items-center font-sans font-medium text-center whitespace-nowrap select-none cursor-pointer',
        'transition-all duration-100 ease-out active:scale-[0.98]',
        'focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[var(--color-accent,#0067c0)]',
        'disabled:opacity-45 disabled:cursor-not-allowed disabled:pointer-events-none disabled:shadow-none',
        $sizeClasses,
        $variantClasses,
        $colorClasses,
        $alignClasses,
    ];
@endphp

@if ($href)
<a href="{{ $href }}" data-fluent-button="true" {{ $attributes->class($classes) }}>
@else
<button type="{{ $type }}" @disabled($disabled) data-fluent-button="true" {{ $attributes->class($classes) }}>
@endif
    {{-- Windows 11 ProgressRing Loading Spinner --}}
    @if ($loading)
        <svg class="animate-spin {{ $iconSize }} shrink-0 text-current opacity-85" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
            <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    @elseif ($iconLeading)
        <flux:icon :name="$iconLeading" :variant="$iconVariant" class="{{ $iconSize }} shrink-0" />
    @endif

    {{-- Button Label / Slot --}}
    @if (! $slot->isEmpty())
        <span class="truncate">{{ $slot }}</span>
    @endif

    {{-- Trailing Icon --}}
    @if ($iconTrailing)
        <flux:icon :name="$iconTrailing" :variant="$iconVariant" class="{{ $iconSize }} shrink-0" />
    @endif

    {{-- Windows 11 Kbd Shortcut Badge --}}
    @if ($kbd)
        <kbd class="ms-1.5 inline-flex items-center px-1.5 py-0.5 text-[10px] font-sans font-normal tracking-wide rounded-[3px] bg-black/5 dark:bg-white/10 text-neutral-600 dark:text-neutral-300 border border-black/5 dark:border-white/5 select-none shadow-2xs">
            {{ $kbd }}
        </kbd>
    @endif
@if ($href)
</a>
@else
</button>
@endif
