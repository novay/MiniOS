@props([
    'resizable' => true,
    'collapsedWidth' => 'w-16',
])

<div
    style="grid-area: sidebar;"
    class="relative flex h-full shrink-0 min-h-0 select-none overflow-hidden"
>
    <aside
        :class="[
            sidebarCollapsed ? '{{ $collapsedWidth }} p-2' : 'p-3.5',
            isResizing ? '!transition-none' : 'transition-[width,padding] duration-200 ease-out'
        ]"
        :style="!sidebarCollapsed ? ('width: ' + sidebarWidth + 'px') : ''"
        {{ $attributes->merge([
            'class' => 'flex shrink-0 flex-col ' . ($resizable ? '' : 'border-r border-neutral-200/90 dark:border-white/5') . ' bg-[#f8f8f8]/85 dark:bg-[#202020]/90 backdrop-blur-xl relative select-none h-full overflow-hidden'
        ]) }}
    >
        {{ $slot }}
    </aside>

    @if ($resizable)
        {{-- Draggable Resizer Divider Handle (Sleek 1px line, zero layout shift) --}}
        <div
            @mousedown.prevent="startResize($event)"
            @dblclick="toggleSidebar()"
            class="group relative w-px shrink-0 cursor-col-resize select-none bg-neutral-200/90 dark:bg-white/10 hover:bg-neutral-400 dark:hover:bg-neutral-500 active:bg-[var(--accent-color,{{ $accent['hex'] ?? '#3b82f6' }})] transition-colors z-20"
            :title="sidebarCollapsed ? '{{ __('Klik dua kali untuk membuka sidebar') }}' : '{{ __('Geser untuk mengatur lebar sidebar (klik 2x untuk toggle)') }}'"
        >
            {{-- Invisible expanded hit area for effortless grabbing --}}
            <div class="absolute inset-y-0 -left-1.5 -right-1.5 cursor-col-resize"></div>
        </div>
    @endif
</div>
