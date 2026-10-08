@props([
    'app' => [],
    'missing' => [],
    'id' => '',
])

@php
    $appName = $app['name'] ?? 'Aplikasi';
    $appIcon = $app['icon'] ?? 'apps';
    $command = 'composer require ' . implode(' ', $missing);
@endphp

<div class="flex h-full w-full flex-col justify-between overflow-y-auto bg-neutral-50/95 dark:bg-[#1f1f1f]/95 p-6 sm:p-8 text-neutral-800 dark:text-neutral-200 select-text" x-data="{ copied: false }">
    <div class="mx-auto w-full max-w-xl space-y-6">
        {{-- Header Card --}}
        <div class="flex items-start gap-4 rounded-xl border border-amber-500/20 bg-amber-500/5 dark:bg-amber-500/10 p-4 sm:p-5">
            <div class="flex size-12 shrink-0 items-center justify-center rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400">
                <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Dependensi Diperlukan</h3>
                    <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-[11px] font-medium text-amber-700 dark:text-amber-300">
                        Belum Siap
                    </span>
                </div>
                <p class="text-xs text-neutral-600 dark:text-neutral-400 leading-relaxed">
                    Aplikasi <strong class="text-neutral-900 dark:text-neutral-100">{{ $appName }}</strong> membutuhkan paket Composer eksternal sebelum fiturnya dapat digunakan di MiniOS.
                </p>
            </div>
        </div>

        {{-- Missing Packages List --}}
        <div class="rounded-xl border border-neutral-200/80 dark:border-white/10 bg-white dark:bg-[#282828] p-4 shadow-sm space-y-3">
            <div class="flex items-center justify-between border-b border-neutral-100 dark:border-white/5 pb-2.5">
                <span class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">Paket yang Belum Terpasang:</span>
                <span class="text-[11px] text-neutral-500">{{ count($missing) }} paket</span>
            </div>
            <div class="space-y-2">
                @foreach ($missing as $pkg)
                    <div class="flex items-center justify-between rounded-lg bg-neutral-50 dark:bg-white/5 px-3 py-2 text-xs">
                        <div class="flex items-center gap-2 font-mono text-neutral-800 dark:text-neutral-200">
                            <svg class="size-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span>{{ $pkg }}</span>
                        </div>
                        <span class="rounded bg-rose-500/10 px-2 py-0.5 text-[10px] font-medium text-rose-600 dark:text-rose-400 border border-rose-500/20">
                            Missing
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Step-by-Step Instructions --}}
        <div class="space-y-3">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-neutral-500 dark:text-neutral-400">
                Langkah-Langkah Aktivasi:
            </h4>

            {{-- Step 1 --}}
            <div class="rounded-xl border border-neutral-200/80 dark:border-white/10 bg-white dark:bg-[#282828] p-4 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-neutral-900 dark:text-neutral-100">
                        1. Pasang paket melalui Terminal atau GUI Control Panel:
                    </span>
                    <button
                        type="button"
                        @click="
                            navigator.clipboard.writeText('{{ $command }}');
                            copied = true;
                            setTimeout(() => copied = false, 2000);
                        "
                        class="inline-flex items-center gap-1.5 rounded-lg bg-neutral-100 dark:bg-white/10 px-2.5 py-1 text-[11px] font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-200 dark:hover:bg-white/20 transition"
                    >
                        <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span x-text="copied ? 'Tersalin!' : 'Salin Perintah'"></span>
                    </button>
                </div>
                <div class="relative overflow-hidden rounded-lg bg-neutral-900 text-neutral-200 p-3 font-mono text-xs shadow-inner">
                    <div class="flex items-center gap-2 select-all">
                        <span class="text-emerald-400 select-none">$</span>
                        <span>{{ $command }}</span>
                    </div>
                </div>
            </div>

            {{-- Step 2 --}}
            <div class="rounded-xl border border-neutral-200/80 dark:border-white/10 bg-white dark:bg-[#282828] p-4 flex items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <div class="text-xs font-medium text-neutral-900 dark:text-neutral-100">2. Ingin pasang otomatis via GUI?</div>
                    <div class="text-[11px] text-neutral-500">Buka Control Panel &gt; Aplikasi terinstal untuk menjalankan instalasi langsung dari MiniOS.</div>
                </div>
                <button
                    type="button"
                    @click="
                        $dispatch('open-app', { id: 'control-panel', url: '/desktop/control-panel' });
                    "
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 text-xs font-medium shadow-sm transition"
                >
                    <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Buka Control Panel</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Bottom refresh action --}}
    <div class="mx-auto w-full max-w-xl pt-4 border-t border-neutral-200/60 dark:border-white/5 flex items-center justify-between text-xs text-neutral-500">
        <span>Setelah paket terpasang di Laravel, segarkan jendela ini:</span>
        <button
            type="button"
            @click="window.location.reload()"
            class="inline-flex items-center gap-1.5 rounded-lg border border-neutral-300 dark:border-white/10 bg-white dark:bg-neutral-800 px-3 py-1.5 font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-700 transition"
        >
            <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span>Periksa Ulang &amp; Segarkan</span>
        </button>
    </div>
</div>
