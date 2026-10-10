<div class="flex flex-col p-4 @sm:p-5 @lg:p-6 space-y-6 @lg:space-y-7">
    {{-- ========================================================= --}}
    {{-- HERO SPOTLIGHT BANNER CAROUSEL (APP STORE STYLE) --}}
    {{-- ========================================================= --}}
    <div class="relative w-full shrink-0 overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-950 via-indigo-900 to-purple-900 p-5 @sm:p-6 @lg:p-7 text-white shadow-lg border border-indigo-500/20">
        {{-- Ambient Glass Orbs --}}
        <div class="pointer-events-none absolute -right-12 -top-12 size-64 rounded-full bg-purple-500/20 blur-3xl"></div>
        <div class="pointer-events-none absolute right-32 -bottom-16 size-52 rounded-full bg-cyan-400/15 blur-2xl"></div>

        <div class="relative z-10 flex flex-col @[720px]:flex-row items-start @[720px]:items-center justify-between gap-5">
            <div class="space-y-2.5 max-w-xl min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/20 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">
                        <flux:icon name="sparkles" class="size-3 text-amber-300" />
                        <span>{{ __('Pilihan Utama • Suite Kreatif') }}</span>
                    </span>
                    <span class="text-xs font-semibold text-white/80">★ 4.9 (500+ Ulasan)</span>
                </div>

                <h1 class="text-lg @sm:text-xl @lg:text-2xl font-extrabold tracking-tight text-white leading-snug">
                    MiniOS Creative & Productivity Suite
                </h1>

                <p class="text-xs @sm:text-xs @lg:text-sm text-white/85 leading-relaxed line-clamp-2 @lg:line-clamp-none">
                    Koleksi lengkap aplikasi desktop produktivitas: <strong class="text-white">Sticky Notes</strong>, <strong class="text-white">Paint Studio</strong>, dan <strong class="text-white">Markdown Live</strong> dalam satu ekosistem web desktop terpadu.
                </p>

                <div class="flex flex-wrap items-center gap-2.5 pt-1.5">
                    <button
                        type="button"
                        wire:click="setTab('catalog')"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-white px-3.5 py-2 text-xs font-bold text-indigo-950 shadow-md transition-all hover:bg-white/90 active:scale-95"
                    >
                        <flux:icon name="squares-2x2" class="size-3.5 text-indigo-700" />
                        <span>{{ __('Jelajahi Semua') }}</span>
                    </button>

                    <button
                        type="button"
                        wire:click="installCatalogApp('notes')"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-white/15 px-3.5 py-2 text-xs font-semibold text-white backdrop-blur-md transition-all hover:bg-white/25 active:scale-95"
                    >
                        <flux:icon name="arrow-down-tray" class="size-3.5" />
                        <span>{{ __('Pasang Sticky Notes') }}</span>
                    </button>
                </div>
            </div>

            {{-- Mockup Icons Preview Stack --}}
            <div class="hidden @[720px]:flex items-center gap-2.5 shrink-0 p-2.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 shadow-2xl">
                <div class="flex flex-col items-center gap-1 p-2 rounded-xl bg-white/15 border border-white/20 hover:scale-105 transition-transform">
                    <div class="size-10 flex items-center justify-center rounded-xl bg-amber-400 text-amber-950 shadow-md">
                        <flux:icon name="document-text" class="size-6" />
                    </div>
                    <span class="text-[10px] font-bold text-white">Notes</span>
                </div>
                <div class="flex flex-col items-center gap-1 p-2 rounded-xl bg-white/15 border border-white/20 hover:scale-105 transition-transform">
                    <div class="size-10 flex items-center justify-center rounded-xl bg-rose-400 text-white shadow-md">
                        <flux:icon name="paint-brush" class="size-6" />
                    </div>
                    <span class="text-[10px] font-bold text-white">Paint</span>
                </div>
                <div class="flex flex-col items-center gap-1 p-2 rounded-xl bg-white/15 border border-white/20 hover:scale-105 transition-transform">
                    <div class="size-10 flex items-center justify-center rounded-xl bg-indigo-500 text-white shadow-md">
                        <flux:icon name="code-bracket" class="size-6" />
                    </div>
                    <span class="text-[10px] font-bold text-white">Markdown</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- SECTION: TOP CHARTS / TANGGA TERATAS --}}
    {{-- ========================================================= --}}
    <div class="space-y-3.5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm @sm:text-base font-bold text-neutral-900 dark:text-white flex items-center gap-2">
                    <flux:icon name="chart-bar" class="size-4.5 text-indigo-600 dark:text-indigo-400" />
                    <span>{{ __('Tangga Teratas (Top Charts)') }}</span>
                </h2>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                    {{ __('Aplikasi yang paling banyak dipasang dan digunakan di MiniOS') }}
                </p>
            </div>

            <button
                type="button"
                wire:click="setTab('catalog')"
                class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1"
            >
                <span>{{ __('Lihat Semua') }}</span>
                <flux:icon name="chevron-right" class="size-3.5" />
            </button>
        </div>

        <div class="grid grid-cols-1 @[540px]:grid-cols-2 @[860px]:grid-cols-3 gap-3">
            @foreach ($topCharts as $chart)
                <div class="flex items-center justify-between gap-2.5 p-3 rounded-xl border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 shadow-2xs hover:shadow-xs hover:border-neutral-300 dark:hover:border-white/10 transition-all group min-w-0">
                    <div class="flex items-center gap-2.5 min-w-0 flex-1">
                        {{-- Rank Number --}}
                        <span class="text-sm font-extrabold text-neutral-400 dark:text-neutral-500 w-4 text-center shrink-0">
                            {{ $chart['rank'] }}
                        </span>

                        {{-- Icon --}}
                        <div class="size-10 shrink-0 flex items-center justify-center rounded-xl bg-neutral-100 dark:bg-black/30 p-1.5 border border-neutral-200/60 dark:border-white/5 shadow-2xs group-hover:scale-105 transition-transform">
                            <x-minios.icon :name="$chart['icon']" class="size-full" />
                        </div>

                        {{-- Details --}}
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-xs text-neutral-900 dark:text-white truncate">{{ $chart['name'] }}</h3>
                            <p class="text-[10px] text-neutral-500 dark:text-neutral-400 truncate">{{ $chart['category_label'] }}</p>
                            <div class="flex items-center gap-1 text-[10px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                                <span class="text-amber-500 font-bold">★ {{ $chart['rating'] }}</span>
                                <span>•</span>
                                <span class="truncate">{{ $chart['downloads'] }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <div class="shrink-0">
                        @if ($chart['is_installed'])
                            <button
                                type="button"
                                @click="openWindow('{{ $chart['id'] }}')"
                                class="inline-flex items-center gap-1 rounded-lg bg-neutral-100 dark:bg-white/10 px-2.5 py-1 text-xs font-semibold text-neutral-800 dark:text-neutral-200 hover:bg-neutral-200 dark:hover:bg-white/15 transition-all"
                            >
                                <span>{{ __('Buka') }}</span>
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="installCatalogApp('{{ $chart['id'] }}')"
                                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-semibold text-white shadow-2xs hover:brightness-110 active:scale-95 transition-all"
                            >
                                <span>{{ __('Pasang') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- SECTION: PILIHAN EDITOR (EDITOR'S CHOICE) --}}
    {{-- ========================================================= --}}
    <div class="space-y-3.5">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-sm @sm:text-base font-bold text-neutral-900 dark:text-white flex items-center gap-2">
                    <flux:icon name="trophy" class="size-4.5 text-amber-500" />
                    <span>{{ __('Pilihan Editor (Editor\'s Choice)') }}</span>
                </h2>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                    {{ __('Aplikasi berkualitas tinggi yang dirancang dengan perhatian khusus pada performa dan estetika') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 @[540px]:grid-cols-2 gap-3.5">
            {{-- Editor Pick 1: Sticky Notes --}}
            <div class="flex flex-col justify-between p-4 @sm:p-5 rounded-2xl border border-neutral-200/90 dark:border-white/5 bg-gradient-to-br from-amber-500/10 via-transparent to-transparent dark:from-amber-500/5 bg-white dark:bg-[#2b2b2b]/70 shadow-2xs hover:shadow-xs transition-all">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-amber-500/20 text-amber-700 dark:text-amber-300 text-[10px] font-bold px-2.5 py-0.5">
                            {{ __('Editor\'s Choice') }}
                        </span>
                        <span class="text-xs font-bold text-amber-500">4.9 ★★★★★</span>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="size-11 shrink-0 flex items-center justify-center rounded-xl bg-amber-400 text-amber-950 p-2 shadow-md">
                            <flux:icon name="document-text" class="size-full" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xs @sm:text-sm font-bold text-neutral-900 dark:text-white truncate">Sticky Notes</h3>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">
                                Catatan tempel desktop dengan kartu multi-warna, pencarian instan, dan penyimpanan lokal persisten.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-3.5 pt-2.5 border-t border-neutral-100 dark:border-white/5 flex items-center justify-between">
                    <span class="text-[10px] text-neutral-400 dark:text-neutral-500">MiniOS Team • 18 KB</span>
                    <button
                        type="button"
                        wire:click="installCatalogApp('notes')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-xs font-semibold shadow-2xs active:scale-95 transition-all"
                    >
                        <flux:icon name="arrow-down-tray" class="size-3.5" />
                        <span>{{ __('Pasang 1-Klik') }}</span>
                    </button>
                </div>
            </div>

            {{-- Editor Pick 2: Paint Studio --}}
            <div class="flex flex-col justify-between p-4 @sm:p-5 rounded-2xl border border-neutral-200/90 dark:border-white/5 bg-gradient-to-br from-rose-500/10 via-transparent to-transparent dark:from-rose-500/5 bg-white dark:bg-[#2b2b2b]/70 shadow-2xs hover:shadow-xs transition-all">
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="rounded-full bg-rose-500/20 text-rose-700 dark:text-rose-300 text-[10px] font-bold px-2.5 py-0.5">
                            {{ __('Editor\'s Choice') }}
                        </span>
                        <span class="text-xs font-bold text-amber-500">4.8 ★★★★★</span>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="size-11 shrink-0 flex items-center justify-center rounded-xl bg-rose-500 text-white p-2 shadow-md">
                            <flux:icon name="paint-brush" class="size-full" />
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="text-xs @sm:text-sm font-bold text-neutral-900 dark:text-white truncate">Paint Studio</h3>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5 line-clamp-2">
                                Kanvas gambar interaktif untuk menggambar bebas, sketsa cepat, dengan color picker dan ekspor berkas.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="mt-3.5 pt-2.5 border-t border-neutral-100 dark:border-white/5 flex items-center justify-between">
                    <span class="text-[10px] text-neutral-400 dark:text-neutral-500">MiniOS Team • 24 KB</span>
                    <button
                        type="button"
                        wire:click="installCatalogApp('paint')"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-xs font-semibold shadow-2xs active:scale-95 transition-all"
                    >
                        <flux:icon name="arrow-down-tray" class="size-3.5" />
                        <span>{{ __('Pasang 1-Klik') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- SECTION: PREVIEW TEMA UNGGULAN --}}
    {{-- ========================================================= --}}
    <div class="rounded-2xl border border-neutral-200/90 dark:border-white/5 bg-gradient-to-r from-fuchsia-600/10 via-purple-600/10 to-indigo-600/10 p-4 @sm:p-5 flex flex-col @[540px]:flex-row items-start @[540px]:items-center justify-between gap-4">
        <div class="space-y-1 min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <flux:icon name="swatch" class="size-4.5 text-fuchsia-600 dark:text-fuchsia-400" />
                <h3 class="text-xs @sm:text-sm font-bold text-neutral-900 dark:text-white truncate">{{ __('Koleksi Tema & Visual Desktop') }}</h3>
                <span class="rounded bg-fuchsia-100 dark:bg-fuchsia-500/20 text-fuchsia-700 dark:text-fuchsia-300 text-[10px] font-bold px-2 py-0.5 shrink-0">
                    {{ __('Segera Hadir') }}
                </span>
            </div>
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-relaxed">
                {{ __('Ubah suasana desktop dengan palet warna eksklusif seperti Cyberpunk Neon 2077, Nordic Frost, dan macOS Glass.') }}
            </p>
        </div>

        <button
            type="button"
            wire:click="setTab('themes')"
            class="inline-flex items-center gap-1.5 shrink-0 rounded-xl bg-neutral-900 dark:bg-white text-white dark:text-neutral-900 px-3.5 py-2 text-xs font-bold shadow-sm hover:opacity-90 active:scale-95 transition-all"
        >
            <span>{{ __('Lihat Tema') }}</span>
            <flux:icon name="arrow-right" class="size-3.5" />
        </button>
    </div>
</div>
