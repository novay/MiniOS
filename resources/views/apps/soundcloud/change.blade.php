{{-- Modal Configuration / Update Playlist --}}
@if ($showConfigModal)
    <div class="absolute inset-0 z-50 flex items-center justify-center p-4 bg-black/50 dark:bg-black/75 backdrop-blur-md">
        <div
            @click.outside="$wire.closeConfig()"
            class="w-full max-w-md rounded-2xl border border-black/10 dark:border-white/15 bg-white dark:bg-[#1c1c1f] p-5 shadow-2xl text-left space-y-4"
        >
            <div class="flex items-center justify-between border-b border-neutral-200 dark:border-white/10 pb-3">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('minios/images/ic-soundcloud.webp') }}" class="size-4 object-contain" alt="" />
                    <h4 class="text-sm font-bold text-neutral-900 dark:text-white">{{ $this->t('modal_change_title') }}</h4>
                </div>
                <button
                    type="button"
                    wire:click="closeConfig"
                    class="rounded-lg p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/10 transition"
                >
                    <flux:icon name="x-mark" class="size-4" />
                </button>
            </div>

            <div class="space-y-2">
                <label class="block text-xs font-semibold text-neutral-700 dark:text-neutral-300">
                    {{ $this->t('modal_change_label') }}
                </label>
                <textarea
                    wire:model="embedCode"
                    rows="4"
                    class="w-full rounded-xl border border-neutral-300 dark:border-white/10 bg-neutral-50 dark:bg-black/40 p-3 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 dark:placeholder-neutral-500 focus:border-[#ff5500] focus:ring-1 focus:ring-[#ff5500] focus:outline-none transition font-mono leading-relaxed resize-none"
                    placeholder="{{ $this->t('modal_change_placeholder') }}"
                ></textarea>
            </div>

            <div class="flex items-center justify-between gap-2 pt-2 border-t border-neutral-200 dark:border-white/10">
                <button
                    type="button"
                    wire:click="resetPlaylist"
                    class="text-xs text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white hover:underline transition"
                >
                    {{ $this->t('btn_restore_sample') }}
                </button>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="closeConfig"
                        class="rounded-lg px-3 py-1.5 text-xs font-medium text-neutral-600 hover:text-neutral-900 hover:bg-neutral-100 dark:text-neutral-300 dark:hover:text-white dark:hover:bg-white/10 transition"
                    >
                        {{ $this->t('btn_cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="save"
                        class="flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-[#ff5500] to-[#ff7700] hover:from-[#ff6600] hover:to-[#ff8800] px-3.5 py-1.5 text-xs font-semibold text-white shadow-md shadow-[#ff5500]/20 transition"
                    >
                        <flux:icon name="check" class="size-3.5" />
                        <span>{{ $this->t('btn_save_load') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif
