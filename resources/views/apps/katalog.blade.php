<div class="relative flex h-full min-h-130 w-full overflow-hidden bg-[#f3f3f3] dark:bg-[#202020] text-neutral-800 dark:text-neutral-100 font-sans select-none">
    {{-- ========================================================= --}}
    {{-- WINDOWS 11 FLUENT NAVIGATION SIDEBAR (LEFT) --}}
    {{-- ========================================================= --}}
    <aside class="flex w-64 md:w-72 shrink-0 flex-col border-r border-neutral-200/90 dark:border-white/5 bg-[#f8f8f8]/85 dark:bg-[#202020]/90 p-3.5 backdrop-blur-xl">
        {{-- Store / Catalog Brand Card --}}
        <div class="mb-4 flex items-center gap-3 rounded-xl p-2 bg-white/70 dark:bg-white/5 border border-neutral-200/60 dark:border-white/5 shadow-2xs">
            <div
                class="flex size-10 items-center justify-center rounded-xl text-white shadow-xs shrink-0"
                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
            >
                <flux:icon name="shopping-bag" class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="truncate text-xs font-bold text-neutral-900 dark:text-white uppercase tracking-wider">
                    {{ __('Katalog MiniOS') }}
                </h2>
                <p class="truncate text-[11px] text-neutral-500 dark:text-neutral-400">
                    App Catalog & Marketplace
                </p>
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
                    class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-left font-medium transition-all {{ $isActive ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
                >
                    {{-- Active Left Accent Pill Indicator --}}
                    @if ($isActive)
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                    @endif

                    <div class="flex size-7 items-center justify-center rounded-lg {{ $navItem['color'] }} shadow-2xs shrink-0 transition-transform group-hover:scale-105">
                        <flux:icon :name="$navItem['icon']" class="size-4" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <span class="truncate block text-xs {{ $isActive ? 'font-bold text-neutral-900 dark:text-white' : 'font-medium' }}">{{ $navItem['label'] }}</span>
                        {{-- <span class="truncate block text-[10px] text-neutral-400 dark:text-neutral-500">{{ $navItem['desc'] }}</span> --}}
                    </div>
                </button>
            @endforeach

            {{-- Separator Section Label --}}
            <div class="px-2.5 pt-4 pb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                    {{ __('INSTALASI & KELOLA') }}
                </span>
            </div>

            {{-- Installed App Navigation Item --}}
            @php
                $isInstalledActive = $effectiveNav === 'installed';
            @endphp
            <button
                type="button"
                wire:click="setTab('installed')"
                class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-left font-medium transition-all {{ $isInstalledActive ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
            >
                @if ($isInstalledActive)
                    <span class="absolute left-0 top-1/2 -translate-y-1/2 h-4.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                @endif

                <div class="flex size-7 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white shadow-2xs shrink-0 transition-transform group-hover:scale-105">
                    <flux:icon name="arrow-down-tray" class="size-4" />
                </div>
                <div class="min-w-0 flex-1">
                    <span class="truncate block text-xs {{ $isInstalledActive ? 'font-bold text-neutral-900 dark:text-white' : 'font-medium' }}">{{ $this->t('header_installed_apps') }}</span>
                    {{-- <span class="truncate block text-[10px] text-neutral-400 dark:text-neutral-500">{{ $stats['total'] }} {{ __('Aplikasi Aktif') }}</span> --}}
                </div>
            </button>
        </nav>

        {{-- Sidebar Footer Quick Action & Storage --}}
        <div class="mt-auto pt-3 border-t border-neutral-200/70 dark:border-white/5 space-y-2">
            <button
                type="button"
                wire:click="openUploadModal"
                class="w-full flex items-center justify-center gap-2 rounded-xl bg-neutral-200/80 dark:bg-white/10 hover:bg-neutral-300/80 dark:hover:bg-white/15 px-3 py-2 text-xs font-semibold text-neutral-700 dark:text-neutral-200 transition-all active:scale-98"
            >
                <flux:icon name="arrow-up-tray" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                <span>{{ $this->t('btn_install_zip') }}</span>
            </button>

            <div class="flex items-center justify-between text-[11px] text-neutral-400 dark:text-neutral-500 px-1 font-medium">
                <span>{{ $this->t('storage_size', ['size' => $stats['storage']]) }}</span>
                <span>{{ $stats['total'] }} {{ __('Apps') }}</span>
            </div>
        </div>
    </aside>

    {{-- ========================================================= --}}
    {{-- MAIN CONTENT PANEL (RIGHT - WINDOWS 11 FLUENT MICA) --}}
    {{-- ========================================================= --}}
    <main class="flex flex-1 flex-col overflow-y-auto bg-[#f3f3f3] dark:bg-[#1f1f1f]">
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
