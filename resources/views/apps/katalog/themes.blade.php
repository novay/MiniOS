<div class="flex flex-1 flex-col overflow-y-auto p-6 md:p-8 space-y-6">
    {{-- ========================================================= --}}
    {{-- HEADER & BREADCRUMB --}}
    {{-- ========================================================= --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-1.5 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">
                <span>{{ $this->t('app_title') }}</span>
                <flux:icon name="chevron-right" class="size-3 text-neutral-400" />
                <span class="text-neutral-700 dark:text-neutral-300">{{ __('Koleksi Tema') }}</span>
            </div>
            <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-white mt-1">
                {{ __('Tema & Personalisasi Desktop') }}
            </h1>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                {{ __('Ubah estetika visual MiniOS dengan palet warna, transparansi kaca, dan wallpaper eksklusif') }}
            </p>
        </div>

        <div class="inline-flex items-center gap-2 rounded-xl bg-purple-500/10 border border-purple-500/20 px-3 py-1.5 text-xs font-semibold text-purple-700 dark:text-purple-300 self-start md:self-auto">
            <flux:icon name="sparkles" class="size-4 text-purple-500" />
            <span>{{ __('Sinkronisasi Online Siap API') }}</span>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- THEME CATEGORIES --}}
    {{-- ========================================================= --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs select-none">
        @php
            $themeCats = [
                'all' => 'Semua Tema',
                'dark' => 'Dark Mode',
                'minimalist' => 'Minimalis',
                'neon' => 'Neon & Cyberpunk',
                'glass' => 'Aero Glass',
                'retro' => 'Retro Classic',
            ];
        @endphp
        @foreach ($themeCats as $catKey => $catLabel)
            <button
                type="button"
                wire:click="setThemeCategory('{{ $catKey }}')"
                class="rounded-full px-3.5 py-1.5 text-xs font-medium transition-all shrink-0 {{ $themeCategory === $catKey ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 shadow-2xs font-semibold' : 'bg-neutral-200/70 dark:bg-white/5 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-white/10' }}"
            >
                {{ $catLabel }}
            </button>
        @endforeach
    </div>

    {{-- ========================================================= --}}
    {{-- THEMES GRID --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($themes as $theme)
            <div class="flex flex-col justify-between rounded-2xl border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 overflow-hidden shadow-2xs hover:shadow-md hover:border-neutral-300 dark:hover:border-white/10 transition-all group">
                {{-- Mockup Preview Top Area --}}
                <div class="relative h-44 w-full p-4 flex flex-col justify-between overflow-hidden" style="background: {{ $theme['preview_bg'] }};">
                    {{-- Ambient overlay --}}
                    <div class="absolute inset-0 bg-black/15 pointer-events-none"></div>

                    {{-- Top row: Category Badge & Rating --}}
                    <div class="relative z-10 flex items-center justify-between">
                        <span class="rounded-full bg-black/40 backdrop-blur-md px-2.5 py-0.5 text-[10px] font-bold text-white border border-white/20">
                            {{ $theme['badge'] }}
                        </span>
                        <span class="rounded-full bg-black/40 backdrop-blur-md px-2 py-0.5 text-[10px] font-bold text-amber-300 border border-white/20 flex items-center gap-1">
                            ★ {{ $theme['rating'] }}
                        </span>
                    </div>

                    {{-- Mini Desktop Window Simulation --}}
                    <div class="relative z-10 mx-auto w-4/5 rounded-xl bg-white/20 dark:bg-black/40 backdrop-blur-xl border border-white/30 p-2.5 shadow-2xl group-hover:scale-102 transition-transform">
                        {{-- Mini Titlebar --}}
                        <div class="flex items-center justify-between border-b border-white/20 pb-1.5 mb-2">
                            <div class="flex items-center gap-1">
                                <span class="size-2 rounded-full bg-rose-400"></span>
                                <span class="size-2 rounded-full bg-amber-400"></span>
                                <span class="size-2 rounded-full bg-emerald-400"></span>
                            </div>
                            <span class="text-[9px] font-medium text-white/90 truncate">{{ $theme['name'] }}</span>
                            <div class="w-6"></div>
                        </div>

                        {{-- Mini content mockup lines --}}
                        <div class="space-y-1">
                            <div class="h-1.5 w-3/4 rounded-full bg-white/40"></div>
                            <div class="h-1.5 w-1/2 rounded-full bg-white/25"></div>
                        </div>
                    </div>

                    {{-- Mini Dock at Bottom --}}
                    <div class="relative z-10 mx-auto flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/35 backdrop-blur-md border border-white/20">
                        <span class="size-2 rounded-full" style="background-color: {{ $theme['colors'][0] }};"></span>
                        <span class="size-2 rounded-full" style="background-color: {{ $theme['colors'][1] }};"></span>
                        <span class="size-2 rounded-full" style="background-color: {{ $theme['colors'][2] }};"></span>
                        <span class="size-2 rounded-full" style="background-color: {{ $theme['colors'][3] }};"></span>
                    </div>
                </div>

                {{-- Theme Info Body --}}
                <div class="p-4.5 space-y-3">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h3 class="font-bold text-sm text-neutral-900 dark:text-white">{{ $theme['name'] }}</h3>
                            <p class="text-[11px] text-neutral-500 dark:text-neutral-400">by {{ $theme['author'] }} • {{ $theme['downloads'] }} unduhan</p>
                        </div>

                        {{-- Color Swatch Dots --}}
                        <div class="flex items-center -space-x-1 shrink-0 p-1 rounded-full bg-neutral-100 dark:bg-black/30 border border-neutral-200/60 dark:border-white/5">
                            @foreach ($theme['colors'] as $c)
                                <span class="size-3.5 rounded-full ring-2 ring-white dark:ring-neutral-800" style="background-color: {{ $c }};"></span>
                            @endforeach
                        </div>
                    </div>

                    <p class="text-xs text-neutral-600 dark:text-neutral-300 line-clamp-2 leading-relaxed">
                        {{ $theme['description'] }}
                    </p>

                    <div class="pt-3 border-t border-neutral-100 dark:border-white/5 flex items-center justify-between">
                        <span class="text-[11px] font-semibold text-neutral-500 dark:text-neutral-400">
                            {{ $theme['category_label'] }}
                        </span>

                        @if ($theme['is_active'])
                            <span class="inline-flex items-center gap-1 rounded-lg bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400 px-3 py-1.5 text-xs font-bold border border-emerald-200/60 dark:border-emerald-500/20">
                                <flux:icon name="check-circle" class="size-3.5" />
                                <span>{{ __('Aktif Saat Ini') }}</span>
                            </span>
                        @else
                            <button
                                type="button"
                                disabled
                                class="inline-flex items-center gap-1.5 rounded-lg bg-neutral-100 dark:bg-white/10 px-3.5 py-1.5 text-xs font-semibold text-neutral-500 dark:text-neutral-400 cursor-not-allowed opacity-80"
                                title="{{ __('Fitur tema online segera hadir via API minios.btekno.id') }}"
                            >
                                <flux:icon name="clock" class="size-3.5" />
                                <span>{{ __('Segera Hadir') }}</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- API Integration Notice Banner --}}
    <div class="rounded-2xl border border-neutral-200/90 dark:border-white/5 bg-gradient-to-r from-blue-500/5 via-indigo-500/5 to-purple-500/5 p-5 text-center space-y-1.5">
        <h4 class="text-xs font-bold text-neutral-800 dark:text-neutral-200 flex items-center justify-center gap-1.5">
            <flux:icon name="cloud-arrow-down" class="size-4 text-indigo-500" />
            <span>{{ __('Dukungan Tema Online (minios.btekno.id)') }}</span>
        </h4>
        <p class="text-[11px] text-neutral-500 dark:text-neutral-400 max-w-lg mx-auto">
            {{ __('Struktur katalog tema ini disiapkan untuk integrasi langsung dengan REST API web pusat, memungkinkan pengunduhan wallpaper resolusi tinggi dan custom theme pack secara real-time.') }}
        </p>
    </div>
</div>
