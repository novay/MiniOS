{{-- ========================================================= --}}
{{-- MODAL ABOUT KATALOG --}}
{{-- ========================================================= --}}
@if ($showAboutModal)
    <div
        class="absolute inset-0 z-50 flex items-center justify-center p-4 bg-black/50 dark:bg-black/75 backdrop-blur-md animate-in fade-in duration-150 select-none"
    >
        <div
            @click.outside="$wire.closeAboutModal()"
            class="w-full max-w-sm rounded-2xl border border-neutral-200/80 dark:border-white/10 bg-white/95 dark:bg-[#1e1e1e]/95 p-6 shadow-2xl backdrop-blur-2xl text-center space-y-4 transition-all"
        >
            {{-- Close Button in Top-Right --}}
            <div class="flex justify-end -mt-2 -mr-2">
                <button
                    type="button"
                    wire:click="closeAboutModal"
                    class="rounded-lg p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/10 transition"
                    title="{{ __('Tutup') }}"
                >
                    <flux:icon name="x-mark" class="size-4" />
                </button>
            </div>

            {{-- App Logo & Header --}}
            <div class="flex flex-col items-center -mt-2">
                <div class="relative mb-3 flex items-center justify-center">
                    <x-minios.icon name="katalog" class="size-16 object-contain drop-shadow-xl" />
                </div>
                <h3 class="text-base font-bold text-neutral-900 dark:text-white tracking-wide">
                    {{ __('Katalog') }}
                </h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 font-mono">
                    {{ __('Versi 1.0.0 (MiniOS Package Manager)') }}
                </p>
            </div>

            <div class="h-px w-full bg-neutral-200/80 dark:bg-white/10"></div>

            {{-- App Description & Metadata Card --}}
            <div class="text-xs text-neutral-600 dark:text-neutral-300 space-y-2.5 leading-relaxed text-left bg-neutral-50/80 dark:bg-white/5 p-3.5 rounded-xl border border-neutral-200/80 dark:border-white/5">
                <p class="text-[12px] leading-relaxed">
                    {{ __('Katalog adalah pusat distribusi dan manajemen aplikasi MiniOS desktop. Jelajahi, pasang modul baru via arsip ZIP, dan kelola aplikasi terpasang dengan mudah.') }}
                </p>

                <div class="pt-2 text-[11px] text-neutral-500 dark:text-neutral-400 space-y-1.5 border-t border-neutral-200/80 dark:border-white/10">
                    <div class="flex justify-between items-center">
                        <span>{{ __('Arsitektur') }}</span>
                        <span class="font-medium text-neutral-800 dark:text-neutral-200">Livewire 3 & Flux UI</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>{{ __('Tema UI') }}</span>
                        <span class="font-medium text-neutral-800 dark:text-neutral-200">Fluent Mica / Acrylic</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>{{ __('Aplikasi Terpasang') }}</span>
                        <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $stats['total'] ?? 0 }} {{ __('Aplikasi') }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>{{ __('Pengembang') }}</span>
                        <span class="font-medium text-neutral-800 dark:text-neutral-200">Novay (Btekno)</span>
                    </div>
                </div>
            </div>

            {{-- Modal Footer Actions --}}
            <div class="flex items-center justify-between gap-3 pt-1">
                <button
                    type="button"
                    @click="$dispatch('open-window', { id: 'docs' }); $wire.closeAboutModal();"
                    class="inline-flex items-center gap-1.5 text-xs text-[var(--accent-color,#3b82f6)] hover:underline font-medium"
                >
                    <flux:icon name="book-open" class="size-3.5" />
                    <span>{{ __('Dokumentasi') }}</span>
                </button>

                <button
                    type="button"
                    wire:click="closeAboutModal"
                    class="rounded-xl bg-neutral-900 text-white hover:bg-neutral-800 dark:bg-white/10 dark:hover:bg-white/15 dark:text-white px-4 py-2 text-xs font-semibold transition active:scale-95 shadow-sm"
                >
                    {{ __('Tutup') }}
                </button>
            </div>
        </div>
    </div>
@endif
