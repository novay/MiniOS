@props([
    'app' => [],
    'missing' => [],
    'id' => '',
])

@php
    $appName = $app['name'] ?? 'Aplikasi';
    $appIcon = $app['icon'] ?? 'apps';
    $command = 'composer require ' . implode(' ', $missing);

    $accentColor = function_exists('os_setting') ? (os_setting('appearance.accent_color') ?? 'indigo') : 'indigo';
    $accentHex = match ($accentColor) {
        'zinc' => '#27272a',
        'emerald' => '#10b981',
        'sky' => '#0ea5e9',
        'amber' => '#f59e0b',
        'rose' => '#f43f5e',
        default => '#0067c0', // Windows 11 Default Accent Blue
    };
@endphp

<div
    class="relative flex h-full w-full flex-col justify-between overflow-y-auto bg-[#f3f3f3]/95 dark:bg-[#202020]/95 p-6 sm:p-8 text-neutral-800 dark:text-neutral-100 select-text font-sans backdrop-blur-2xl"
    x-data="{
        copied: false,
        copiedPkg: null,
        isRefreshing: false,
        copyCommand() {
            navigator.clipboard.writeText('{{ $command }}');
            this.copied = true;
            setTimeout(() => this.copied = false, 2000);
        },
        copySingle(pkg) {
            navigator.clipboard.writeText('composer require ' + pkg);
            this.copiedPkg = pkg;
            setTimeout(() => this.copiedPkg = null, 2000);
        }
    }"
>
    {{-- Windows 11 Ambient Glow --}}
    <div
        class="pointer-events-none absolute -right-12 -top-12 size-48 rounded-full blur-3xl opacity-15 dark:opacity-20"
        style="background-color: var(--accent-color, {{ $accentHex }});"
    ></div>

    <div class="relative mx-auto w-full max-w-xl space-y-5">
        {{-- ========================================================= --}}
        {{-- WINDOWS 11 INFOBAR (WARNING NOTIFICATION BANNER)          --}}
        {{-- ========================================================= --}}
        <div class="relative overflow-hidden rounded-xl border border-amber-500/30 bg-amber-500/10 dark:bg-amber-500/15 p-4 sm:p-4.5 shadow-2xs backdrop-blur-sm">
            <div class="flex items-start gap-3.5">
                {{-- Windows 11 Fluent Warning Icon Badge --}}
                <div class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white dark:bg-amber-500 dark:text-neutral-950 shadow-xs">
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                </div>

                <div class="min-w-0 flex-1 space-y-1">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="text-sm font-semibold tracking-tight text-neutral-900 dark:text-neutral-100">
                            {{ __('Dependencies Required') }}
                        </h3>
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-500/20 dark:bg-amber-400/20 border border-amber-500/30 px-2 py-0.5 text-[11px] font-medium text-amber-800 dark:text-amber-300">
                            <span class="size-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            {{ __('Not Ready') }}
                        </span>
                    </div>
                    <p class="text-xs text-neutral-600 dark:text-neutral-300/90 leading-relaxed">
                        {!! str_replace(':app', '<strong class="font-semibold text-neutral-900 dark:text-white">' . e($appName) . '</strong>', e(__('The :app application requires external Composer packages before its features can be used in MiniOS.'))) !!}
                    </p>
                </div>
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- APP CARD (FLUENT IDENTITY HEADER)                         --}}
        {{-- ========================================================= --}}
        <div class="flex items-center justify-between gap-4 rounded-xl border border-black/[0.08] dark:border-white/[0.08] bg-white/80 dark:bg-[#2b2b2b]/80 p-3.5 shadow-2xs backdrop-blur-md">
            <div class="flex items-center gap-3 min-w-0">
                <div class="relative flex size-11 shrink-0 items-center justify-center rounded-xl bg-neutral-100 dark:bg-white/10 p-1.5 border border-black/5 dark:border-white/10 shadow-2xs">
                    <x-minios.icon :name="$appIcon" class="size-7" />
                </div>
                <div class="min-w-0">
                    <h4 class="text-xs font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                        {{ $appName }}
                    </h4>
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 truncate">
                        ID: <code class="font-mono text-neutral-700 dark:text-neutral-300">{{ $id }}</code>
                    </p>
                </div>
            </div>
            <span class="shrink-0 rounded-full bg-neutral-200/70 dark:bg-white/10 px-2.5 py-0.5 text-[11px] font-medium text-neutral-600 dark:text-neutral-300">
                {{ __(count($missing) > 1 ? ':count packages required' : ':count package required', ['count' => count($missing)]) }}
            </span>
        </div>

        {{-- ========================================================= --}}
        {{-- MISSING PACKAGES LIST (WINDOWS 11 SETTINGS LISTVIEW)      --}}
        {{-- ========================================================= --}}
        <div class="overflow-hidden rounded-xl border border-black/[0.08] dark:border-white/[0.08] bg-white/80 dark:bg-[#2b2b2b]/80 shadow-2xs backdrop-blur-md">
            <div class="flex items-center justify-between border-b border-black/[0.06] dark:border-white/[0.06] bg-neutral-50/60 dark:bg-white/[0.02] px-4 py-2.5">
                <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-200">
                    {{ __('Uninstalled Packages:') }}
                </span>
                <span class="text-[11px] font-medium text-neutral-500 dark:text-neutral-400">
                    {{ __(count($missing) > 1 ? ':count packages' : ':count package', ['count' => count($missing)]) }}
                </span>
            </div>

            <div class="divide-y divide-black/[0.05] dark:divide-white/[0.05]">
                @foreach ($missing as $pkg)
                    <div class="group flex items-center justify-between gap-3 px-4 py-2.5 text-xs transition-colors hover:bg-black/[0.02] dark:hover:bg-white/[0.03]">
                        <div class="flex items-center gap-2.5 font-mono text-neutral-800 dark:text-neutral-200 min-w-0">
                            {{-- Fluent Package Icon --}}
                            <div class="flex size-6 shrink-0 items-center justify-center rounded-md bg-neutral-100 dark:bg-white/10 text-neutral-500 dark:text-neutral-400 group-hover:text-neutral-700 dark:group-hover:text-neutral-200 transition-colors">
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m7.5 4.27 9 5.15"/>
                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                                    <path d="m3.3 7 8.7 5 8.7-5"/>
                                    <path d="M12 22V12"/>
                                </svg>
                            </div>
                            <span class="truncate font-medium">{{ $pkg }}</span>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button
                                type="button"
                                @click="copySingle('{{ $pkg }}')"
                                title="{{ __('Copy command for this package') }}"
                                class="hidden sm:inline-flex items-center gap-1 rounded px-1.5 py-0.5 text-[10px] text-neutral-500 hover:text-neutral-800 dark:text-neutral-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                            >
                                <svg class="size-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <span x-text="copiedPkg === '{{ $pkg }}' ? '{{ __('Copied') }}' : '{{ __('Copy') }}'"></span>
                            </button>
                            <span class="rounded-md bg-rose-500/10 px-2 py-0.5 text-[10px] font-semibold text-rose-600 dark:text-rose-400 border border-rose-500/20 uppercase tracking-wider">
                                {{ __('Missing') }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ========================================================= --}}
        {{-- STEP-BY-STEP INSTRUCTIONS (WINDOWS 11 STYLE)              --}}
        {{-- ========================================================= --}}
        <div class="space-y-3">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400 flex items-center gap-1.5">
                <svg class="size-3.5 text-neutral-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="9 11 12 14 22 4"></polyline>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                <span>{{ __('Activation Steps:') }}</span>
            </h4>

            {{-- Step 1: Windows Terminal Box --}}
            <div class="rounded-xl border border-black/[0.08] dark:border-white/[0.08] bg-white/80 dark:bg-[#2b2b2b]/80 p-4 space-y-3 shadow-2xs backdrop-blur-md">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-medium text-neutral-900 dark:text-neutral-100">
                        {{ __('1. Install package via Terminal or GUI Control Panel:') }}
                    </span>
                    <button
                        type="button"
                        @click="copyCommand()"
                        class="inline-flex items-center gap-1.5 rounded-md border border-black/10 dark:border-white/10 bg-neutral-100 dark:bg-white/10 px-2.5 py-1 text-[11px] font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-200 dark:hover:bg-white/20 active:scale-[0.98] transition-all cursor-pointer shadow-2xs"
                    >
                        <template x-if="!copied">
                            <svg class="size-3.5 text-neutral-500 dark:text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </template>
                        <template x-if="copied">
                            <svg class="size-3.5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </template>
                        <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy Command') }}'"></span>
                    </button>
                </div>

                {{-- Authentic Windows Terminal Frame --}}
                <div class="overflow-hidden rounded-lg border border-neutral-800 bg-[#0c0c0c] text-neutral-200 shadow-inner">
                    {{-- Windows Terminal Tab Bar --}}
                    <div class="flex items-center justify-between border-b border-neutral-800 bg-[#181818] px-3 py-1.5 text-[11px] select-none text-neutral-400 font-mono">
                        <div class="flex items-center gap-2">
                            <svg class="size-3.5 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="4 17 10 11 4 5"></polyline>
                                <line x1="12" y1="19" x2="20" y2="19"></line>
                            </svg>
                            <span class="text-neutral-200 font-sans text-[11px]">Windows PowerShell</span>
                        </div>
                        <div class="flex items-center gap-1.5 opacity-60">
                            <span class="size-2 rounded-full bg-neutral-600"></span>
                            <span class="size-2 rounded-full bg-neutral-600"></span>
                            <span class="size-2 rounded-full bg-neutral-600"></span>
                        </div>
                    </div>

                    {{-- Terminal Console Content --}}
                    <div class="p-3 font-mono text-xs flex items-center gap-2 select-all overflow-x-auto">
                        <span class="text-sky-400 select-none font-semibold">PS&gt;</span>
                        <span class="text-neutral-100 tracking-wide">{{ $command }}</span>
                    </div>
                </div>
            </div>

            {{-- Step 2: Fluent Quick Action Card --}}
            <div class="rounded-xl border border-black/[0.08] dark:border-white/[0.08] bg-white/80 dark:bg-[#2b2b2b]/80 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs backdrop-blur-md">
                <div class="flex items-start gap-3">
                    <div class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-xs">
                        <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1.5"/>
                            <rect x="14" y="3" width="7" height="7" rx="1.5"/>
                            <rect x="3" y="14" width="7" height="7" rx="1.5"/>
                            <circle cx="17.5" cy="17.5" r="3.5"/>
                        </svg>
                    </div>
                    <div class="space-y-0.5">
                        <div class="text-xs font-semibold text-neutral-900 dark:text-neutral-100">
                            {{ __('2. Want to install automatically via GUI?') }}
                        </div>
                        <div class="text-[11px] text-neutral-500 dark:text-neutral-400 leading-normal">
                            {{ __('Open Control Panel > Installed Apps to run the installation directly from MiniOS.') }}
                        </div>
                    </div>
                </div>

                {{-- Windows 11 Primary Accent Button --}}
                <button
                    type="button"
                    @click="
                        if (typeof openApplication === 'function') {
                            openApplication('control-panel');
                        }
                        $dispatch('open-app', { id: 'control-panel', url: '/desktop/control-panel' });
                    "
                    class="shrink-0 inline-flex items-center justify-center gap-2 rounded-md px-3.5 py-2 text-xs font-medium text-white shadow-xs transition-all active:scale-[0.98] cursor-pointer"
                    style="background-color: var(--accent-color, {{ $accentHex }});"
                >
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path>
                        <polyline points="15 3 21 3 21 9"></polyline>
                        <line x1="10" y1="14" x2="21" y2="3"></line>
                    </svg>
                    <span>{{ __('Open Control Panel') }}</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- WINDOWS 11 DIALOG FOOTER / COMMAND BAR                    --}}
    {{-- ========================================================= --}}
    <div class="relative mx-auto w-full max-w-xl pt-5 mt-6 border-t border-black/[0.08] dark:border-white/[0.08] flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-neutral-500 dark:text-neutral-400">
        <div class="flex items-center gap-1.5 text-center sm:text-left">
            <svg class="size-3.5 text-neutral-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <span>{{ __('After packages are installed in Laravel, refresh this window:') }}</span>
        </div>

        {{-- Windows 11 Standard / Secondary Button --}}
        <button
            type="button"
            @click="
                isRefreshing = true;
                setTimeout(() => window.location.reload(), 250);
            "
            class="inline-flex items-center gap-2 rounded-md border border-black/15 dark:border-white/15 bg-white dark:bg-[#2d2d2d] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-[#383838] active:scale-[0.98] transition-all shadow-2xs cursor-pointer"
        >
            <svg
                class="size-3.5 text-neutral-500 dark:text-neutral-400 transition-transform duration-500"
                :class="{ 'animate-spin': isRefreshing }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>{{ __('Check Again & Refresh') }}</span>
        </button>
    </div>
</div>
