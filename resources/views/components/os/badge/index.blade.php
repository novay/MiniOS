@props([
    'variant' => null,
    'rounded' => false,
    'color' => 'zinc',
    'inset' => null,
    'size' => 'md',
    'icon' => null,
    'iconTrailing' => null,
    'iconVariant' => 'micro',
    'as' => 'div',
    'href' => null,
    'label' => null,
])

@php
    $iconTrailing = $attributes->pluck('icon:trailing') ?? $iconTrailing;
    $iconVariant = $attributes->pluck('icon:variant') ?? $iconVariant;

    if ($variant === 'pill') {
        $rounded = true;
        $variant = null;
    }

    $insetClasses = '';
    if ($inset) {
        $insets = is_string($inset) ? explode(' ', $inset) : [];
        if (in_array('top', $insets)) $insetClasses .= ' -mt-1';
        if (in_array('bottom', $insets)) $insetClasses .= ' -mb-1';
        if (in_array('left', $insets)) $insetClasses .= ' -ms-2';
        if (in_array('right', $insets)) $insetClasses .= ' -me-2';
    }

    $sizeClasses = match ($size) {
        'lg' => 'text-sm py-1.5 px-3 gap-2',
        'sm' => 'text-xs py-0.5 px-2 gap-1',
        default => 'text-xs sm:text-[13px] py-1 px-2.5 gap-1.5',
    };

    $iconSizeClasses = match ($size) {
        'lg' => 'size-4',
        'sm' => 'size-3',
        default => ($iconVariant === 'outline' ? 'size-4' : 'size-3.5'),
    };

    $radiusClasses = $rounded ? 'rounded-full' : match ($size) {
        'lg' => 'rounded-lg',
        default => 'rounded-md',
    };

    $colorClasses = $variant === 'solid' ? match ($color) {
        'red' => 'text-white bg-red-500 dark:bg-red-600 [&:is(button)]:hover:bg-red-600 dark:[button]:hover:bg-red-500',
        'orange' => 'text-white bg-orange-500 dark:bg-orange-600 [&:is(button)]:hover:bg-orange-600 dark:[button]:hover:bg-orange-500',
        'amber' => 'text-white dark:text-zinc-950 bg-amber-500 dark:bg-amber-500 [&:is(button)]:hover:bg-amber-600 dark:[button]:hover:bg-amber-400',
        'yellow' => 'text-white dark:text-zinc-950 bg-yellow-500 dark:bg-yellow-400 [&:is(button)]:hover:bg-yellow-600 dark:[button]:hover:bg-yellow-300',
        'lime' => 'text-white bg-lime-500 dark:bg-lime-600 [&:is(button)]:hover:bg-lime-600 dark:[button]:hover:bg-lime-500',
        'green' => 'text-white bg-green-500 dark:bg-green-600 [&:is(button)]:hover:bg-green-600 dark:[button]:hover:bg-green-500',
        'emerald' => 'text-white bg-emerald-500 dark:bg-emerald-600 [&:is(button)]:hover:bg-emerald-600 dark:[button]:hover:bg-emerald-500',
        'teal' => 'text-white bg-teal-500 dark:bg-teal-600 [&:is(button)]:hover:bg-teal-600 dark:[button]:hover:bg-teal-500',
        'cyan' => 'text-white bg-cyan-500 dark:bg-cyan-600 [&:is(button)]:hover:bg-cyan-600 dark:[button]:hover:bg-cyan-500',
        'sky' => 'text-white bg-sky-500 dark:bg-sky-600 [&:is(button)]:hover:bg-sky-600 dark:[button]:hover:bg-sky-500',
        'blue' => 'text-white bg-blue-500 dark:bg-blue-600 [&:is(button)]:hover:bg-blue-600 dark:[button]:hover:bg-blue-500',
        'indigo' => 'text-white bg-indigo-500 dark:bg-indigo-600 [&:is(button)]:hover:bg-indigo-600 dark:[button]:hover:bg-indigo-500',
        'violet' => 'text-white bg-violet-500 dark:bg-violet-600 [&:is(button)]:hover:bg-violet-600 dark:[button]:hover:bg-violet-500',
        'purple' => 'text-white bg-purple-500 dark:bg-purple-600 [&:is(button)]:hover:bg-purple-600 dark:[button]:hover:bg-purple-500',
        'fuchsia' => 'text-white bg-fuchsia-500 dark:bg-fuchsia-600 [&:is(button)]:hover:bg-fuchsia-600 dark:[button]:hover:bg-fuchsia-500',
        'pink' => 'text-white bg-pink-500 dark:bg-pink-600 [&:is(button)]:hover:bg-pink-600 dark:[button]:hover:bg-pink-500',
        'rose' => 'text-white bg-rose-500 dark:bg-rose-600 [&:is(button)]:hover:bg-rose-600 dark:[button]:hover:bg-rose-500',
        default => 'text-white bg-zinc-600 dark:bg-zinc-600 [&:is(button)]:hover:bg-zinc-700 dark:[button]:hover:bg-zinc-500',
    } : match ($color) {
        'red' => 'text-red-700 dark:text-red-300 bg-red-400/20 dark:bg-red-400/25 border border-red-500/20 dark:border-red-400/25 [&:is(button)]:hover:bg-red-400/30 dark:[button]:hover:bg-red-400/35',
        'orange' => 'text-orange-700 dark:text-orange-300 bg-orange-400/20 dark:bg-orange-400/25 border border-orange-500/20 dark:border-orange-400/25 [&:is(button)]:hover:bg-orange-400/30 dark:[button]:hover:bg-orange-400/35',
        'amber' => 'text-amber-800 dark:text-amber-300 bg-amber-400/25 dark:bg-amber-400/25 border border-amber-500/25 dark:border-amber-400/25 [&:is(button)]:hover:bg-amber-400/35 dark:[button]:hover:bg-amber-400/35',
        'yellow' => 'text-yellow-800 dark:text-yellow-300 bg-yellow-400/25 dark:bg-yellow-400/25 border border-yellow-500/25 dark:border-yellow-400/25 [&:is(button)]:hover:bg-yellow-400/35 dark:[button]:hover:bg-yellow-400/35',
        'lime' => 'text-lime-800 dark:text-lime-300 bg-lime-400/25 dark:bg-lime-400/25 border border-lime-500/25 dark:border-lime-400/25 [&:is(button)]:hover:bg-lime-400/35 dark:[button]:hover:bg-lime-400/35',
        'green' => 'text-green-800 dark:text-green-300 bg-green-400/20 dark:bg-green-400/25 border border-green-500/20 dark:border-green-400/25 [&:is(button)]:hover:bg-green-400/30 dark:[button]:hover:bg-green-400/35',
        'emerald' => 'text-emerald-800 dark:text-emerald-300 bg-emerald-400/20 dark:bg-emerald-400/25 border border-emerald-500/20 dark:border-emerald-400/25 [&:is(button)]:hover:bg-emerald-400/30 dark:[button]:hover:bg-emerald-400/35',
        'teal' => 'text-teal-800 dark:text-teal-300 bg-teal-400/20 dark:bg-teal-400/25 border border-teal-500/20 dark:border-teal-400/25 [&:is(button)]:hover:bg-teal-400/30 dark:[button]:hover:bg-teal-400/35',
        'cyan' => 'text-cyan-800 dark:text-cyan-300 bg-cyan-400/20 dark:bg-cyan-400/25 border border-cyan-500/20 dark:border-cyan-400/25 [&:is(button)]:hover:bg-cyan-400/30 dark:[button]:hover:bg-cyan-400/35',
        'sky' => 'text-sky-800 dark:text-sky-300 bg-sky-400/20 dark:bg-sky-400/25 border border-sky-500/20 dark:border-sky-400/25 [&:is(button)]:hover:bg-sky-400/30 dark:[button]:hover:bg-sky-400/35',
        'blue' => 'text-blue-800 dark:text-blue-300 bg-blue-400/20 dark:bg-blue-400/25 border border-blue-500/20 dark:border-blue-400/25 [&:is(button)]:hover:bg-blue-400/30 dark:[button]:hover:bg-blue-400/35',
        'indigo' => 'text-indigo-700 dark:text-indigo-300 bg-indigo-400/20 dark:bg-indigo-400/25 border border-indigo-500/20 dark:border-indigo-400/25 [&:is(button)]:hover:bg-indigo-400/30 dark:[button]:hover:bg-indigo-400/35',
        'violet' => 'text-violet-700 dark:text-violet-300 bg-violet-400/20 dark:bg-violet-400/25 border border-violet-500/20 dark:border-violet-400/25 [&:is(button)]:hover:bg-violet-400/30 dark:[button]:hover:bg-violet-400/35',
        'purple' => 'text-purple-700 dark:text-purple-300 bg-purple-400/20 dark:bg-purple-400/25 border border-purple-500/20 dark:border-purple-400/25 [&:is(button)]:hover:bg-purple-400/30 dark:[button]:hover:bg-purple-400/35',
        'fuchsia' => 'text-fuchsia-700 dark:text-fuchsia-300 bg-fuchsia-400/20 dark:bg-fuchsia-400/25 border border-fuchsia-500/20 dark:border-fuchsia-400/25 [&:is(button)]:hover:bg-fuchsia-400/30 dark:[button]:hover:bg-fuchsia-400/35',
        'pink' => 'text-pink-700 dark:text-pink-300 bg-pink-400/20 dark:bg-pink-400/25 border border-pink-500/20 dark:border-pink-400/25 [&:is(button)]:hover:bg-pink-400/30 dark:[button]:hover:bg-pink-400/35',
        'rose' => 'text-rose-700 dark:text-rose-300 bg-rose-400/20 dark:bg-rose-400/25 border border-rose-500/20 dark:border-rose-400/25 [&:is(button)]:hover:bg-rose-400/30 dark:[button]:hover:bg-rose-400/35',
        default => 'text-zinc-700 dark:text-zinc-200 bg-zinc-400/15 dark:bg-zinc-400/30 border border-zinc-500/15 dark:border-zinc-400/20 [&:is(button)]:hover:bg-zinc-400/25 dark:[button]:hover:bg-zinc-400/40',
    };

    $isInteractive = $href !== null || $as === 'button';

    $classes = [
        'inline-flex items-center font-medium whitespace-nowrap select-none shrink-0 transition-colors',
        '[print-color-adjust:exact]',
        $sizeClasses,
        $radiusClasses,
        $colorClasses,
        $insetClasses,
        $isInteractive ? 'cursor-pointer transition-all active:scale-[0.98] hover:brightness-105 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-[var(--color-accent,#0067c0)]' : '',
    ];

    $elementAttributes = $attributes->class($classes)->merge([
        'data-slot' => 'badge',
        'data-flux-badge' => 'true',
        'data-os-badge' => 'true',
    ]);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $elementAttributes }}>
        @if (is_string($icon) && $icon !== '')
            <flux:icon :name="$icon" :variant="$iconVariant" class="{{ $iconSizeClasses }} shrink-0" data-flux-badge-icon />
        @elseif ($icon)
            {{ $icon }}
        @endif

        {{ $slot->isEmpty() ? $label : $slot }}

        @if (is_string($iconTrailing) && $iconTrailing !== '')
            <flux:icon :name="$iconTrailing" :variant="$iconVariant" class="{{ $iconSizeClasses }} shrink-0" data-flux-badge-icon:trailing />
        @elseif ($iconTrailing)
            {{ $iconTrailing }}
        @endif
    </a>
@elseif ($as === 'button')
    <button type="button" {{ $elementAttributes }}>
        @if (is_string($icon) && $icon !== '')
            <flux:icon :name="$icon" :variant="$iconVariant" class="{{ $iconSizeClasses }} shrink-0" data-flux-badge-icon />
        @elseif ($icon)
            {{ $icon }}
        @endif

        {{ $slot->isEmpty() ? $label : $slot }}

        @if (is_string($iconTrailing) && $iconTrailing !== '')
            <flux:icon :name="$iconTrailing" :variant="$iconVariant" class="{{ $iconSizeClasses }} shrink-0" data-flux-badge-icon:trailing />
        @elseif ($iconTrailing)
            {{ $iconTrailing }}
        @endif
    </button>
@else
    <div {{ $elementAttributes }}>
        @if (is_string($icon) && $icon !== '')
            <flux:icon :name="$icon" :variant="$iconVariant" class="{{ $iconSizeClasses }} shrink-0" data-flux-badge-icon />
        @elseif ($icon)
            {{ $icon }}
        @endif

        {{ $slot->isEmpty() ? $label : $slot }}

        @if (is_string($iconTrailing) && $iconTrailing !== '')
            <flux:icon :name="$iconTrailing" :variant="$iconVariant" class="{{ $iconSizeClasses }} shrink-0" data-flux-badge-icon:trailing />
        @elseif ($iconTrailing)
            {{ $iconTrailing }}
        @endif
    </div>
@endif
