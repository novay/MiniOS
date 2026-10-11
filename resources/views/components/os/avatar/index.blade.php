@props([
    'src' => null,
    'name' => null,
    'initials' => null,
    'tooltip' => null,
    'tooltipPosition' => 'top',
    'circle' => false,
    'color' => null,
    'badge' => null,
    'icon' => null,
    'iconVariant' => 'solid',
    'size' => 'md',
    'href' => null,
    'alt' => null,
    'as' => 'div',
])

@php
    $singleInitial = (bool) $attributes->pluck('initials:single');
    $colorSeed = $attributes->pluck('color:seed');
    $badgeColor = $attributes->pluck('badge:color');
    $badgeCircle = (bool) $attributes->pluck('badge:circle');
    $badgePosition = $attributes->pluck('badge:position') ?? 'bottom right';
    $badgeVariant = $attributes->pluck('badge:variant');
    $iconVariant = $attributes->pluck('icon:variant') ?? $iconVariant;
    $tooltip = $attributes->pluck('tooltip') ?? $tooltip;
    $tooltipPosition = $attributes->pluck('tooltip:position') ?? $tooltipPosition;

    // Automatic initials extraction from name
    if ($name && ! $initials) {
        $parts = explode(' ', trim($name));

        if ($singleInitial) {
            $initials = strtoupper(mb_substr($parts[0], 0, 1));
        } else {
            $parts = collect($parts)->filter()->values()->all();

            if (count($parts) > 1) {
                $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1));
            } elseif (count($parts) === 1) {
                $initials = strtoupper(mb_substr($parts[0], 0, 1)) . (mb_strlen($parts[0]) > 1 ? strtolower(mb_substr($parts[0], 1, 1)) : '');
            }
        }
    }

    // Default tooltip from name if tooltip="true" (boolean attribute)
    if ($name && $tooltip === true) {
        $tooltip = $name;
    }

    $hasTextContent = $icon !== null || $initials !== null || $slot->isNotEmpty();

    // Default fallback icon if nothing provided
    if (! $src && ! $hasTextContent) {
        $icon = 'user';
        $hasTextContent = true;
    }

    // Microsoft Fluent 2 Persona Color Palette
    $fluentColors = ['red', 'orange', 'amber', 'yellow', 'lime', 'green', 'emerald', 'teal', 'cyan', 'sky', 'blue', 'indigo', 'violet', 'purple', 'fuchsia', 'pink', 'rose'];

    if ($hasTextContent && $color === 'auto') {
        $seed = $colorSeed ?? $name ?? $icon ?? $initials ?? (string) $slot;
        $hash = crc32((string) $seed);
        $color = $fluentColors[abs($hash) % count($fluentColors)];
    }

    // Dimensions based on size
    $sizeClasses = match ($size) {
        'xl' => 'size-16 text-lg',
        'lg' => 'size-12 text-base',
        'sm' => 'size-8 text-xs',
        'xs' => 'size-6 text-[10px]',
        default => 'size-10 text-sm',
    };

    // Windows 11 Fluent Corner Radii
    $radiusClass = $circle ? 'rounded-full' : match ($size) {
        'xl' => 'rounded-2xl',
        'lg' => 'rounded-xl',
        'sm' => 'rounded-md',
        'xs' => 'rounded-[4px]',
        default => 'rounded-lg',
    };

    // Windows 11 Fluent Persona Saturated & Acrylic Fills
    $colorClass = match ($color) {
        'red' => 'bg-[#d13438] text-white',
        'orange' => 'bg-[#ca5010] text-white',
        'amber' => 'bg-[#f59e0b] text-neutral-950',
        'yellow' => 'bg-[#f9e04c] text-neutral-950',
        'lime' => 'bg-[#107c10] text-white',
        'green', 'emerald' => 'bg-[#0e700e] text-white',
        'teal' => 'bg-[#008272] text-white',
        'cyan', 'sky' => 'bg-[#0078d4] text-white',
        'blue' => 'bg-[#004e8c] text-white',
        'indigo' => 'bg-[#4f6bed] text-white',
        'violet' => 'bg-[#5c2d91] text-white',
        'purple' => 'bg-[#8764b8] text-white',
        'fuchsia' => 'bg-[#881798] text-white',
        'pink' => 'bg-[#e3008c] text-white',
        'rose' => 'bg-[#c239b3] text-white',
        'zinc', 'gray' => 'bg-neutral-200 text-neutral-800 dark:bg-[#323232] dark:text-neutral-200',
        default => ($hasTextContent
            ? 'bg-neutral-200/90 text-neutral-700 dark:bg-[#2d2d2d] dark:text-neutral-200'
            : 'bg-transparent'),
    };

    // Fluent Icon Size
    $iconSizeClass = match ($size) {
        'xl' => 'size-8',
        'lg' => 'size-6',
        'sm' => 'size-4',
        'xs' => 'size-3.5',
        default => 'size-5',
    };

    // Windows 11 Fluent Presence Status Colors
    $badgeColorClass = match ($badgeColor) {
        'red', 'rose' => 'bg-[#c42b1c] text-white',
        'amber', 'yellow' => 'bg-[#ffaa44] text-neutral-950',
        'emerald', 'green' => 'bg-[#107c10] text-white',
        'sky', 'blue' => 'bg-[#0078d4] text-white',
        'purple', 'indigo' => 'bg-[#8764b8] text-white',
        'zinc', 'gray' => 'bg-[#797775] text-white',
        default => 'bg-[#107c10] text-white',
    };

    $badgeSizeClass = match ($size) {
        'xs' => 'size-2 min-w-2',
        'sm' => 'size-2.5 min-w-2.5',
        'lg' => 'size-3.5 min-w-3.5',
        'xl' => 'size-4.5 min-w-4.5',
        default => 'size-3 min-w-3',
    };

    // Fluent Badge Cutout Positioning
    $badgePositionClass = match ($badgePosition) {
        'top left' => '-top-0.5 -left-0.5',
        'top right' => '-top-0.5 -right-0.5',
        'bottom left' => '-bottom-0.5 -left-0.5',
        default => '-bottom-0.5 -right-0.5',
    };

    if ($circle) {
        $badgePositionClass = match ($badgePosition) {
            'top left' => 'top-0 left-0',
            'top right' => 'top-0 right-0',
            'bottom left' => 'bottom-0 left-0',
            default => 'bottom-0 right-0',
        };
    }

    $isInteractive = $href !== null || $as === 'button';

    $classes = [
        // Base Fluent Persona Layout
        'relative isolate flex-none inline-flex items-center justify-center font-sans font-semibold tracking-tight select-none overflow-visible',
        'ring-1 ring-black/[0.08] dark:ring-white/[0.12] shadow-[0_1px_2px_rgba(0,0,0,0.06)]',
        $sizeClasses,
        $radiusClass,
        $colorClass,
        // Fluent Interactive Feedback
        $isInteractive ? 'cursor-pointer transition-all duration-150 ease-out active:scale-[0.96] hover:brightness-105 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[var(--color-accent,#0067c0)]' : '',
    ];

    $elementAttributes = $attributes->class($classes)->merge([
        'data-slot' => 'avatar',
        'data-flux-avatar' => 'true',
        'data-os-avatar' => 'true',
        'data-fluent-persona' => 'true',
        'data-size' => $size,
    ]);

    if ($circle) {
        $elementAttributes = $elementAttributes->merge(['data-circle' => 'true']);
    }
@endphp

@if ($tooltip)
    <os:tooltip :content="$tooltip" :position="$tooltipPosition" class="inline-flex">
        @if ($href)
            <a href="{{ $href }}" {{ $elementAttributes }}>
        @elseif ($as === 'button')
            <button type="button" {{ $elementAttributes }}>
        @else
            <div {{ $elementAttributes }}>
        @endif
            @if ($src)
                <img src="{{ $src }}" alt="{{ $alt ?? $name }}" class="size-full object-cover {{ $radiusClass }}">
            @elseif ($icon)
                <flux:icon :name="$icon" :variant="$iconVariant" class="{{ $iconSizeClass }} opacity-90" />
            @elseif ($hasTextContent)
                <span class="leading-none select-none">{{ $initials ?? $slot }}</span>
            @endif

            @if ($badge instanceof \Illuminate\View\ComponentSlot)
                <div {{ $badge->attributes->class(['absolute z-10 flex items-center justify-center font-bold tabular-nums leading-none ring-2 ring-white dark:ring-[#202020] shadow-xs', $badgeSizeClass, $badgePositionClass, $badgeCircle ? 'rounded-full' : 'rounded-[3px]', $badgeColorClass]) }} aria-hidden="true">{{ $badge }}</div>
            @elseif ($badge)
                @php $badgeIsText = is_string($badge) && $badge !== ''; @endphp
                <div class="absolute z-10 flex items-center justify-center font-bold tabular-nums leading-none ring-2 ring-white dark:ring-[#202020] shadow-xs {{ $badgeIsText ? 'px-1.5 py-0.5 text-[9px] min-w-3.5 h-3.5' : $badgeSizeClass }} {{ $badgePositionClass }} {{ $badgeCircle || ! $badgeIsText ? 'rounded-full' : 'rounded-full' }} {{ $badgeColorClass }}" aria-hidden="true">
                    {{ $badgeIsText ? $badge : '' }}
                </div>
            @endif
        @if ($href)
            </a>
        @elseif ($as === 'button')
            </button>
        @else
            </div>
        @endif
    </os:tooltip>
@else
    @if ($href)
        <a href="{{ $href }}" {{ $elementAttributes }}>
    @elseif ($as === 'button')
        <button type="button" {{ $elementAttributes }}>
    @else
        <div {{ $elementAttributes }}>
    @endif
        @if ($src)
            <img src="{{ $src }}" alt="{{ $alt ?? $name }}" class="size-full object-cover {{ $radiusClass }}">
        @elseif ($icon)
            <flux:icon :name="$icon" :variant="$iconVariant" class="{{ $iconSizeClass }} opacity-90" />
        @elseif ($hasTextContent)
            <span class="leading-none select-none">{{ $initials ?? $slot }}</span>
        @endif

        @if ($badge instanceof \Illuminate\View\ComponentSlot)
            <div {{ $badge->attributes->class(['absolute z-10 flex items-center justify-center font-bold tabular-nums leading-none ring-2 ring-white dark:ring-[#202020] shadow-xs', $badgeSizeClass, $badgePositionClass, $badgeCircle ? 'rounded-full' : 'rounded-[3px]', $badgeColorClass]) }} aria-hidden="true">{{ $badge }}</div>
        @elseif ($badge)
            @php $badgeIsText = is_string($badge) && $badge !== ''; @endphp
            <div class="absolute z-10 flex items-center justify-center font-bold tabular-nums leading-none ring-2 ring-white dark:ring-[#202020] shadow-xs {{ $badgeIsText ? 'px-1.5 py-0.5 text-[9px] min-w-3.5 h-3.5' : $badgeSizeClass }} {{ $badgePositionClass }} {{ $badgeCircle || ! $badgeIsText ? 'rounded-full' : 'rounded-full' }} {{ $badgeColorClass }}" aria-hidden="true">
                {{ $badgeIsText ? $badge : '' }}
            </div>
        @endif
    @if ($href)
        </a>
    @elseif ($as === 'button')
        </button>
    @else
        </div>
    @endif
@endif
