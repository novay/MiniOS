<x-minios:desktop app-id="katalog" width="260" min-width="180" max-width="380">
    {{-- ========================================================= --}}
    {{-- 1. APPLICATION TOP MENUBAR --}}
    {{-- ========================================================= --}}
    <x-minios:desktop.menu>
        <x-minios.menubar>
            {{-- File --}}
            <x-minios.menubar.menu label="{{ __('File') }}">
                <x-minios.menubar.item wire:click="openUploadModal" icon="arrow-up-tray" shortcut="⌘O">
                    {{ $this->t('btn_install_zip') }}
                </x-minios.menubar.item>
                <x-minios.menubar.separator />
                <x-minios.menubar.item @click="$dispatch('close-window', { id: 'katalog' })" icon="x-mark" shortcut="⌥W">
                    {{ __('Tutup Jendela') }}
                </x-minios.menubar.item>
            </x-minios.menubar.menu>

            {{-- View --}}
            <x-minios.menubar.menu label="{{ __('View') }}">
                <x-minios.menubar.item wire:click="setTab('explore')" icon="sparkles" shortcut="⌘1">
                    {{ __('Jelajah') }}
                </x-minios.menubar.item>
                <x-minios.menubar.item wire:click="setTab('apps')" icon="squares-2x2" shortcut="⌘2">
                    {{ __('Aplikasi') }}
                </x-minios.menubar.item>
                <x-minios.menubar.item wire:click="setTab('themes')" icon="swatch" shortcut="⌘3">
                    {{ __('Tema') }}
                </x-minios.menubar.item>
                <x-minios.menubar.item wire:click="setTab('installed')" icon="arrow-down-tray" shortcut="⌘4">
                    {{ $this->t('header_installed_apps') }}
                </x-minios.menubar.item>
                <x-minios.menubar.separator />
                <x-minios.menubar.item @click="toggleSidebar()" icon="bars-3-bottom-left" shortcut="⌘B">
                    <span x-text="sidebarCollapsed ? '{{ __('Buka Sidebar') }}' : '{{ __('Tutup Sidebar') }}'"></span>
                </x-minios.menubar.item>
            </x-minios.menubar.menu>

            {{-- Window --}}
            <x-minios.menubar.menu label="{{ __('Window') }}">
                <x-minios.menubar.item @click="refresh()" icon="arrow-path" shortcut="⌘R">
                    {{ __('Muat Ulang') }}
                </x-minios.menubar.item>
                <x-minios.menubar.item @click="window.location.reload()" icon="arrow-path" shortcut="⇧⌘R">
                    {{ __('Muat Ulang Halaman') }}
                </x-minios.menubar.item>
                <x-minios.menubar.separator />
                <x-minios.menubar.item @click="toggleStatusbar()" icon="chart-bar" shortcut="⌘P">
                    <span x-text="statusbarVisible ? '{{ __('Disable Status Bar') }}' : '{{ __('Enable Status Bar') }}'"></span>
                </x-minios.menubar.item>
            </x-minios.menubar.menu>

            {{-- Help --}}
            <x-minios.menubar.menu label="{{ __('Help') }}">
                <x-minios.menubar.item @click="$dispatch('open-window', { id: 'docs' })" icon="book-open">
                    {{ __('Dokumentasi Layout') }}
                </x-minios.menubar.item>
                <x-minios.menubar.separator />
                <x-minios.menubar.item icon="information-circle" shortcut="⌘A">
                    {{ __('About Katalog v1.0.0') }}
                </x-minios.menubar.item>
            </x-minios.menubar.menu>
        </x-minios.menubar>

        <div class="flex items-center gap-2 pr-1 text-[11px] text-neutral-400 dark:text-neutral-500">
            <span class="flex items-center gap-1.5 font-medium">
                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                <span>v1.0.0</span>
            </span>
        </div>
    </x-minios:desktop.menu>

    {{-- ========================================================= --}}
    {{-- 2. APPLICATION NAVIGATION SIDEBAR (LEFT) --}}
    {{-- ========================================================= --}}
    <x-minios:desktop.sidebar>
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

        {{-- Search Input (Filter List Menu, hidden when sidebar is collapsed) --}}
        <div
            x-show="!sidebarCollapsed"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="mb-3 px-0.5"
        >
            <div class="relative flex items-center">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                    <flux:icon name="magnifying-glass" class="size-4 text-neutral-400 dark:text-neutral-500" />
                </div>
                <input
                    type="text"
                    x-model="menuSearch"
                    @keydown.escape.stop="menuSearch = ''"
                    placeholder="{{ __('Cari menu...') }}"
                    class="w-full rounded-xl border border-neutral-200/80 dark:border-white/10 bg-white/70 dark:bg-white/5 py-1.5 pl-8.5 pr-7 text-xs text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 shadow-2xs transition-all focus:outline-none focus:ring-1 focus:ring-[var(--accent-color,#3b82f6)] focus:border-[var(--accent-color,#3b82f6)]"
                />
                <button
                    type="button"
                    x-cloak
                    x-show="menuSearch.length > 0"
                    @click="menuSearch = ''"
                    class="absolute inset-y-0 right-0 flex items-center pr-2 text-neutral-400 hover:text-neutral-700 dark:hover:text-white"
                    title="{{ __('Hapus pencarian') }}"
                >
                    <flux:icon name="x-mark" class="size-3.5" />
                </button>
            </div>
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
                    x-show="sidebarCollapsed || !menuSearch || {{ json_encode(strtolower($navItem['label'].' '.$navItem['desc'])) }}.includes(menuSearch.toLowerCase().trim())"
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
            <div
                x-show="!sidebarCollapsed && (!menuSearch || {{ json_encode(strtolower($this->t('header_installed_apps').' instalasi kelola')) }}.includes(menuSearch.toLowerCase().trim()))"
                class="px-2.5 pt-4 pb-1"
            >
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
                x-show="sidebarCollapsed || !menuSearch || {{ json_encode(strtolower($this->t('header_installed_apps').' instalasi kelola')) }}.includes(menuSearch.toLowerCase().trim())"
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

            {{-- Empty search state --}}
            <div
                x-cloak
                x-show="!sidebarCollapsed && menuSearch && ![
                    'jelajah', 'unggulan', 'tren', 'explore',
                    'aplikasi', 'template', 'apps',
                    'themes', 'tema', 'koleksi tema',
                    'instalasi', 'kelola', 'terpasang', 'installed'
                ].some(k => k.includes(menuSearch.toLowerCase().trim()))"
                class="py-6 px-2 text-center text-xs text-neutral-400 dark:text-neutral-500"
            >
                <flux:icon name="magnifying-glass" class="mx-auto size-5 mb-1.5 text-neutral-300 dark:text-neutral-600" />
                <span class="block font-medium">{{ __('Menu tidak ditemukan') }}</span>
                <span class="block text-[11px] text-neutral-400 dark:text-neutral-500 mt-0.5">{{ __('Coba kata kunci lain') }}</span>
            </div>
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
    </x-minios:desktop.sidebar>

    {{-- ========================================================= --}}
    {{-- 3. MAIN CONTENT PANEL (RIGHT - WINDOWS 11 FLUENT MICA) --}}
    {{-- ========================================================= --}}
    <x-minios:desktop.content>
        @if ($effectiveNav === 'explore')
            @include('minios::apps.katalog.explore')
        @elseif ($effectiveNav === 'apps')
            @include('minios::apps.katalog.apps')
        @elseif ($effectiveNav === 'themes')
            @include('minios::apps.katalog.themes')
        @else
            @include('minios::apps.katalog.installed')
        @endif
    </x-minios:desktop.content>

    {{-- ========================================================= --}}
    {{-- 4. APPLICATION FIXED STATUSBAR --}}
    {{-- ========================================================= --}}
    <x-minios:desktop.statusbar>
        <div class="flex items-center gap-3">
            <span class="flex items-center gap-1.5 font-medium text-neutral-600 dark:text-neutral-300">
                <flux:icon name="check-circle" class="size-3.5 text-emerald-500" />
                <span>{{ __('Siap') }}</span>
            </span>
            <span class="text-neutral-300 dark:text-neutral-700">|</span>
            <span>{{ $stats['total'] }} {{ __('Aplikasi Terpasang') }}</span>
            <span class="text-neutral-300 dark:text-neutral-700">|</span>
            <span>{{ count($catalogApps) }} {{ __('Katalog Tersedia') }}</span>
        </div>

        <div class="flex items-center gap-3">
            <span>{{ $this->t('storage_size', ['size' => $stats['storage']]) }}</span>
            <span class="text-neutral-300 dark:text-neutral-700">|</span>
            <button
                type="button"
                @click="toggleSidebar()"
                class="hover:text-neutral-800 dark:hover:text-white transition-colors flex items-center gap-1"
                :title="sidebarCollapsed ? '{{ __('Buka Sidebar') }}' : '{{ __('Tutup Sidebar') }}'"
            >
                <flux:icon name="bars-3-bottom-left" class="size-3" />
                <span x-text="sidebarCollapsed ? '{{ __('Tampilkan Sidebar') }}' : '{{ __('Sembunyikan Sidebar') }}'"></span>
            </button>
        </div>
    </x-minios:desktop.statusbar>

    {{-- ========================================================= --}}
    {{-- WINDOW-SCOPED APPLICATION MODALS --}}
    {{-- ========================================================= --}}
    @include('minios::apps.katalog.modals.index')
</x-minios:desktop>
