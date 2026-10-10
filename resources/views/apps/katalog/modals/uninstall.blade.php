@if ($appToUninstall)
    <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-sm p-4 animate-in fade-in duration-150">
        <div class="w-full max-w-md rounded-2xl border border-neutral-200 dark:border-white/10 bg-white dark:bg-[#2c2c2c] p-6 shadow-2xl text-left">
            <div class="flex items-center gap-3 border-b border-neutral-200/80 dark:border-white/5 pb-4">
                <div class="flex size-10 items-center justify-center rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400">
                    <flux:icon name="exclamation-triangle" class="size-5" />
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('modal_uninstall_title') }}</h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('modal_uninstall_desc') }}</p>
                </div>
            </div>

            <div class="mt-4">
                <p class="text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed">
                    {{ $this->t('uninstall_confirm_msg', ['name' => $appToUninstallDetails['name'] ?? $appToUninstall]) }}
                </p>
            </div>

            <div class="mt-6 flex items-center justify-end gap-2 border-t border-neutral-200/80 dark:border-white/5 pt-4">
                <button
                    type="button"
                    wire:click="cancelUninstall"
                    class="rounded-xl border border-neutral-300 dark:border-white/10 bg-white dark:bg-[#323232] px-4 py-2 text-xs font-semibold text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-[#3c3c3c] transition-all"
                >
                    {{ $this->t('btn_cancel') }}
                </button>
                <button
                    type="button"
                    wire:click="uninstallApp"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white px-4 py-2 text-xs font-bold shadow-xs transition-all disabled:opacity-50"
                >
                    <span wire:loading.remove wire:target="uninstallApp">{{ $this->t('btn_uninstall_app') }}</span>
                    <span wire:loading wire:target="uninstallApp" class="inline-flex items-center gap-1">
                        <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>{{ $this->t('btn_uninstalling') }}</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif
