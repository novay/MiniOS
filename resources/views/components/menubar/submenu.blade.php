@props([
    'label' => '',
    'icon' => null,
    'disabled' => false,
])

<div
    class="relative"
    x-data="{ openSub: false }"
    @mouseenter="if (!{{ $disabled ? 'true' : 'false' }}) openSub = true"
    @mouseleave="openSub = false"
>
    <button
        type="button"
        {{ $attributes->merge(['class' => 'group flex w-full items-center justify-between gap-3 px-2 py-1.5 rounded text-left transition-colors cursor-pointer select-none text-xs ' . ($disabled ? 'opacity-40 cursor-not-allowed pointer-events-none text-neutral-400' : 'text-neutral-700 dark:text-neutral-200 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 dark:hover:text-white')]) }}
        @if($disabled) disabled @endif
    >
        <div class="flex items-center gap-2 truncate">
            @if ($icon)
                <flux:icon :name="$icon" class="size-3.5 shrink-0 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
            @else
                <span class="w-3.5 shrink-0"></span>
            @endif
            <span class="truncate">{{ $label }}</span>
        </div>
        <flux:icon name="chevron-right" class="size-3 shrink-0 text-neutral-400 group-hover:text-white" />
    </button>

    <div
        x-cloak
        x-show="openSub"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95 -translate-x-1"
        x-transition:enter-end="opacity-100 scale-100 translate-x-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100 translate-x-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-x-1"
        class="absolute left-full top-0 ml-0.5 z-50 min-w-[200px] rounded-lg border border-black/10 dark:border-white/10 bg-white/95 dark:bg-[#1e1e1e]/95 p-1 text-xs text-neutral-800 dark:text-neutral-200 shadow-2xl backdrop-blur-2xl"
        @click.stop
    >
        {{ $slot }}
    </div>
</div>
