{{-- Modal About SoundCloud --}}
@if ($showAboutModal)
    <div class="absolute inset-0 z-50 flex items-center justify-center p-4 bg-black/50 dark:bg-black/75 backdrop-blur-md">
        <div
            @click.outside="$wire.closeAbout()"
            class="w-full max-w-sm rounded-2xl border border-black/10 dark:border-white/15 bg-white dark:bg-[#1c1c1f] p-6 shadow-2xl text-center space-y-4"
        >
            <div class="flex justify-end -mt-2 -mr-2">
                <button
                    type="button"
                    wire:click="closeAbout"
                    class="rounded-lg p-1 text-neutral-400 hover:text-neutral-700 dark:hover:text-white hover:bg-neutral-100 dark:hover:bg-white/10 transition"
                    title="{{ $this->t('btn_close') }}"
                >
                    <flux:icon name="x-mark" class="size-4" />
                </button>
            </div>

            {{-- App Logo & Title --}}
            <div class="flex flex-col items-center -mt-3">
                <img src="{{ asset('minios/images/ic-soundcloud.webp') }}" class="size-16 object-contain drop-shadow-xl mb-3" alt="SoundCloud" />
                <h3 class="text-base font-bold text-neutral-900 dark:text-white tracking-wide">{{ $this->t('modal_about_title') }}</h3>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 font-mono">v{{ $version }} (Custom Player)</p>
            </div>

            <div class="h-px w-full bg-neutral-200 dark:bg-white/10"></div>

            {{-- Description & Details --}}
            <div class="text-xs text-neutral-600 dark:text-neutral-300 space-y-2.5 leading-relaxed text-left bg-neutral-50 dark:bg-black/35 p-3.5 rounded-xl border border-neutral-200 dark:border-white/5">
                <p>
                    {{ $this->t('modal_about_desc') }}
                </p>
                <div class="pt-1.5 text-[11px] text-neutral-500 dark:text-neutral-400 space-y-1.5 border-t border-neutral-200 dark:border-white/10">
                    <div class="flex justify-between">
                        <span>{{ $this->t('modal_about_author_label') }}</span>
                        <span class="text-neutral-800 dark:text-neutral-200">{{ $this->t('modal_about_author_val') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>{{ $this->t('modal_about_developer_label') }}</span>
                        <span class="text-neutral-800 dark:text-neutral-200">{{ $this->t('modal_about_developer_val') }}</span>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between gap-3 pt-2">
                <a
                    href="https://soundcloud.com"
                    target="_blank"
                    class="inline-flex items-center gap-1.5 text-xs text-[#ff5500] hover:text-[#ff7700] hover:underline"
                >
                    <flux:icon name="arrow-top-right-on-square" class="size-3.5" />
                    <span>soundcloud.com</span>
                </a>

                <button
                    type="button"
                    wire:click="closeAbout"
                    class="rounded-xl bg-neutral-100 hover:bg-neutral-200 text-neutral-800 dark:bg-white/10 dark:hover:bg-white/15 dark:text-white px-4 py-2 text-xs font-semibold transition active:scale-95"
                >
                    {{ $this->t('btn_close') }}
                </button>
            </div>
        </div>
    </div>
@endif
