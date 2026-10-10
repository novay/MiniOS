<div class="flex h-full min-h-125 w-full flex-col overflow-hidden bg-[#f3f3f3] dark:bg-[#202020] text-[#1b1b1b] dark:text-zinc-100 font-sans select-none">

    {{-- ========================================================= --}}
    {{-- WINDOWS 11 HEADER & BREADCRUMB --}}
    {{-- ========================================================= --}}
    <header class="flex shrink-0 items-center justify-between border-b border-neutral-200/90 dark:border-white/5 bg-white/70 dark:bg-[#2b2b2b]/70 px-6 py-3.5 backdrop-blur-xl">
        <div>
            {{-- Windows 11 Breadcrumb --}}
            <div class="flex items-center gap-1.5 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">
                <span>{{ $this->t('app_title') }}</span>
                <svg class="size-3 text-neutral-400 dark:text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
                <span class="text-neutral-700 dark:text-neutral-300">{{ $this->t('crumb_programs') }}</span>
            </div>

            <div class="flex items-center gap-2 mt-0.5">
                <h1 class="text-base font-semibold tracking-tight text-neutral-900 dark:text-white">{{ $activeTab === 'catalog' ? $this->t('catalog_banner_title') : $this->t('header_installed_apps') }}</h1>
                <span class="rounded-md bg-neutral-200/70 dark:bg-white/10 px-2 py-0.2 text-[11px] font-medium text-neutral-600 dark:text-neutral-300">{{ $activeTab === 'catalog' ? count($catalogApps) : $stats['total'] }}</span>
            </div>
        </div>

        {{-- Primary Action (Windows 11 Fluent Button) --}}
        <div class="flex items-center gap-2">
            <button
                type="button"
                wire:click="openUploadModal"
                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                class="flex items-center gap-2 rounded-md px-3.5 py-1.5 text-xs font-medium text-white shadow-xs transition-all hover:brightness-110 active:scale-98"
            >
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>{{ $this->t('btn_install_zip') }}</span>
            </button>
        </div>
    </header>

    {{-- ========================================================= --}}
    {{-- TOOLBAR: WINDOWS 11 COMMAND BAR (SEARCH & SEGMENTED TABS) --}}
    {{-- ========================================================= --}}
    <div class="flex shrink-0 flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 px-6 pt-4 pb-2.5">
        {{-- Search Input (Windows 11 Fluent input box) --}}
        <div class="relative w-full sm:w-80">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="size-3.5 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            @if ($activeTab === 'catalog')
                <input
                    type="text"
                    wire:model.live.debounce.250ms="catalogSearch"
                    placeholder="{{ $this->t('catalog_search_placeholder') }}"
                    class="w-full rounded-md border border-neutral-300/90 dark:border-white/10 bg-white dark:bg-[#2d2d2d] py-1.5 pl-9 pr-8 text-xs text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 shadow-xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                />
                @if ($catalogSearch)
                    <button
                        type="button"
                        wire:click="$set('catalogSearch', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-neutral-400 hover:text-neutral-700 dark:hover:text-white transition-colors"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            @else
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="{{ $this->t('search_placeholder') }}"
                    class="w-full rounded-md border border-neutral-300/90 dark:border-white/10 bg-white dark:bg-[#2d2d2d] py-1.5 pl-9 pr-8 text-xs text-neutral-800 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 shadow-xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                />
                @if ($search)
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-neutral-400 hover:text-neutral-700 dark:hover:text-white transition-colors"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
            @endif
        </div>

        {{-- Filter Segmented Controls (Windows 11 Filter Style) --}}
        <div class="flex items-center gap-1 rounded-md bg-neutral-200/60 dark:bg-[#2d2d2d] p-1 border border-neutral-300/60 dark:border-white/10 text-xs">
            <button
                type="button"
                wire:click="setTab('all')"
                @if ($activeTab === 'all')
                    style="background-color: var(--accent-color, {{ $accent['hex'] }}); color: #ffffff;"
                @endif
                class="flex items-center gap-1.5 rounded-[5px] px-3 py-1 font-medium transition-all {{ $activeTab === 'all' ? 'text-white shadow-xs' : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white' }}"
            >
                <span>{{ $this->t('tab_all') }}</span>
                <span class="rounded px-1.5 py-0.2 text-[10px] {{ $activeTab === 'all' ? 'bg-white/20 text-white' : 'bg-neutral-300/80 text-neutral-600 dark:bg-white/10 dark:text-neutral-400' }}">{{ $stats['total'] }}</span>
            </button>

            <button
                type="button"
                wire:click="setTab('custom')"
                @if ($activeTab === 'custom')
                    style="background-color: var(--accent-color, {{ $accent['hex'] }}); color: #ffffff;"
                @endif
                class="flex items-center gap-1.5 rounded-[5px] px-3 py-1 font-medium transition-all {{ $activeTab === 'custom' ? 'text-white shadow-xs' : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white' }}"
            >
                <span>{{ $this->t('tab_custom') }}</span>
                <span class="rounded px-1.5 py-0.2 text-[10px] {{ $activeTab === 'custom' ? 'bg-white/20 text-white' : 'bg-neutral-300/80 text-neutral-600 dark:bg-white/10 dark:text-neutral-400' }}">{{ $stats['custom'] }}</span>
            </button>

            <button
                type="button"
                wire:click="setTab('system')"
                @if ($activeTab === 'system')
                    style="background-color: var(--accent-color, {{ $accent['hex'] }}); color: #ffffff;"
                @endif
                class="flex items-center gap-1.5 rounded-[5px] px-3 py-1 font-medium transition-all {{ $activeTab === 'system' ? 'text-white shadow-xs' : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white' }}"
            >
                <span>{{ $this->t('tab_system') }}</span>
                <span class="rounded px-1.5 py-0.2 text-[10px] {{ $activeTab === 'system' ? 'bg-white/20 text-white' : 'bg-neutral-300/80 text-neutral-600 dark:bg-white/10 dark:text-neutral-400' }}">{{ $stats['system'] }}</span>
            </button>

            <button
                type="button"
                wire:click="setTab('catalog')"
                @if ($activeTab === 'catalog')
                    style="background-color: var(--accent-color, {{ $accent['hex'] }}); color: #ffffff;"
                @endif
                class="flex items-center gap-1.5 rounded-[5px] px-3 py-1 font-medium transition-all {{ $activeTab === 'catalog' ? 'text-white shadow-xs' : 'text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white' }}"
            >
                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>{{ $this->t('tab_catalog') }}</span>
                <span class="rounded px-1.5 py-0.2 text-[10px] {{ $activeTab === 'catalog' ? 'bg-white/20 text-white' : 'bg-neutral-300/80 text-neutral-600 dark:bg-white/10 dark:text-neutral-400' }}">{{ $stats['catalog'] ?? count($catalogApps) }}</span>
            </button>
        </div>
    </div>

    @if ($activeTab === 'catalog')
        {{-- ========================================================= --}}
        {{-- APP CATALOG / DISCOVERY MARKETPLACE SHOWCASE --}}
        {{-- ========================================================= --}}
        <div class="flex-1 overflow-y-auto px-6 pb-6 space-y-4">
            {{-- Category Filter Pills --}}
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs select-none">
                @php
                    $categories = [
                        'all' => $this->t('cat_all'),
                        'productivity' => $this->t('cat_productivity'),
                        'media' => $this->t('cat_media'),
                        'developer' => $this->t('cat_developer'),
                        'games' => $this->t('cat_games'),
                        'utilities' => $this->t('cat_utilities'),
                    ];
                @endphp
                @foreach ($categories as $catKey => $catLabel)
                    <button
                        type="button"
                        wire:click="setCatalogCategory('{{ $catKey }}')"
                        class="rounded-full px-3 py-1 text-xs font-medium transition-all shrink-0 {{ $catalogCategory === $catKey ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 shadow-2xs font-semibold' : 'bg-neutral-200/60 dark:bg-white/5 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-white/10' }}"
                    >
                        {{ $catLabel }}
                    </button>
                @endforeach
            </div>

            {{-- Catalog Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @forelse ($catalogApps as $catApp)
                    <div class="flex flex-col justify-between rounded-xl border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 p-4 shadow-2xs transition-all hover:border-neutral-300 dark:hover:border-white/10 hover:shadow-xs group">
                        <div>
                            {{-- Top row: Icon, Title, Rating, Badge --}}
                            <div class="flex items-start justify-between gap-2.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="size-11 shrink-0 flex items-center justify-center rounded-xl bg-neutral-100 dark:bg-black/30 p-2 border border-neutral-200/60 dark:border-white/5 shadow-2xs">
                                        <x-minios.icon :name="$catApp['icon']" class="size-full" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h3 class="font-bold text-xs text-neutral-900 dark:text-white truncate">{{ $catApp['name'] }}</h3>
                                        </div>
                                        <div class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                            by {{ $catApp['author'] }}
                                        </div>
                                        <div class="flex items-center gap-1 text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                            <span class="text-amber-500 font-semibold">★ {{ $catApp['rating'] }}</span>
                                            <span class="text-neutral-300 dark:text-neutral-600">•</span>
                                            <span>{{ $catApp['downloads'] }} {{ $this->t('lbl_downloads') }}</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Category Badge --}}
                                <span class="rounded px-2 py-0.5 text-[10px] font-semibold shrink-0 {{
                                    $catApp['badge_color'] === 'indigo' ? 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20' :
                                    ($catApp['badge_color'] === 'emerald' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-500/20' :
                                    'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200/60 dark:border-amber-500/20')
                                }}">
                                    {{ $catApp['badge'] }}
                                </span>
                            </div>

                            {{-- Description --}}
                            <p class="mt-3 text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed line-clamp-2">
                                {{ $catApp['description'] }}
                            </p>

                            {{-- Features bullet points --}}
                            <div class="mt-3 space-y-1 border-t border-neutral-100 dark:border-white/5 pt-2 text-[11px] text-neutral-500 dark:text-neutral-400">
                                @foreach ($catApp['features'] as $feat)
                                    <div class="flex items-center gap-1.5 truncate">
                                        <svg class="size-3 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span class="truncate">{{ $feat }}</span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Tags --}}
                            <div class="mt-3 flex flex-wrap gap-1">
                                @foreach ($catApp['tags'] as $tag)
                                    <span class="rounded bg-neutral-100 dark:bg-white/5 px-1.5 py-0.2 text-[10px] font-medium text-neutral-500 dark:text-neutral-400 border border-neutral-200/40 dark:border-white/5">
                                        #{{ $tag }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Footer Action Bar --}}
                        <div class="mt-4 flex items-center justify-between border-t border-neutral-100 dark:border-white/5 pt-3">
                            <span class="text-[11px] font-mono text-neutral-400">
                                v{{ $catApp['version'] }} • {{ $catApp['size'] }}
                            </span>

                            <div class="flex items-center gap-1.5">
                                @if ($catApp['is_installed'])
                                    <span class="inline-flex items-center gap-1 rounded bg-emerald-50 dark:bg-emerald-500/10 px-2 py-1 text-[11px] font-semibold text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-500/20">
                                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <span>{{ $this->t('badge_installed_catalog') }}</span>
                                    </span>

                                    <button
                                        type="button"
                                        @click="openWindow('{{ $catApp['id'] }}')"
                                        class="inline-flex items-center gap-1 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#323232] px-2.5 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#3c3c3c] transition-all"
                                    >
                                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                        </svg>
                                        <span>{{ $this->t('btn_open') }}</span>
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        wire:click="installCatalogApp('{{ $catApp['id'] }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="installCatalogApp('{{ $catApp['id'] }}')"
                                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                                        class="inline-flex items-center gap-1.5 rounded-md px-3.5 py-1 text-xs font-medium text-white shadow-xs transition-all hover:brightness-110 active:scale-95 disabled:opacity-50"
                                    >
                                        <span wire:loading.remove wire:target="installCatalogApp('{{ $catApp['id'] }}')" class="inline-flex items-center gap-1">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            <span>{{ $this->t('btn_one_click_install') }}</span>
                                        </span>
                                        <span wire:loading wire:target="installCatalogApp('{{ $catApp['id'] }}')" class="inline-flex items-center gap-1">
                                            <svg class="size-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                            </svg>
                                            <span>{{ $this->t('btn_installing_catalog') }}</span>
                                        </span>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full rounded-lg border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 py-12 text-center">
                        <div class="flex flex-col items-center justify-center gap-2">
                            <div class="flex size-12 items-center justify-center rounded-lg bg-neutral-100 dark:bg-white/5 text-neutral-400">
                                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('catalog_empty_title') }}</p>
                            <p class="text-xs text-neutral-500">{{ $this->t('catalog_empty_desc') }}</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    @else
        {{-- ========================================================= --}}
        {{-- INSTALLED APPS (WINDOWS 11 FLUENT LIST TILES) --}}
        {{-- ========================================================= --}}
    <div class="flex-1 overflow-y-auto px-6 pb-4">
        <div class="flex flex-col gap-1.5">
            @forelse ($applications as $app)
                <div class="rounded-lg border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 shadow-2xs transition-all hover:border-neutral-300 dark:hover:border-white/10 group overflow-hidden">
                    {{-- Accordion Header / Primary Row --}}
                    <div class="flex items-center justify-between gap-2 p-3 transition-colors {{ $expandedApp === $app['id'] ? 'bg-neutral-50/90 dark:bg-white/[0.03] border-b border-neutral-200/60 dark:border-white/5' : 'hover:bg-neutral-50/60 dark:hover:bg-[#323232]' }}">
                        {{-- Left: Icon & Application Name / Details (Click to Toggle Accordion) --}}
                        <div
                            wire:click="toggleAppDetails('{{ $app['id'] }}')"
                            class="flex items-center gap-3.5 min-w-0 flex-1 cursor-pointer select-none"
                            title="{{ $this->t('app_accordion_title') }}"
                        >
                            {{-- Accordion Chevron Indicator --}}
                            <div class="text-neutral-400 dark:text-neutral-500 transition-transform duration-200 {{ $expandedApp === $app['id'] ? 'rotate-90 text-neutral-700 dark:text-neutral-200' : '' }}">
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>

                            <x-minios.icon :name="$app['icon']" class="size-9 shrink-0" />

                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-xs text-neutral-900 dark:text-white truncate">{{ $app['name'] }}</span>
                                    <span class="rounded bg-neutral-100 dark:bg-black/30 border border-neutral-200/60 dark:border-white/5 px-1.5 py-0.2 text-[10px] text-neutral-500 dark:text-neutral-400 font-mono">{{ $app['id'] }}</span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5 text-[11px] text-neutral-500 dark:text-neutral-400">
                                    <span>{{ $app['size'] }}</span>
                                    <span>•</span>
                                    <span>v{{ $app['version'] }}</span>
                                    @if (! empty($app['models']) || ! empty($app['migrations']))
                                        <span>•</span>
                                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zM4 10h16M4 14h16"/>
                                            </svg>
                                            <span>{{ $this->t('models_migrations_count', ['models' => count($app['models']), 'migrations' => count($app['migrations'])]) }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Middle: Type Badge (System or Custom) --}}
                        <div class="flex items-center gap-2 shrink-0">
                            @if ($app['isCore'])
                                <flux:badge size="sm" icon="shield-check" icon:variant="outline">
                                    {{ $this->t('badge_system') }}
                                </flux:badge>
                            @else
                                <flux:badge size="sm" icon="cube" icon:variant="outline" color="emerald">
                                    {{ $this->t('badge_custom') }}
                                </flux:badge>
                            @endif
                        </div>

                        {{-- Right: Actions (Windows 11 Fluent Action Buttons) --}}
                        <div class="flex items-center gap-1.5 shrink-0">
                            {{-- Open in MiniOS Desktop Window --}}
                            <flux:button
                                type="button"
                                @click="openWindow('{{ $app['id'] }}')"
                                title="{{ $this->t('btn_open_title') }}"
                                size="sm"
                            >
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                <span>{{ $this->t('btn_open') }}</span>
                            </flux:button>

                            {{-- Uninstall Custom App or Lock Badge --}}
                            @if ($app['canUninstall'])
                                <button
                                    type="button"
                                    wire:click="confirmUninstall('{{ $app['id'] }}')"
                                    title="{{ $this->t('btn_uninstall_title') }}"
                                    class="inline-flex items-center gap-1 rounded-md border border-rose-200/90 dark:border-rose-500/20 bg-rose-50 dark:bg-rose-500/10 px-3 py-1.5 text-xs font-medium text-rose-700 dark:text-rose-300 shadow-xs transition-all hover:bg-rose-600 hover:text-white hover:border-transparent active:scale-98"
                                >
                                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    <span>{{ $this->t('btn_uninstall') }}</span>
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Accordion Body / Expandable Detail Section --}}
                    @if ($expandedApp === $app['id'])
                        <div class="border-t border-neutral-200/70 dark:border-white/5 bg-neutral-50/40 dark:bg-black/20 p-4 space-y-4 animate-in fade-in slide-in-from-top-1 duration-150">
                            {{-- Info Grid (Manifest, Paths, Entrypoint) --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('arch_components') }}</div>
                                    <div class="space-y-1 text-[11px]">
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-neutral-500 shrink-0">{{ $this->t('lbl_class_manifest') }}</span>
                                            <span class="font-mono text-neutral-800 dark:text-neutral-200 text-right truncate" title="{{ $app['class'] }}">{{ $app['class'] }}</span>
                                        </div>
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-neutral-500 shrink-0">{{ $this->t('lbl_livewire_component') }}</span>
                                            <span class="font-mono text-neutral-800 dark:text-neutral-200 text-right truncate" title="{{ $app['component'] ?? '-' }}">{{ $app['component'] ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-neutral-500 shrink-0">{{ $this->t('lbl_entry_route') }}</span>
                                            <span class="font-mono text-neutral-800 dark:text-neutral-200 text-right">{{ $app['entry'] }}</span>
                                        </div>
                                        <div class="flex items-start justify-between gap-2">
                                            <span class="text-neutral-500 shrink-0">{{ $this->t('lbl_installed_at') }}</span>
                                            <span class="text-neutral-800 dark:text-neutral-200 text-right">{{ $app['installedAt'] }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2">
                                    <div class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('registered_routes') }}</div>
                                    <div class="flex flex-wrap gap-1 max-h-24 overflow-y-auto pr-1">
                                        @forelse ($app['routes'] as $route)
                                            <span class="inline-flex items-center rounded bg-neutral-100 dark:bg-white/5 px-2 py-0.5 font-mono text-[10px] text-neutral-600 dark:text-neutral-300 border border-neutral-200/60 dark:border-white/5">
                                                {{ $route }}
                                            </span>
                                        @empty
                                            <span class="text-[11px] text-neutral-400 italic">{{ $this->t('no_custom_routes') }}</span>
                                        @endforelse
                                    </div>
                                    @if ($app['folderPath'])
                                        <div class="pt-1 border-t border-neutral-100 dark:border-white/5">
                                            <div class="text-[10px] text-neutral-400 truncate" title="{{ $app['folderPath'] }}">
                                                <span class="font-medium text-neutral-500">{{ $this->t('lbl_folder') }}</span> {{ $app['folderPath'] }}
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Database Migrations & Models Section --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                {{-- Migrations Box --}}
                                <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="size-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7c0-2-1.5-3-3.5-3h-9C5.5 4 4 5 4 7zM4 10h16M4 14h16"/>
                                            </svg>
                                            <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('db_migrations') }}</span>
                                        </div>
                                        <span class="rounded-full bg-neutral-100 dark:bg-white/10 px-2 py-0.2 text-[10px] font-medium text-neutral-600 dark:text-neutral-300">
                                            {{ $this->t('migrations_count', ['count' => count($app['migrations'])]) }}
                                        </span>
                                    </div>

                                    @if (! empty($app['migrations']))
                                        <div class="divide-y divide-neutral-100 dark:divide-white/5 max-h-36 overflow-y-auto">
                                            @foreach ($app['migrations'] as $m)
                                                <div class="flex items-center justify-between py-1.5 text-[11px] gap-2">
                                                    <span class="font-mono text-neutral-700 dark:text-neutral-300 truncate" title="{{ $m['name'] }}">
                                                        {{ $m['name'] }}
                                                    </span>
                                                    @if ($m['applied'])
                                                        <span class="inline-flex items-center gap-1 rounded bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-500/20 shrink-0">
                                                            <svg class="size-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            <span>{{ $this->t('badge_applied') }}</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded bg-amber-50 dark:bg-amber-500/10 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:text-amber-400 border border-amber-200/80 dark:border-amber-500/20 shrink-0">
                                                            <span>{{ $this->t('badge_pending') }}</span>
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="rounded border border-dashed border-neutral-200 dark:border-white/10 p-3 text-center text-[11px] text-neutral-400 italic">
                                            {{ $this->t('no_migrations') }}
                                        </div>
                                    @endif
                                </div>

                                {{-- Eloquent Models Box --}}
                                <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2.5">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-1.5">
                                            <svg class="size-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                            </svg>
                                            <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('eloquent_models') }}</span>
                                        </div>
                                        <span class="rounded-full bg-neutral-100 dark:bg-white/10 px-2 py-0.2 text-[10px] font-medium text-neutral-600 dark:text-neutral-300">
                                            {{ $this->t('models_count', ['count' => count($app['models'])]) }}
                                        </span>
                                    </div>

                                    @if (! empty($app['models']))
                                        <div class="divide-y divide-neutral-100 dark:divide-white/5 max-h-36 overflow-y-auto">
                                            @foreach ($app['models'] as $mod)
                                                <div class="flex items-center justify-between py-1.5 text-[11px] gap-2">
                                                    <div class="min-w-0">
                                                        <span class="font-medium text-neutral-800 dark:text-neutral-200">{{ $mod['name'] }}</span>
                                                        @if ($mod['table'])
                                                            <span class="text-neutral-400 font-mono text-[10px] ml-1.5">({{ $mod['table'] }})</span>
                                                        @endif
                                                    </div>
                                                    <div class="shrink-0 flex items-center gap-1.5">
                                                        @if ($mod['count'] !== null)
                                                            <span class="inline-flex items-center rounded bg-neutral-100 dark:bg-white/5 px-2 py-0.5 text-[10px] font-medium text-neutral-600 dark:text-neutral-300 border border-neutral-200/60 dark:border-white/5">
                                                                {{ $this->t('data_count', ['count' => $mod['count']]) }}
                                                            </span>
                                                        @endif
                                                        <span class="inline-flex items-center gap-0.5 rounded bg-emerald-50 dark:bg-emerald-500/10 px-1.5 py-0.5 text-[10px] font-medium text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-500/20">
                                                            {{ $this->t('badge_ready') }}
                                                        </span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="rounded border border-dashed border-neutral-200 dark:border-white/10 p-3 text-center text-[11px] text-neutral-400 italic">
                                            {{ $this->t('no_models') }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Required Packages & Dependencies Section --}}
                            <div class="rounded-lg border border-neutral-200/80 dark:border-white/5 bg-white dark:bg-[#202020] p-3 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <svg class="size-3.5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                        <span class="text-[11px] font-semibold uppercase tracking-wider text-neutral-400">{{ $this->t('composer_dependencies') }}</span>
                                    </div>
                                    <span class="rounded-full bg-neutral-100 dark:bg-white/10 px-2 py-0.2 text-[10px] font-medium text-neutral-600 dark:text-neutral-300">
                                        {{ $this->t('dependencies_count', ['count' => count($app['packages'] ?? [])]) }}
                                    </span>
                                </div>

                                @if (! empty($app['packages']))
                                    <div class="divide-y divide-neutral-100 dark:divide-white/5 max-h-48 overflow-y-auto">
                                        @foreach ($app['packages'] as $pkg)
                                            <div class="flex flex-col sm:flex-row sm:items-center justify-between py-2 text-[11px] gap-2">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-mono font-medium text-neutral-900 dark:text-neutral-100">{{ $pkg['name'] }}</span>
                                                        @if ($pkg['installed'] && $pkg['version'])
                                                            <span class="rounded bg-neutral-100 dark:bg-white/5 px-1.5 py-0.2 text-[10px] font-mono text-neutral-500">{{ $pkg['version'] }}</span>
                                                        @endif
                                                    </div>
                                                    @if (! $pkg['installed'])
                                                        <div class="flex items-center gap-1.5 mt-1">
                                                            <span class="text-[10px] text-neutral-400">{{ $this->t('lbl_install_cmd') }}</span>
                                                            <code class="rounded bg-neutral-100 dark:bg-black/40 px-1.5 py-0.5 font-mono text-[10px] text-neutral-700 dark:text-neutral-300 border border-neutral-200/60 dark:border-white/5 select-all">{{ $pkg['command'] }}</code>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="shrink-0 flex items-center gap-2">
                                                    @if ($pkg['installed'])
                                                        <span class="inline-flex items-center gap-1 rounded bg-emerald-50 dark:bg-emerald-500/10 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-500/20">
                                                            <svg class="size-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            <span>{{ $this->t('badge_installed') }}</span>
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded bg-rose-50 dark:bg-rose-500/10 px-2 py-0.5 text-[10px] font-medium text-rose-700 dark:text-rose-400 border border-rose-200/80 dark:border-rose-500/20">
                                                            <svg class="size-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                            </svg>
                                                            <span>{{ $this->t('badge_uninstalled') }}</span>
                                                        </span>

                                                        <button
                                                            type="button"
                                                            wire:click="openComposerModal('{{ $app['id'] }}', '{{ $pkg['name'] }}')"
                                                            class="inline-flex items-center gap-1 rounded bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white px-2 py-0.5 text-[10px] font-medium shadow-sm transition"
                                                        >
                                                            <svg class="size-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                            </svg>
                                                            <span>{{ $this->t('btn_install_gui') }}</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded border border-dashed border-neutral-200 dark:border-white/10 p-3 text-center text-[11px] text-neutral-400 italic">
                                        {{ $this->t('no_dependencies') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="rounded-lg border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 py-12 text-center">
                    <div class="flex flex-col items-center justify-center gap-2">
                        <div class="flex size-12 items-center justify-center rounded-lg bg-neutral-100 dark:bg-white/5 text-neutral-400">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('no_apps_found_title') }}</p>
                        <p class="text-xs text-neutral-500">{{ $this->t('no_apps_found_desc') }}</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- ========================================================= --}}
    {{-- BOTTOM STATUS BAR (WINDOWS 11 COMPACT INFO BAR) --}}
    {{-- ========================================================= --}}
    <footer class="flex shrink-0 items-center justify-between border-t border-neutral-200/80 dark:border-white/5 bg-[#ebebeb]/90 dark:bg-[#1f1f1f]/95 px-5 py-2 text-[11px] text-neutral-500 dark:text-neutral-400 backdrop-blur-md">
        <div class="flex items-center gap-2 min-w-0">
            @if ($statusMessage)
                <div class="flex items-center gap-2 truncate animate-in fade-in duration-150">
                    @if ($statusType === 'success')
                        <span class="flex size-2 rounded-full bg-emerald-500 animate-pulse shrink-0"></span>
                        <span class="font-medium text-emerald-600 dark:text-emerald-400 truncate">{{ $statusMessage }}</span>
                    @else
                        <span class="flex size-2 rounded-full bg-rose-500 animate-pulse shrink-0"></span>
                        <span class="font-medium text-rose-600 dark:text-rose-400 truncate">{{ $statusMessage }}</span>
                    @endif
                    <button
                        type="button"
                        wire:click="dismissStatus"
                        title="{{ $this->t('btn_close') }}"
                        class="ml-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-white transition-colors"
                    >
                        <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            @else
                <div class="flex items-center gap-2 truncate">
                    <span class="inline-block size-1.5 rounded-full bg-neutral-400 dark:bg-neutral-600 shrink-0"></span>
                    <span class="truncate">
                        @if ($activeTab === 'catalog')
                            {{ $this->t('tab_catalog') }}: {{ count($catalogApps) }} template aplikasi
                        @else
                            {{ $this->t('total_apps_status', ['total' => $stats['total'], 'system' => $stats['system'], 'custom' => $stats['custom']]) }}
                        @endif
                    </span>
                </div>
            @endif
        </div>

        <div class="shrink-0 font-medium text-neutral-400 dark:text-neutral-500 text-[11px]">
            <span>{{ $this->t('storage_size', ['size' => $stats['storage']]) }}</span>
        </div>
    </footer>

    {{-- ========================================================= --}}
    {{-- UPLOAD APPLICATION ZIP MODAL (WINDOWS 11 FLUENT DIALOG) --}}
    {{-- ========================================================= --}}
    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 backdrop-blur-sm p-4 animate-in fade-in duration-150">
            <div class="w-full max-w-lg rounded-xl border border-neutral-200 dark:border-white/10 bg-white dark:bg-[#2c2c2c] p-6 shadow-2xl text-left">
                <div class="flex items-center justify-between border-b border-neutral-200/80 dark:border-white/5 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex size-9 items-center justify-center rounded-lg"
                            style="color: var(--accent-color, {{ $accent['hex'] }}); background-color: color-mix(in srgb, var(--accent-color, {{ $accent['hex'] }}) 15%, transparent);"
                        >
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('modal_upload_title') }}</h2>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('modal_upload_desc') }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeUploadModal"
                        class="text-neutral-400 hover:text-neutral-700 dark:hover:text-white transition-colors"
                    >
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Structure Guideline --}}
                <div class="mt-4 rounded-lg bg-neutral-100/70 dark:bg-white/3 border border-neutral-200/80 dark:border-white/5 p-3.5 text-xs text-neutral-600 dark:text-neutral-300">
                    <p class="font-medium text-neutral-900 dark:text-white mb-1.5 flex items-center gap-1.5">
                        <svg class="size-3.5" style="color: var(--accent-color, {{ $accent['hex'] }});" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $this->t('package_structure_title') }}
                    </p>
                    <ul class="space-y-1 text-[11px] text-neutral-500 dark:text-neutral-400">
                        <li>• <span class="text-neutral-800 dark:text-neutral-200">{{ $this->t('structure_manifest') }}</span></li>
                        <li>• <span class="text-neutral-800 dark:text-neutral-200">{{ $this->t('structure_livewire') }}</span></li>
                        <li>• <span class="text-neutral-800 dark:text-neutral-200">{{ $this->t('structure_views') }}</span></li>
                    </ul>
                </div>

                {{-- Dropzone / File Input Area --}}
                <div class="mt-4">
                    <label class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-neutral-300 dark:border-white/15 bg-neutral-50/60 dark:bg-white/2 hover:border-[var(--accent-color,{{ $accent['hex'] }})] dark:hover:border-[var(--accent-color,{{ $accent['hex'] }})]/60 p-6 text-center transition-all cursor-pointer group">
                        <input
                            type="file"
                            wire:model="uploadFile"
                            accept=".zip,application/zip"
                            class="hidden"
                        />
                        <div class="flex size-12 items-center justify-center rounded-lg bg-neutral-100 dark:bg-white/5 text-neutral-400 group-hover:scale-105 transition-all" style="color: var(--accent-color, {{ $accent['hex'] }});">
                            <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>

                        @if ($uploadFile)
                            <div class="mt-3">
                                <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 truncate max-w-xs">{{ method_exists($uploadFile, 'getClientOriginalName') ? $uploadFile->getClientOriginalName() : $this->t('selected_file') }}</p>
                                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">{{ method_exists($uploadFile, 'getSize') ? round($uploadFile->getSize() / 1024, 1) : 0 }} KB • {{ $this->t('ready_to_install') }}</p>
                            </div>
                        @else
                            <div class="mt-3">
                                <p class="text-xs font-medium text-neutral-700 dark:text-neutral-200 group-hover:text-neutral-900 dark:group-hover:text-white">{{ $this->t('upload_click_select') }}</p>
                                <p class="text-[11px] text-neutral-400 dark:text-neutral-500 mt-0.5">{{ $this->t('upload_max_size') }}</p>
                            </div>
                        @endif
                    </label>

                    {{-- Upload loading indicator --}}
                    <div wire:loading wire:target="uploadFile" class="mt-2 text-center text-xs" style="color: var(--accent-color, {{ $accent['hex'] }});">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            {{ $this->t('uploading_zip') }}
                        </span>
                    </div>

                    @error('uploadFile')
                        <p class="mt-2 text-xs text-rose-500 dark:text-rose-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Modal Actions --}}
                <div class="mt-6 flex items-center justify-end gap-2 border-t border-neutral-200/80 dark:border-white/5 pt-4">
                    <button
                        type="button"
                        wire:click="closeUploadModal"
                        class="rounded-md border border-neutral-300 dark:border-white/10 bg-white dark:bg-[#323232] px-4 py-2 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-xs transition-all hover:bg-neutral-100 dark:hover:bg-[#3c3c3c]"
                    >
                        {{ $this->t('btn_cancel') }}
                    </button>

                    <button
                        type="button"
                        wire:click="installZip"
                        wire:loading.attr="disabled"
                        wire:target="installZip"
                        @disabled(! $uploadFile)
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="flex items-center gap-2 rounded-md px-5 py-2 text-xs font-medium text-white shadow-xs transition-all hover:brightness-110 active:scale-98 disabled:opacity-50 disabled:pointer-events-none"
                    >
                        <span wire:loading.remove wire:target="installZip">{{ $this->t('btn_install_now') }}</span>
                        <span wire:loading wire:target="installZip" class="inline-flex items-center gap-1.5">
                            <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            {{ $this->t('btn_installing') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- UNINSTALL CONFIRMATION MODAL (WINDOWS 11 FLUENT DIALOG) --}}
    {{-- ========================================================= --}}
    @if ($appToUninstall && $appToUninstallDetails)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 backdrop-blur-sm p-4 animate-in fade-in duration-150">
            <div class="w-full max-w-md rounded-xl border border-rose-200 dark:border-rose-500/20 bg-white dark:bg-[#2c2c2c] p-6 shadow-2xl text-left">
                <div class="flex items-center gap-3">
                    <div class="flex size-11 items-center justify-center rounded-lg bg-rose-50 text-rose-600 dark:bg-rose-500/15 dark:text-rose-400 shrink-0">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-neutral-900 dark:text-white">{{ $this->t('modal_uninstall_title') }}</h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('modal_uninstall_desc') }}</p>
                    </div>
                </div>

                <div class="mt-4 rounded-lg bg-neutral-100/70 dark:bg-white/3 border border-neutral-200/80 dark:border-white/5 p-3.5 flex items-center gap-3">
                    <div class="size-9 shrink-0 flex items-center justify-center rounded-lg bg-white dark:bg-white/5 p-1 border border-neutral-200/80 dark:border-white/10 shadow-xs">
                        <x-minios.icon :name="$appToUninstallDetails['icon']" class="size-full" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-neutral-900 dark:text-white truncate">{{ $appToUninstallDetails['name'] }}</p>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">ID: {{ $appToUninstallDetails['id'] }}</p>
                    </div>
                </div>

                <p class="mt-4 text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed">
                    {!! $this->t('uninstall_confirm_msg', ['name' => '<strong class="text-neutral-900 dark:text-white">' . e($appToUninstallDetails['name']) . '</strong>']) !!}
                </p>

                <div class="mt-6 flex items-center justify-end gap-2 border-t border-neutral-200/80 dark:border-white/5 pt-4">
                    <button
                        type="button"
                        wire:click="cancelUninstall"
                        class="rounded-md border border-neutral-300 dark:border-white/10 bg-white dark:bg-[#323232] px-4 py-2 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-xs transition-all hover:bg-neutral-100 dark:hover:bg-[#3c3c3c]"
                    >
                        {{ $this->t('btn_cancel') }}
                    </button>

                    <button
                        type="button"
                        wire:click="uninstallApp"
                        wire:loading.attr="disabled"
                        wire:target="uninstallApp"
                        class="flex items-center gap-2 rounded-md bg-rose-600 px-5 py-2 text-xs font-medium text-white shadow-xs transition-all hover:bg-rose-500 active:scale-98 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="uninstallApp">{{ $this->t('btn_uninstall_app') }}</span>
                        <span wire:loading wire:target="uninstallApp" class="inline-flex items-center gap-1.5">
                            <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            {{ $this->t('btn_uninstalling') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- COMPOSER TERMINAL RUNNER MODAL --}}
    {{-- ========================================================= --}}
    @if ($showComposerModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in" x-data="{ copiedCmd: false }">
            <div class="w-full max-w-xl rounded-2xl border border-neutral-200/80 dark:border-white/10 bg-white dark:bg-[#252525] p-6 shadow-2xl space-y-4">
                {{-- Header --}}
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-neutral-900 dark:text-white">{{ $this->t('modal_composer_title') }}</h2>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('modal_composer_app', ['name' => $composerAppName]) }}</p>
                        </div>
                    </div>
                    @if ($composerStatus !== 'running')
                        <button
                            type="button"
                            wire:click="closeComposerModal"
                            class="rounded-lg p-1 text-neutral-400 hover:bg-neutral-100 dark:hover:bg-white/10 hover:text-neutral-700 dark:hover:text-neutral-200 transition"
                        >
                            <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>

                {{-- Command preview card --}}
                <div class="rounded-xl border border-neutral-200/70 dark:border-white/5 bg-neutral-50 dark:bg-black/30 p-3 space-y-1.5">
                    <div class="flex items-center justify-between text-[11px] text-neutral-500">
                        <span>{{ $this->t('composer_cmd_label') }}</span>
                        <button
                            type="button"
                            @click="
                                navigator.clipboard.writeText('{{ $composerCommand }}');
                                copiedCmd = true;
                                setTimeout(() => copiedCmd = false, 2000);
                            "
                            class="inline-flex items-center gap-1 font-sans text-neutral-600 dark:text-neutral-300 hover:text-indigo-600 transition"
                        >
                            <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="copiedCmd ? '{{ $this->t('btn_copied') }}' : '{{ $this->t('btn_copy') }}'"></span>
                        </button>
                    </div>
                    <code class="block font-mono text-xs text-indigo-700 dark:text-indigo-400 font-semibold select-all break-all">
                        {{ $composerCommand }}
                    </code>
                </div>

                {{-- Terminal Output Box --}}
                <div class="rounded-xl border border-neutral-800 bg-[#121212] overflow-hidden shadow-inner">
                    <div class="flex items-center justify-between px-3 py-2 bg-[#1b1b1b] border-b border-white/5 select-none">
                        <div class="flex items-center gap-1.5">
                            <span class="size-2.5 rounded-full bg-rose-500/80 inline-block"></span>
                            <span class="size-2.5 rounded-full bg-amber-500/80 inline-block"></span>
                            <span class="size-2.5 rounded-full bg-emerald-500/80 inline-block"></span>
                        </div>
                        <span class="text-[10px] font-mono text-neutral-400">{{ $this->t('terminal_output_header', ['package' => $composerPackage]) }}</span>
                        <div class="w-8"></div>
                    </div>

                    <pre class="max-h-56 min-h-32 overflow-y-auto p-3.5 font-mono text-xs leading-relaxed text-neutral-300 whitespace-pre-wrap select-text selection:bg-indigo-500/30">{{ $composerOutput }}</pre>
                </div>

                {{-- Status indicators & Actions --}}
                <div class="flex items-center justify-between border-t border-neutral-200/80 dark:border-white/5 pt-3">
                    <div class="text-xs">
                        @if ($composerStatus === 'running')
                            <span class="inline-flex items-center gap-1.5 text-indigo-600 dark:text-indigo-400 font-medium">
                                <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                                {{ $this->t('composer_executing') }}
                            </span>
                        @elseif ($composerStatus === 'success')
                            <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $this->t('composer_success') }}
                            </span>
                        @elseif ($composerStatus === 'error')
                            <span class="inline-flex items-center gap-1 text-rose-600 dark:text-rose-400 font-medium">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                {{ $this->t('composer_error') }}
                            </span>
                        @else
                            <span class="text-neutral-500 text-[11px]">{{ $this->t('composer_ready_hint') }}</span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($composerStatus !== 'running')
                            <button
                                type="button"
                                wire:click="closeComposerModal"
                                class="rounded-lg border border-neutral-300 dark:border-white/10 bg-white dark:bg-[#323232] px-4 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-[#3c3c3c] transition"
                            >
                                {{ $this->t('btn_close') }}
                            </button>
                        @endif

                        @if ($composerStatus === 'idle' || $composerStatus === 'error')
                            <button
                                type="button"
                                wire:click="runComposerInstall"
                                wire:loading.attr="disabled"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white px-4 py-1.5 text-xs font-medium shadow-sm transition disabled:opacity-50"
                            >
                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <span>{{ $this->t('btn_start_install') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
