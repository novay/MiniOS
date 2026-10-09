<div
    x-data="{
        tab: @entangle('activeTab'),
        copied: false,
        copySpecs() {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(@js($this->copySpecsText)).then(() => {
                    this.copied = true;
                    setTimeout(() => { this.copied = false; }, 2200);
                });
            }
        }
    }"
    class="flex h-full flex-col justify-between bg-[#f3f3f3]/95 dark:bg-[#202020]/95 text-neutral-800 dark:text-neutral-100 select-none p-5 sm:p-6 backdrop-blur-2xl font-sans overflow-y-auto"
>
    <div class="space-y-4">
        {{-- ========================================================= --}}
        {{-- WINDOWS 11 HERO BRANDING HEADER                           --}}
        {{-- ========================================================= --}}
        <div class="relative overflow-hidden rounded-2xl border border-neutral-200/80 dark:border-white/10 bg-white/80 dark:bg-white/5 p-4 sm:p-5 shadow-2xs backdrop-blur-xl">
            {{-- Ambient Glow --}}
            <div
                class="pointer-events-none absolute -right-6 -top-6 size-28 rounded-full blur-2xl opacity-20 dark:opacity-30"
                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
            ></div>

            <div class="flex items-center gap-4">
                {{-- MiniOS Logo with Accent Ring --}}
                <div class="relative flex size-14 sm:size-16 shrink-0 items-center justify-center rounded-2xl bg-white dark:bg-[#1a1a1a] p-2 shadow-md border border-neutral-200/70 dark:border-white/10">
                    <img src="{{ asset('minios/images/logo.png') }}" alt="MiniOS Logo" class="size-full object-contain drop-shadow" onerror="this.src='/minios/images/logo.png'" />
                    <span
                        class="absolute -bottom-1 -right-1 flex size-4 items-center justify-center rounded-full text-white text-[9px] shadow-2xs"
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                    >
                        <flux:icon name="check" class="size-2.5" />
                    </span>
                </div>

                {{-- Branding Info --}}
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h2 class="text-lg sm:text-xl font-bold tracking-tight text-neutral-900 dark:text-white">
                            MiniOS
                        </h2>
                        <span class="inline-flex items-center rounded-full bg-neutral-200/80 dark:bg-white/10 px-2 py-0.5 text-[10px] font-semibold text-neutral-700 dark:text-neutral-300">
                            {{ $this->trans('edition') }}
                        </span>
                    </div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 font-medium truncate">
                        {{ $this->trans('tagline') }}
                    </p>
                    <p class="text-[11px] text-neutral-400 dark:text-neutral-500 mt-0.5">
                        Versi {{ $this->miniosVersion }} • {{ $this->buildNumber }}
                    </p>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- FLUENT SEGMENTED TAB SWITCHER                             --}}
        {{-- ========================================================= --}}
        <div class="flex rounded-xl bg-neutral-200/70 dark:bg-white/5 p-1 text-xs font-medium border border-neutral-200/60 dark:border-white/5">
            <button
                type="button"
                wire:click="setTab('specs')"
                @click="tab = 'specs'"
                :class="tab === 'specs'
                    ? 'bg-white dark:bg-[#2c2c2c] text-neutral-900 dark:text-white shadow-2xs font-semibold'
                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'"
                class="flex flex-1 items-center justify-center gap-1.5 rounded-lg py-1.5 transition-all"
            >
                <flux:icon name="cpu-chip" class="size-3.5" />
                <span>{{ $this->trans('tab_specs') }}</span>
            </button>

            <button
                type="button"
                wire:click="setTab('about')"
                @click="tab = 'about'"
                :class="tab === 'about'
                    ? 'bg-white dark:bg-[#2c2c2c] text-neutral-900 dark:text-white shadow-2xs font-semibold'
                    : 'text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white'"
                class="flex flex-1 items-center justify-center gap-1.5 rounded-lg py-1.5 transition-all"
            >
                <flux:icon name="information-circle" class="size-3.5" />
                <span>{{ $this->trans('tab_about') }}</span>
            </button>
        </div>

        {{-- ========================================================= --}}
        {{-- TAB CONTENT 1: SPESIFIKASI SISTEM (SPECS)                 --}}
        {{-- ========================================================= --}}
        <div x-show="tab === 'specs'" class="space-y-3">
            <div class="overflow-hidden rounded-xl border border-neutral-200/80 dark:border-white/10 bg-white/90 dark:bg-white/5 shadow-2xs divide-y divide-neutral-100 dark:divide-white/5 text-xs">
                {{-- Device Name --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="computer-desktop" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('device_name') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 text-[11px] font-mono">{{ $this->hostname }}</span>
                </div>

                {{-- Processor --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="cpu-chip" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('processor') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 text-right truncate max-w-[200px] sm:max-w-xs" title="{{ $this->processor }}">{{ $this->processor }}</span>
                </div>

                {{-- Installed RAM --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="chart-bar-square" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('memory') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $this->installedRam }}</span>
                </div>

                {{-- Host OS --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="server" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('host_system') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 text-[11px]">{{ $this->hostOs }}</span>
                </div>

                {{-- User --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="user" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('user') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $this->userName }}</span>
                </div>

                {{-- Framework --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="cube" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('framework') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">Laravel {{ $this->laravelVersion }} &amp; Livewire {{ $this->livewireVersion }}</span>
                </div>

                {{-- UI Components --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="squares-plus" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('ui_components') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">Flux UI {{ $this->fluxVersion }} &amp; Tailwind CSS</span>
                </div>

                {{-- PHP Runtime --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="code-bracket" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('runtime') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">PHP {{ $this->phpVersion }} ({{ $this->phpSapi }})</span>
                </div>

                {{-- Database Engine --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="circle-stack" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('database') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200 uppercase text-[11px]">{{ $this->dbConnection }}</span>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- TAB CONTENT 2: LISENSI & PENGEMBANG                       --}}
        {{-- ========================================================= --}}
        <div x-show="tab === 'about'" x-cloak class="space-y-3">
            <div class="overflow-hidden rounded-xl border border-neutral-200/80 dark:border-white/10 bg-white/90 dark:bg-white/5 shadow-2xs divide-y divide-neutral-100 dark:divide-white/5 text-xs">
                {{-- Author / Creator --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="user-circle" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('author') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">Enter(wind) / Novay</span>
                </div>

                {{-- License --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="shield-check" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('license') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">MIT Open Source</span>
                </div>

                {{-- UI Inspiration --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="sparkles" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('ui_design') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">Windows 11 Fluent Design</span>
                </div>

                {{-- Ecosystem --}}
                <div class="flex items-center justify-between px-3.5 py-2.5">
                    <span class="flex items-center gap-2 text-neutral-500 dark:text-neutral-400 font-medium">
                        <flux:icon name="globe-alt" class="size-3.5 text-neutral-400 shrink-0" />
                        <span>{{ $this->trans('ecosystem') }}</span>
                    </span>
                    <span class="font-semibold text-neutral-800 dark:text-neutral-200">Laravel Web Desktop</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- BOTTOM ACTIONS & COPYRIGHT                                --}}
    {{-- ========================================================= --}}
    <div class="mt-5 space-y-3 pt-3 border-t border-neutral-200/70 dark:border-white/5">
        <div class="flex items-center gap-2">
            {{-- Copy Specs Button --}}
            <button
                type="button"
                @click="copySpecs()"
                class="flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-xs font-medium text-neutral-800 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-white/10 active:scale-98 transition-all"
            >
                <template x-if="!copied">
                    <span class="flex items-center gap-1.5">
                        <flux:icon name="clipboard-document" class="size-3.5 text-neutral-500" />
                        <span>{{ $this->trans('copy_specs') }}</span>
                    </span>
                </template>
                <template x-if="copied">
                    <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold">
                        <flux:icon name="check" class="size-3.5" />
                        <span>{{ $this->trans('copied') }}</span>
                    </span>
                </template>
            </button>

            {{-- Open Settings Button --}}
            <button
                type="button"
                @click="openWindow('settings'); closeWindow('about')"
                class="flex items-center justify-center gap-1.5 rounded-lg border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-xs font-medium text-neutral-800 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-white/10 active:scale-98 transition-all"
                title="Buka Pengaturan Sistem"
            >
                <flux:icon name="cog-6-tooth" class="size-3.5 text-neutral-500" />
                <span class="hidden sm:inline">{{ $this->trans('settings') }}</span>
            </button>

            {{-- Close Button --}}
            <button
                type="button"
                @click="closeWindow('about')"
                class="flex items-center justify-center rounded-lg px-3.5 py-2 text-xs font-medium text-white shadow-2xs active:scale-98 transition-all"
                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
            >
                {{ $this->trans('close') }}
            </button>
        </div>

        <div class="text-center text-[10px] text-neutral-400 dark:text-neutral-500">
            {{ date('Y') }} &copy; Enter(wind). All rights reserved.
        </div>
    </div>
</div>
