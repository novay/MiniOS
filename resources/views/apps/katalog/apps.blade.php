<div class="flex flex-1 flex-col overflow-y-auto p-6 md:p-8 space-y-6">
    {{-- ========================================================= --}}
    {{-- HEADER & SEARCH TOOLBAR --}}
    {{-- ========================================================= --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-1.5 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">
                <span>{{ $this->t('app_title') }}</span>
                <flux:icon name="chevron-right" class="size-3 text-neutral-400" />
                <span class="text-neutral-700 dark:text-neutral-300">{{ __('Katalog Aplikasi') }}</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white mt-1">
                {{ $this->t('catalog_banner_title') }}
            </h1>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                {{ __('Temukan dan pasang aplikasi desktop siap pakai dalam 1-klik') }}
            </p>
        </div>

        {{-- Search Input --}}
        <div class="relative w-full md:w-72">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <flux:icon name="magnifying-glass" class="size-4 text-neutral-400 dark:text-neutral-500" />
            </div>
            <input
                type="text"
                wire:model.live.debounce.250ms="catalogSearch"
                placeholder="{{ $this->t('catalog_search_placeholder') }}"
                class="w-full rounded-xl border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] py-2 pl-9 pr-8 text-xs text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
            />
            @if ($catalogSearch)
                <button
                    type="button"
                    wire:click="$set('catalogSearch', '')"
                    class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-neutral-400 hover:text-neutral-700 dark:hover:text-white transition-colors"
                >
                    <flux:icon name="x-mark" class="size-3.5" />
                </button>
            @endif
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- CATEGORY FILTER PILLS --}}
    {{-- ========================================================= --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs select-none">
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
                class="rounded-full px-3.5 py-1.5 text-xs font-medium transition-all shrink-0 {{ $catalogCategory === $catKey ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 shadow-2xs font-semibold' : 'bg-neutral-200/70 dark:bg-white/5 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-white/10' }}"
            >
                {{ $catLabel }}
            </button>
        @endforeach
    </div>

    {{-- ========================================================= --}}
    {{-- CATALOG APPS GRID --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($catalogApps as $catApp)
            <div class="flex flex-col justify-between rounded-2xl border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 p-4.5 shadow-2xs transition-all hover:border-neutral-300 dark:hover:border-white/10 hover:shadow-xs group">
                <div class="space-y-3">
                    {{-- Top row: Icon, Name, Author, Rating, Badge --}}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="size-12 shrink-0 flex items-center justify-center rounded-xl bg-neutral-100 dark:bg-black/30 p-2 border border-neutral-200/60 dark:border-white/5 shadow-2xs group-hover:scale-105 transition-transform">
                                <x-minios.icon :name="$catApp['icon']" class="size-full" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-neutral-900 dark:text-white truncate">{{ $catApp['name'] }}</h3>
                                <div class="text-[11px] text-neutral-500 dark:text-neutral-400">
                                    by {{ $catApp['author'] }}
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                    <span class="text-amber-500 font-bold">★ {{ $catApp['rating'] }}</span>
                                    <span>•</span>
                                    <span>{{ $catApp['downloads'] }} {{ $this->t('lbl_downloads') }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Category / Official Badge --}}
                        <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold shrink-0 bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-500/20">
                            {{ $catApp['badge'] ?? $catApp['category_label'] }}
                        </span>
                    </div>

                    {{-- Description --}}
                    <p class="text-xs text-neutral-600 dark:text-neutral-300 line-clamp-2 leading-relaxed">
                        {{ $catApp['description'] }}
                    </p>

                    {{-- Features List --}}
                    @if (!empty($catApp['features']))
                        <div class="pt-2 border-t border-neutral-100 dark:border-white/5 space-y-1">
                            @foreach (array_slice($catApp['features'], 0, 2) as $feat)
                                <div class="flex items-center gap-1.5 text-[11px] text-neutral-500 dark:text-neutral-400">
                                    <flux:icon name="check" class="size-3 text-emerald-500 shrink-0" />
                                    <span class="truncate">{{ $feat }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Action Footer --}}
                <div class="mt-4 pt-3.5 border-t border-neutral-100 dark:border-white/5 flex items-center justify-between gap-2">
                    <span class="text-[11px] font-medium text-neutral-400 dark:text-neutral-500">
                        {{ $catApp['size'] }} • v{{ $catApp['version'] }}
                    </span>

                    <div>
                        @if ($catApp['is_installed'])
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 px-2 py-1 text-[11px] font-semibold border border-emerald-200/60 dark:border-emerald-500/20">
                                    <flux:icon name="check-circle" class="size-3" />
                                    <span>{{ __('Terpasang') }}</span>
                                </span>

                                <button
                                    type="button"
                                    @click="openWindow('{{ $catApp['id'] }}')"
                                    class="inline-flex items-center gap-1 rounded-lg bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 px-3 py-1 text-xs font-semibold shadow-2xs hover:opacity-90 active:scale-95 transition-all"
                                >
                                    <span>{{ __('Buka') }}</span>
                                </button>
                            </div>
                        @else
                            <button
                                type="button"
                                wire:click="installCatalogApp('{{ $catApp['id'] }}')"
                                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                                class="inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-bold text-white shadow-2xs hover:brightness-110 active:scale-95 transition-all"
                            >
                                <flux:icon name="arrow-down-tray" class="size-3.5" />
                                <span>{{ __('Pasang 1-Klik') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center">
                <flux:icon name="magnifying-glass" class="mx-auto size-10 text-neutral-300 dark:text-neutral-600 mb-3" />
                <h3 class="text-sm font-bold text-neutral-800 dark:text-neutral-200">{{ __('Tidak ada aplikasi di katalog') }}</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1 max-w-sm mx-auto">
                    {{ __('Coba cari dengan kata kunci lain atau pilih kategori lain.') }}
                </p>
                <button
                    type="button"
                    wire:click="$set('catalogSearch', ''); setCatalogCategory('all')"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-neutral-200/80 dark:bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-300 hover:bg-neutral-300 dark:hover:bg-white/15 transition-all"
                >
                    {{ __('Reset Pencarian') }}
                </button>
            </div>
        @endforelse
    </div>
</div>
