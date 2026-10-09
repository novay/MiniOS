@props([
    'icon' => null,
    'shortcut' => null,
    'disabled' => false,
    'danger' => false,
])

@php
    $baseClass = "group flex w-full items-center justify-between gap-3 px-2 py-1.5 rounded text-left transition-colors cursor-pointer select-none text-xs ";
    if ($disabled) {
        $stateClass = "opacity-40 cursor-not-allowed pointer-events-none text-neutral-400 dark:text-neutral-500 ";
    } elseif ($danger) {
        $stateClass = "text-rose-600 dark:text-rose-400 hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white ";
    } else {
        $stateClass = "text-neutral-700 dark:text-neutral-200 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 dark:hover:text-white ";
    }
@endphp

@if ($attributes->has('href'))
    <a
        {{ $attributes->merge(['class' => $baseClass . $stateClass]) }}
        @click="closeMenu()"
    >
        <div class="flex items-center gap-2 truncate">
            @if ($icon)
                <flux:icon :name="$icon" class="size-3.5 shrink-0 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
            @else
                <span class="w-3.5 shrink-0"></span>
            @endif
            <span class="truncate">{{ $slot }}</span>
        </div>
        @if ($shortcut)
            <span class="ml-auto pl-3 text-[10px] font-mono tracking-tight text-neutral-400 dark:text-neutral-500 group-hover:text-white/90">
                {{ $shortcut }}
            </span>
        @endif
    </a>
@else
    <button
        type="button"
        {{ $attributes->merge(['class' => $baseClass . $stateClass]) }}
        @if(!$disabled) @click="closeMenu()" @endif
        @if($disabled) disabled @endif
    >
        <div class="flex items-center gap-2 truncate">
            @if ($icon)
                <flux:icon :name="$icon" class="size-3.5 shrink-0 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
            @else
                <span class="w-3.5 shrink-0"></span>
            @endif
            <span class="truncate">{{ $slot }}</span>
        </div>
        @if ($shortcut)
            <span class="ml-auto pl-3 text-[10px] font-mono tracking-tight text-neutral-400 dark:text-neutral-500 group-hover:text-white/90">
                {{ $shortcut }}
            </span>
        @endif
    </button>
@endif
