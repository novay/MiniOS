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

    class="group relative flex items-center justify-center transition-all duration-200"
    :class="{
        'h-11 w-11': (settings?.dock?.size ?? 'medium') === 'small',
        'h-14 w-14': (settings?.dock?.size ?? 'medium') === 'medium',
        'h-[68px] w-[68px]': (settings?.dock?.size ?? 'medium') === 'large',
    }"
>
    @if ($active)
        <div class="absolute left-0 h-7 w-[3px] rounded-r-full bg-[#e95420]"></div>
    @endif

    <button
        type="button"
        aria-label="{{ $label }}"
        @if ($appId)
            @click.stop="openApplication(@js($appId))"
            @contextmenu.prevent.stop="openDockContextMenu($event, @js($appId))"
        @endif
        {{ $attributes->except('class') }}
        class="relative flex items-center justify-center transition-all duration-150 hover:scale-110 active:scale-95"
        :class="{
            'size-9 rounded-lg': (settings?.dock?.size ?? 'medium') === 'small',
            'size-11 rounded-xl': (settings?.dock?.size ?? 'medium') === 'medium',
            'size-[56px] rounded-2xl': (settings?.dock?.size ?? 'medium') === 'large',
            'bg-white/15': @js($appId) ? isWindowFocused(@js($appId)) : applicationsOpen,
        }"
    >
        {{ $slot }}
    </button>

    {{-- Tooltip --}}
    <div
        class="pointer-events-none absolute z-[100] whitespace-nowrap rounded-md bg-neutral-950/95 px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-xl transition-opacity duration-150 group-hover:opacity-100"
        :class="{
            '-top-10 left-1/2 -translate-x-1/2': (settings?.dock?.position ?? 'bottom') === 'bottom',
            'left-full ml-3 top-1/2 -translate-y-1/2': (settings?.dock?.position ?? 'bottom') === 'left',
            'right-full mr-3 top-1/2 -translate-y-1/2': (settings?.dock?.position ?? 'bottom') === 'right',
        }"
    >
        {{ $label }}

        <div
            class="absolute size-2 rotate-45 bg-neutral-950"
            :class="{
                '-bottom-1 left-1/2 -translate-x-1/2': (settings?.dock?.position ?? 'bottom') === 'bottom',
                '-left-1 top-1/2 -translate-y-1/2': (settings?.dock?.position ?? 'bottom') === 'left',
                '-right-1 top-1/2 -translate-y-1/2': (settings?.dock?.position ?? 'bottom') === 'right',
            }"
        ></div>
    </div>
</div>
