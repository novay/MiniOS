@props([
    'label',
    'active' => false,
    'appId' => null,
])

<div
    @if ($appId)
        wire:key="desktop-dock-{{ $appId }}"
        data-dock-app="{{ $appId }}"
    @endif
    class="group relative flex items-center justify-center transition-all duration-150"
>
    <button
        type="button"
        aria-label="{{ $label }}"
        @if ($appId)
            @click.stop="openApplication(@js($appId))"
            @contextmenu.prevent.stop="openDockContextMenu($event, @js($appId))"
        @endif
        {{ $attributes->except('class') }}
        class="group relative flex items-center justify-center rounded-sm transition duration-150 hover:bg-white/40 active:bg-white/60 dark:hover:bg-white/10 dark:active:bg-white/20 focus:outline-hidden"
        :class="{
            'size-9': (settings?.dock?.size ?? 'medium') === 'small',
            'size-11': (settings?.dock?.size ?? 'medium') === 'medium',
            'size-14': (settings?.dock?.size ?? 'medium') === 'large',
            'bg-white/50 dark:bg-white/15 shadow-xs border border-white/20 dark:border-white/10': @js($appId) ? isWindowFocused(@js($appId)) : applicationsOpen,
        }"
    >
        {{-- Windows 11 Running & Active Indicators --}}
        @if ($appId)
            {{-- Focused Window Indicator (Accent Color Bar) --}}
            <span
                x-cloak
                x-show="(settings?.dock?.show_indicators ?? true) && isWindowRunning(@js($appId)) && isWindowFocused(@js($appId))"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-0"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-0"
                class="absolute transition-all"
                :class="{
                    'right-0 bottom-0 left-0 mx-2.5 h-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'small',
                    'right-0 bottom-0 left-0 mx-3 h-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'medium',
                    'right-0 bottom-0 left-0 mx-3.5 h-1 rounded-sm': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'large',
                    'top-0 bottom-0 left-0 my-2.5 w-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'left' && (settings?.dock?.size ?? 'medium') === 'small',
                    'top-0 bottom-0 left-0 my-3 w-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'left' && (settings?.dock?.size ?? 'medium') === 'medium',
                    'top-0 bottom-0 left-0 my-3.5 w-1 rounded-sm': (settings?.dock?.position ?? 'bottom') === 'left' && (settings?.dock?.size ?? 'medium') === 'large',
                    'top-0 bottom-0 right-0 my-2.5 w-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'right' && (settings?.dock?.size ?? 'medium') === 'small',
                    'top-0 bottom-0 right-0 my-3 w-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'right' && (settings?.dock?.size ?? 'medium') === 'medium',
                    'top-0 bottom-0 right-0 my-3.5 w-1 rounded-sm': (settings?.dock?.position ?? 'bottom') === 'right' && (settings?.dock?.size ?? 'medium') === 'large',
                }"
                :style="'background-color: var(--accent-color, #0078d4)'"
            ></span>

            {{-- Running but Unfocused Window Indicator (Subtle Dot/Pill) --}}
            <span
                x-cloak
                x-show="(settings?.dock?.show_indicators ?? true) && isWindowRunning(@js($appId)) && !isWindowFocused(@js($appId))"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-0"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-0"
                class="absolute transition-all"
                :class="{
                    'bottom-0 left-1/2 -translate-x-1/2 w-1.5 h-[3px] rounded-sm bg-neutral-400 dark:bg-neutral-500': (settings?.dock?.position ?? 'bottom') === 'bottom',
                    'left-0 top-1/2 -translate-y-1/2 h-1.5 w-[3px] rounded-sm bg-neutral-400 dark:bg-neutral-500': (settings?.dock?.position ?? 'bottom') === 'left',
                    'right-0 top-1/2 -translate-y-1/2 h-1.5 w-[3px] rounded-sm bg-neutral-400 dark:bg-neutral-500': (settings?.dock?.position ?? 'bottom') === 'right',
                }"
            ></span>
        @else
            {{-- Start Menu Indicator --}}
            <span
                x-cloak
                x-show="applicationsOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-0"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-0"
                class="absolute transition-all"
                :class="{
                    'right-0 bottom-0 left-0 mx-2.5 h-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'small',
                    'right-0 bottom-0 left-0 mx-3 h-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'medium',
                    'right-0 bottom-0 left-0 mx-3.5 h-1 rounded-sm': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'large',
                    'top-0 bottom-0 left-0 my-2.5 w-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'left' && (settings?.dock?.size ?? 'medium') === 'small',
                    'top-0 bottom-0 left-0 my-3 w-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'left' && (settings?.dock?.size ?? 'medium') === 'medium',
                    'top-0 bottom-0 left-0 my-3.5 w-1 rounded-sm': (settings?.dock?.position ?? 'bottom') === 'left' && (settings?.dock?.size ?? 'medium') === 'large',
                    'top-0 bottom-0 right-0 my-2.5 w-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'right' && (settings?.dock?.size ?? 'medium') === 'small',
                    'top-0 bottom-0 right-0 my-3 w-[3px] rounded-sm': (settings?.dock?.position ?? 'bottom') === 'right' && (settings?.dock?.size ?? 'medium') === 'medium',
                    'top-0 bottom-0 right-0 my-3.5 w-1 rounded-sm': (settings?.dock?.position ?? 'bottom') === 'right' && (settings?.dock?.size ?? 'medium') === 'large',
                }"
                :style="'background-color: var(--accent-color, #0078d4)'"
            ></span>
        @endif

        {{-- Icon Container with dynamic size cascade and tactile click --}}
        <span
            class="block transition duration-75 active:scale-90 flex items-center justify-center pointer-events-none [&>svg]:transition-all [&>svg]:duration-200 [&>img]:transition-all [&>img]:duration-200 [&>div]:transition-all [&>div]:duration-200"
            :class="{
                'p-0.5 [&>svg]:size-6 [&>img]:size-6 [&>div]:size-6': (settings?.dock?.size ?? 'medium') === 'small',
                'p-1 [&>svg]:size-9 [&>img]:size-9 [&>div]:size-9': (settings?.dock?.size ?? 'medium') === 'medium',
                'p-0.5 [&>svg]:size-11 [&>img]:size-11 [&>div]:size-11': (settings?.dock?.size ?? 'medium') === 'large',
            }"
        >
            {{ $slot }}
        </span>
    </button>

    {{-- Windows 11 Flyout Tooltip --}}
    <div
        class="pointer-events-none absolute z-[100] whitespace-nowrap rounded-md border border-neutral-200/80 dark:border-white/10 bg-white/95 dark:bg-[#2b2b2b]/95 px-2.5 py-1 text-[11px] font-normal text-neutral-800 dark:text-neutral-100 opacity-0 shadow-lg backdrop-blur-md transition-opacity duration-150 group-hover:opacity-100 select-none"
        :class="{
            '-top-9 left-1/2 -translate-x-1/2': (settings?.dock?.position ?? 'bottom') === 'bottom',
            'left-full ml-3 top-1/2 -translate-y-1/2': (settings?.dock?.position ?? 'bottom') === 'left',
            'right-full mr-3 top-1/2 -translate-y-1/2': (settings?.dock?.position ?? 'bottom') === 'right',
        }"
    >
        {{ $label }}
    </div>
</div>
