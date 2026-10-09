@props([
    'checked' => false,
    'shortcut' => null,
    'disabled' => false,
])

<button
    type="button"
    {{ $attributes->merge(['class' => 'group flex w-full items-center justify-between gap-3 px-2 py-1.5 rounded text-left transition-colors cursor-pointer select-none text-xs ' . ($disabled ? 'opacity-40 cursor-not-allowed pointer-events-none text-neutral-400 dark:text-neutral-500' : 'text-neutral-700 dark:text-neutral-200 hover:bg-blue-600 hover:text-white dark:hover:bg-blue-600 dark:hover:text-white')]) }}
    @if(!$disabled) @click="closeMenu()" @endif
    @if($disabled) disabled @endif
>
    <div class="flex items-center gap-2 truncate">
        @if ($checked)
            <span class="flex size-3.5 items-center justify-center shrink-0">
                <span class="size-1.5 rounded-full bg-blue-600 dark:bg-blue-400 group-hover:bg-white"></span>
            </span>
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
