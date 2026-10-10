@props([
    'label' => '',
    'description' => null,
    'icon' => null,
    'iconColor' => null,
    'iconVariant' => 'solid',
    'badge' => null,
    'badgeColor' => null,
    'active' => false,
])

@php
    $finalIconColor = $attributes->get('icon:color') ?? $attributes->get('icon-color') ?? $iconColor ?? 'text-neutral-500';
    $searchTarget = strtolower($label . ' ' . ($description ?? ''));
@endphp

<button
    x-data="{
        tooltipHover: false,
        tooltipTop: 0,
        tooltipLeft: 0,
        showTooltip() {
            if (!sidebarCollapsed) {
                this.tooltipHover = false;
                return;
            }
            const rect = this.$el.getBoundingClientRect();
            this.tooltipTop = rect.top + (rect.height / 2);
            this.tooltipLeft = rect.right + 8;
            this.tooltipHover = true;
        },
        hideTooltip() {
            this.tooltipHover = false;
        }
    }"
    type="button"
    x-show="sidebarCollapsed || !menuSearch || {{ json_encode($searchTarget) }}.includes(menuSearch.toLowerCase().trim())"
    @mouseenter="showTooltip()"
    @mouseleave="hideTooltip()"
    @focus="showTooltip()"
    @blur="hideTooltip()"
    @click="hideTooltip()"
    :class="sidebarCollapsed ? 'justify-center px-0 py-2.5' : 'px-3 py-2.5'"
    {{ $attributes->except(['icon:color', 'icon-color'])->merge([
        'class' => 'group relative flex items-center gap-3 rounded-md text-left font-medium transition-all ' .
            ($active
                ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs'
                : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white')
    ]) }}
>
    {{-- Active Left Accent Pill Indicator --}}
    @if ($active)
        <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] ?? '#3b82f6' }});"></span>
    @endif

    {{-- Icon --}}
    @if ($icon)
        <flux:icon :name="$icon" class="size-5 shrink-0 {{ $finalIconColor }}" :variant="$iconVariant" />
    @endif

    {{-- Label & Description --}}
    <div x-show="!sidebarCollapsed" class="min-w-0 flex-1 truncate">
        @if ($label)
            <span class="truncate block text-sm font-medium {{ $active ? 'text-neutral-900 dark:text-white' : '' }}">
                {{ $label }}
            </span>
        @endif
        @if ($description)
            <span class="truncate block text-[10px] text-neutral-400 dark:text-neutral-500">
                {{ $description }}
            </span>
        @endif
        {{ $slot }}
    </div>

    {{-- Optional Badge --}}
    @if ($badge)
        <span
            x-show="!sidebarCollapsed"
            class="ml-auto inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold {{ $badgeColor ?? 'bg-neutral-200/80 dark:bg-white/10 text-neutral-700 dark:text-neutral-300' }}"
        >
            {{ $badge }}
        </span>
    @endif

    {{-- Floating Desktop Tooltip when Sidebar is Collapsed --}}
    <template x-teleport="body">
        <div
            x-show="sidebarCollapsed && tooltipHover"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 scale-95 -translate-x-1"
            x-transition:enter-end="opacity-100 scale-100 translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 scale-100 translate-x-0"
            x-transition:leave-end="opacity-0 scale-95 -translate-x-1"
            :style="`top: ${tooltipTop}px; left: ${tooltipLeft}px; transform: translateY(-50%);`"
            class="pointer-events-none fixed z-[99999] flex items-center gap-2 rounded-lg border border-neutral-700/60 dark:border-white/15 bg-neutral-900/95 dark:bg-[#1c1c1c]/95 px-2.5 py-1.5 text-xs font-medium text-white shadow-xl shadow-black/25 backdrop-blur-md whitespace-nowrap select-none"
        >
            <span>{{ $label }}</span>
            @if ($badge)
                <span class="inline-flex items-center px-1.5 py-0.2 rounded-full text-[10px] font-semibold bg-white/20 text-white">
                    {{ $badge }}
                </span>
            @endif
        </div>
    </template>
</button>
