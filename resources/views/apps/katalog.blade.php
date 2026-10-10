<div
    x-data="{
        sidebarCollapsed: false,
        sidebarWidth: 260,
        isResizing: false,
        toggleSidebar() {
            this.sidebarCollapsed = !this.sidebarCollapsed;
        },
        startResize(e) {
            if (this.sidebarCollapsed) return;
            this.isResizing = true;
            const startX = e.clientX;
            const startWidth = this.sidebarWidth;
            const onMouseMove = (ev) => {
                if (!this.isResizing) return;
                const newWidth = Math.min(380, Math.max(180, startWidth + (ev.clientX - startX)));
                this.sidebarWidth = newWidth;
            };
            const onMouseUp = () => {
                this.isResizing = false;
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
            };
            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        }
    }"
    :class="{ 'select-none cursor-col-resize': isResizing }"
    class="relative flex h-full w-full min-h-0 overflow-hidden bg-[#f3f3f3] dark:bg-[#202020] text-neutral-800 dark:text-neutral-100 font-sans select-none"
>
    {{-- ========================================================= --}}
    {{-- WINDOWS 11 FLUENT NAVIGATION SIDEBAR (LEFT) --}}
    {{-- ========================================================= --}}
    <aside
        :class="sidebarCollapsed ? 'w-16 p-2' : 'p-3.5'"
        :style="!sidebarCollapsed ? ('width: ' + sidebarWidth + 'px') : ''"
        class="flex shrink-0 flex-col border-r border-neutral-200/90 dark:border-white/5 bg-[#f8f8f8]/85 dark:bg-[#202020]/90 backdrop-blur-xl transition-[width,padding] duration-150 relative select-none"
    >
        {{-- Store / Catalog Brand Card with Toggle Collapse Button --}}
        <div class="mb-4 flex items-center justify-between gap-2">
            <div x-show="!sidebarCollapsed" class="flex flex-1 items-center gap-2.5 min-w-0 rounded-xl p-2 bg-white/70 dark:bg-white/5 border border-neutral-200/60 dark:border-white/5 shadow-2xs">
                <div
                    class="flex size-9 items-center justify-center rounded-xl text-white shadow-xs shrink-0"
                    style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                >
                    <flux:icon name="shopping-bag" class="size-4.5" />
                </div>
                <div class="min-w-0 flex-1">
                    <h2 class="truncate text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-wider">
                        {{ __('Katalog MiniOS') }}
                    </h2>
                    <p class="truncate text-[10px] text-neutral-500 dark:text-neutral-400">
                        App Catalog & Store
                    </p>
                </div>
            </div>

            {{-- Toggle Sidebar Collapse Button --}}
            <button
                type="button"
                @click="toggleSidebar()"
                class="flex size-9 shrink-0 items-center justify-center rounded-xl text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-all"
                :class="sidebarCollapsed ? 'w-full' : ''"
                :title="sidebarCollapsed ? '{{ __('Buka Sidebar') }}' : '{{ __('Tutup Sidebar') }}'"
            >
                <flux:icon name="bars-3-bottom-left" class="size-4.5" />
            </button>
        </div>

        {{-- Category Navigation List --}}
        <nav class="flex flex-1 flex-col gap-1 text-[13px] overflow-y-auto">
            @php
                $navItems = [
                    'explore' => [
                        'label' => __('Jelajah'),
                        'icon' => 'sparkles',
                        'desc' => __('Unggulan & Tren'),
                        'color' => 'bg-gradient-to-br from-indigo-500 to-purple-600 text-white',
                    ],
                    'apps' => [
                        'label' => __('Aplikasi'),
                        'icon' => 'squares-2x2',
                        'desc' => count($catalogApps) . ' ' . __('Template'),
                        'color' => 'bg-gradient-to-br from-blue-500 to-cyan-600 text-white',
                    ],
                    'themes' => [
                        'label' => __('Themes'),
                        'icon' => 'swatch',
                        'desc' => __('Koleksi Tema'),
                        'color' => 'bg-gradient-to-br from-fuchsia-500 to-pink-600 text-white',
                    ],
                ];
            @endphp

            @foreach ($navItems as $navKey => $navItem)
                @php
                    $isActive = $effectiveNav === $navKey;
                @endphp
                <button
                    type="button"
                    wire:click="setTab('{{ $navKey }}')"
                    :title="sidebarCollapsed ? '{{ $navItem['label'] }}' : ''"
                    :class="sidebarCollapsed ? 'justify-center px-0 py-2.5' : 'px-3 py-2.5'"
                    class="group relative flex items-center gap-3 rounded-xl text-left font-medium transition-all {{ $isActive ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
                >
                    {{-- Active Left Accent Pill Indicator --}}
                    @if ($isActive)
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                    @endif

                    <div class="flex size-7 items-center justify-center rounded-lg {{ $navItem['color'] }} shadow-2xs shrink-0 transition-transform group-hover:scale-105">
                        <flux:icon :name="$navItem['icon']" class="size-4" />
                    </div>

                    <div x-show="!sidebarCollapsed" class="min-w-0 flex-1 truncate">
                        <span class="truncate block text-xs {{ $isActive ? 'font-bold text-neutral-900 dark:text-white' : 'font-medium' }}">{{ $navItem['label'] }}</span>
                    </div>
                </button>
            @endforeach

            {{-- Separator Section Label --}}
            <div x-show="!sidebarCollapsed" class="px-2.5 pt-4 pb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                    {{ __('INSTALASI & KELOLA') }}
                </span>
            </div>
            <div x-show="sidebarCollapsed" class="my-2 border-t border-neutral-200/80 dark:border-white/5"></div>

            {{-- Installed App Navigation Item --}}
            @php
                $isInstalledActive = $effectiveNav === 'installed';
            @endphp
            <button
                type="button"
                wire:click="setTab('installed')"
                :title="sidebarCollapsed ? '{{ $this->t('header_installed_apps') }}' : ''"
                :class="sidebarCollapsed ? 'justify-center px-0 py-2.5' : 'px-3 py-2.5'"
                class="group relative flex items-center gap-3 rounded-xl text-left font-medium transition-all {{ $isInstalledActive ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
            >
                @if ($isInstalledActive)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                @endif

                <div class="flex size-7 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-2xs shrink-0 transition-transform group-hover:scale-105">
                    <flux:icon name="arrow-down-tray" class="size-4" />
                </div>
                <div x-show="!sidebarCollapsed" class="min-w-0 flex-1 truncate">
                    <span class="truncate block text-xs {{ $isInstalledActive ? 'font-bold text-neutral-900 dark:text-white' : 'font-medium' }}">{{ $this->t('header_installed_apps') }}</span>
                </div>
            </button>
        </nav>

        {{-- Sidebar Footer Quick Action & Storage --}}
        <div class="mt-auto pt-3 border-t border-neutral-200/70 dark:border-white/5 space-y-2">
            <button
                type="button"
                wire:click="openUploadModal"
                :title="sidebarCollapsed ? '{{ $this->t('btn_install_zip') }}' : ''"
                :class="sidebarCollapsed ? 'justify-center px-0' : 'justify-center px-3'"
                class="w-full flex items-center gap-2 rounded-xl bg-neutral-200/80 dark:bg-white/10 hover:bg-neutral-300/80 dark:hover:bg-white/15 py-2 text-xs font-semibold text-neutral-700 dark:text-neutral-200 transition-all active:scale-98"
            >
                <flux:icon name="arrow-up-tray" class="size-3.5 text-neutral-500 dark:text-neutral-400 shrink-0" />
                <span x-show="!sidebarCollapsed" class="truncate">{{ $this->t('btn_install_zip') }}</span>
            </button>

            <div x-show="!sidebarCollapsed" class="flex items-center justify-between text-[11px] text-neutral-400 dark:text-neutral-500 px-1 font-medium">
                <span>{{ $this->t('storage_size', ['size' => $stats['storage']]) }}</span>
                <span>{{ $stats['total'] }} {{ __('Apps') }}</span>
            </div>
        </div>
    </aside>

    {{-- Resizer divider handle --}}
    <div
        x-show="!sidebarCollapsed"
        @mousedown.prevent="startResize($event)"
        class="group relative w-1 hover:w-1.5 shrink-0 cursor-col-resize select-none bg-neutral-200/60 dark:bg-white/5 hover:bg-[var(--accent-color,{{ $accent['hex'] }})]/60 transition-colors z-20"
        title="{{ __('Geser untuk mengatur lebar sidebar') }}"
    >
        <div class="absolute inset-y-0 -left-1 -right-1"></div>
    </div>

    {{-- ========================================================= --}}
    {{-- MAIN CONTENT PANEL (RIGHT - WINDOWS 11 FLUENT MICA) --}}
    {{-- ========================================================= --}}
    <main class="@container flex flex-1 flex-col min-w-0 h-full overflow-y-auto bg-[#f3f3f3] dark:bg-[#1f1f1f]">
        @if ($effectiveNav === 'explore')
            @include('minios::apps.katalog.explore')
        @elseif ($effectiveNav === 'apps')
            @include('minios::apps.katalog.apps')
        @elseif ($effectiveNav === 'themes')
            @include('minios::apps.katalog.themes')
        @else
            @include('minios::apps.katalog.installed')
        @endif
    </main>

    {{-- ========================================================= --}}
    {{-- WINDOW-SCOPED APPLICATION MODALS --}}
    {{-- ========================================================= --}}
    @include('minios::apps.katalog.modals.index')
</div>
