<div class="flex flex-col p-4 @sm:p-5 @lg:p-6 space-y-5">
    {{-- ========================================================= --}}
    {{-- HEADER & BREADCRUMB --}}
    {{-- ========================================================= --}}
    <div class="flex flex-col @[540px]:flex-row @[540px]:items-center justify-between gap-3">
        <div class="min-w-0">
            <div class="flex items-center gap-1.5 text-[11px] font-medium text-neutral-500 dark:text-neutral-400">
                <span>{{ $this->t('app_title') }}</span>
                <flux:icon name="chevron-right" class="size-3 text-neutral-400" />
                <span class="text-neutral-700 dark:text-neutral-300">{{ __('Koleksi Tema') }}</span>
            </div>
            <h1 class="text-lg @sm:text-xl font-bold tracking-tight text-neutral-900 dark:text-white mt-1 truncate">
                {{ __('Tema & Personalisasi Desktop') }}
            </h1>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 truncate">
                {{ __('Ubah estetika visual MiniOS dengan palet warna, transparansi kaca, dan wallpaper eksklusif') }}
            </p>
        </div>

        <div class="inline-flex items-center gap-2 rounded-xl bg-purple-500/10 border border-purple-500/20 px-3 py-1.5 text-xs font-semibold text-purple-700 dark:text-purple-300 self-start @[540px]:self-auto shrink-0">
            <flux:icon name="sparkles" class="size-4 text-purple-500" />
            <span>{{ __('Sinkronisasi Online Siap API') }}</span>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- THEME CATEGORIES --}}
    {{-- ========================================================= --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs select-none scrollbar-none">
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
                class="rounded-full px-3 py-1 text-xs font-medium transition-all shrink-0 {{ $themeCategory === $catKey ? 'bg-neutral-900 text-white dark:bg-white dark:text-neutral-900 shadow-2xs font-semibold' : 'bg-neutral-200/70 dark:bg-white/5 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-white/10' }}"
            >
                {{ $catLabel }}
            </button>
        @endforeach
    </div>

    {{-- ========================================================= --}}
    {{-- THEMES GRID --}}
    {{-- ========================================================= --}}
    <div class="grid grid-cols-1 @[540px]:grid-cols-2 @[860px]:grid-cols-3 gap-4">
        @foreach ($themes as $theme)
            <div class="flex flex-col justify-between rounded-2xl border border-neutral-200/90 dark:border-white/5 bg-white dark:bg-[#2b2b2b]/70 overflow-hidden shadow-2xs hover:shadow-md hover:border-neutral-300 dark:hover:border-white/10 transition-all group min-w-0">
                {{-- Mockup Preview Top Area --}}
                <div class="relative h-40 w-full p-3.5 flex flex-col justify-between overflow-hidden" style="background: {{ $theme['preview_bg'] }};">
                    {{-- Ambient overlay --}}
                    <div class="absolute inset-0 bg-black/15 pointer-events-none"></div>

                    {{-- Top Mockup Bar (Simulated window frame) --}}
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 px-2 py-0.5 rounded-md bg-black/30 backdrop-blur-md">
                            <span class="size-2 rounded-full bg-rose-400"></span>
                            <span class="size-2 rounded-full bg-amber-400"></span>
                            <span class="size-2 rounded-full bg-emerald-400"></span>
                            <span class="text-[9px] text-white/80 font-mono ml-1 font-semibold truncate">{{ $theme['name'] }}</span>
                        </div>

                        <span class="text-[9px] font-bold px-2 py-0.5 rounded-full bg-white/20 text-white backdrop-blur-md">
                            {{ $theme['author'] }}
                        </span>
                    </div>

                    {{-- Simulated Window Cards Mockup inside Theme --}}
                    <div class="relative z-10 flex items-center justify-center py-2">
                        <div class="w-4/5 rounded-xl bg-white/25 dark:bg-black/35 backdrop-blur-md border border-white/30 p-2.5 shadow-lg flex items-center gap-2.5">
                            <div class="size-7 rounded-lg flex items-center justify-center text-white" style="background-color: {{ $theme['colors'][0] }};">
                                <flux:icon name="sparkles" class="size-3.5" />
                            </div>
                            <div class="flex-1 min-w-0 space-y-1">
                                <div class="h-2 w-16 bg-white/70 rounded-full"></div>
                                <div class="h-1.5 w-10 bg-white/40 rounded-full"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Color Palette Swatches at Bottom of Preview --}}
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-1.5 bg-black/40 backdrop-blur-md px-2 py-1 rounded-lg">
                            @foreach ($theme['colors'] as $color)
                                <span class="size-3 rounded-full border border-white/40 shadow-xs" style="background-color: {{ $color }};" title="{{ $color }}"></span>
                            @endforeach
                        </div>
                        <span class="text-[9px] text-white/90 font-medium bg-black/30 backdrop-blur-md px-2 py-0.5 rounded-md">
                            Preset
                        </span>
                    </div>
                </div>

                {{-- Bottom Content: Details & Actions --}}
                <div class="p-4 flex flex-col justify-between flex-1 gap-3">
                    <div class="space-y-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-bold text-xs @sm:text-sm text-neutral-900 dark:text-white truncate">
                                {{ $theme['name'] }}
                            </h3>
                            <span class="rounded bg-neutral-100 dark:bg-white/10 px-2 py-0.5 text-[9px] font-mono text-neutral-600 dark:text-neutral-300 shrink-0">
                                {{ $theme['downloads'] }}
                            </span>
                        </div>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 line-clamp-2 leading-relaxed">
                            {{ $theme['description'] }}
                        </p>
                    </div>

                    <div class="pt-2.5 border-t border-neutral-100 dark:border-white/5 flex items-center justify-between gap-2">
                        <span class="text-[10px] text-neutral-400 font-medium">
                            {{ $theme['author'] }}
                        </span>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <button
                                type="button"
                                disabled
                                class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-300/80 dark:border-white/10 bg-neutral-100 dark:bg-white/5 px-2.5 py-1 text-xs font-semibold text-neutral-400 dark:text-neutral-500 cursor-not-allowed opacity-80"
                                title="{{ __('Fitur tema terhubung ke API minios.btekno.id') }}"
                            >
                                <flux:icon name="clock" class="size-3" />
                                <span>{{ __('Segera Hadir') }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
