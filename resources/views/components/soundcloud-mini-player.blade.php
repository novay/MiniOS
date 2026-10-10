<div
    x-data="{
        scPlaying: false,
        scTrack: null,
        scPositionSec: 0,
        scDurationSec: 0,
        wasActiveInBackground: false,
        dismissed: false,

        init() {
            window.dispatchEvent(new CustomEvent('minios-sc-request-state'));

            this.$watch('isSoundcloudActive', (isActive) => {
                if (isActive) {
                    this.wasActiveInBackground = false;
                    this.dismissed = false;
                }
            });
        },

        get isSoundcloudActive() {
            return this.activeWindow === 'soundcloud' && this.isWindowVisible('soundcloud');
        },

        get isSoundcloudRunning() {
            return Boolean(this.isWindowRunning('soundcloud'));
        },

        get shouldShowMiniPlayer() {
            if (!this.isSoundcloudRunning || this.dismissed || this.isSoundcloudActive) {
                return false;
            }

            if (this.scPlaying && this.scTrack) {
                this.wasActiveInBackground = true;
                return true;
            }

            return this.wasActiveInBackground && Boolean(this.scTrack);
        },

        get progressPercent() {
            if (!this.scDurationSec || this.scDurationSec <= 0) return 0;
            return Math.min(100, Math.max(0, (this.scPositionSec / this.scDurationSec) * 100));
        },

        handleOpenSoundcloud() {
            this.openApplication('soundcloud');
        },

        handleTogglePlay() {
            window.dispatchEvent(new CustomEvent('minios-sc-play-toggle'));
        },

        handleNext() {
            window.dispatchEvent(new CustomEvent('minios-sc-next'));
        },

        handlePrev() {
            window.dispatchEvent(new CustomEvent('minios-sc-prev'));
        },

        handleDismiss() {
            this.dismissed = true;
        }
    }"
    @minios-sc-playback.window="
        scPlaying = $event.detail.isPlaying;
        if ($event.detail.track) {
            scTrack = $event.detail.track;
        }
        if ($event.detail.positionSec !== undefined) {
            scPositionSec = $event.detail.positionSec;
        }
        if ($event.detail.durationSec !== undefined) {
            scDurationSec = $event.detail.durationSec;
        }
        if (scPlaying) {
            dismissed = false;
        }
    "
    @minios-sc-progress.window="
        if ($event.detail.positionSec !== undefined) {
            scPositionSec = $event.detail.positionSec;
        }
        if ($event.detail.durationSec !== undefined) {
            scDurationSec = $event.detail.durationSec;
        }
    "
    x-cloak
    x-show="shouldShowMiniPlayer"
    x-transition:enter="transition-all duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
    x-transition:enter-start="opacity-0 -translate-x-8 scale-95"
    x-transition:enter-end="opacity-100 translate-x-0 scale-100"
    x-transition:leave="transition-all duration-200 ease-in"
    x-transition:leave-start="opacity-100 translate-x-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-x-8 scale-95"
    style="display: none;"
    class="fixed z-[9500] select-none pointer-events-auto"
    :class="{
        'bottom-16 sm:bottom-20 left-4 sm:left-6': (settings?.dock?.position ?? 'bottom') === 'bottom',
        'bottom-6 left-20': (settings?.dock?.position ?? 'bottom') === 'left',
        'bottom-6 left-4 sm:left-6': (settings?.dock?.position ?? 'bottom') === 'right',
    }"
>
    <div
        @click="handleOpenSoundcloud()"
        class="group relative flex items-center gap-3 w-72 sm:w-80 p-2.5 rounded-2xl bg-white/90 dark:bg-[#18181b]/92 backdrop-blur-2xl border border-black/10 dark:border-white/10 shadow-[0_14px_38px_rgba(0,0,0,0.18)] dark:shadow-[0_20px_45px_rgba(0,0,0,0.6)] overflow-hidden cursor-pointer hover:border-black/20 dark:hover:border-white/20 hover:shadow-2xl transition-all duration-200"
        title="{{ __('Kembali ke SoundCloud') }}"
    >
        {{-- Artwork / Cover --}}
        <div class="relative shrink-0 w-12 h-12 rounded-xl overflow-hidden bg-neutral-200 dark:bg-neutral-800 shadow-sm">
            <img
                :src="scTrack?.artwork || 'https://i1.sndcdn.com/artworks-XKcM15C0Q7C6On4B-pgVVDw-t500x500.jpg'"
                :alt="scTrack?.title || 'SoundCloud'"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            />

            {{-- Equalizer visualizer overlay on artwork when playing --}}
            <div
                x-show="scPlaying"
                class="absolute inset-0 bg-black/35 flex items-center justify-center pointer-events-none"
            >
                <div class="flex items-end gap-0.5 h-3">
                    <span class="w-[2.5px] bg-white rounded-full animate-[pulse_0.8s_ease-in-out_infinite]" style="height: 60%"></span>
                    <span class="w-[2.5px] bg-[#ff5500] rounded-full animate-[pulse_0.6s_ease-in-out_infinite]" style="height: 100%"></span>
                    <span class="w-[2.5px] bg-white rounded-full animate-[pulse_1s_ease-in-out_infinite]" style="height: 45%"></span>
                </div>
            </div>
        </div>

        {{-- Track Metadata --}}
        <div class="flex-1 min-w-0 pr-1">
            <div class="flex items-center gap-1.5 mb-0.5">
                <span class="inline-flex items-center gap-1 text-[10px] font-bold tracking-wider uppercase text-[#ff5500]">
                    <svg class="w-2.5 h-2.5 fill-current" viewBox="0 0 24 24">
                        <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                    </svg>
                    SoundCloud
                </span>
            </div>
            <p
                class="text-xs font-semibold text-zinc-900 dark:text-zinc-100 truncate leading-snug"
                x-text="scTrack?.title || 'SoundCloud Music'"
            ></p>
            <p
                class="text-[11px] text-zinc-500 dark:text-zinc-400 truncate mt-0.5"
                x-text="scTrack?.artist || scTrack?.uploader || 'SoundCloud'"
            ></p>
        </div>

        {{-- Playback Controls --}}
        <div class="flex items-center gap-1 shrink-0" @click.stop>
            {{-- Prev Track --}}
            <button
                type="button"
                @click="handlePrev()"
                class="p-1.5 rounded-full text-zinc-600 dark:text-zinc-300 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 active:scale-90 transition"
                title="{{ __('Previous') }}"
            >
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="M6 6h2v12H6zm3.5 6 8.5 6V6z"/>
                </svg>
            </button>

            {{-- Play/Pause Button --}}
            <button
                type="button"
                @click="handleTogglePlay()"
                class="w-8 h-8 rounded-full bg-[#ff5500] hover:bg-[#ff3300] active:scale-95 text-white flex items-center justify-center shadow-md shadow-orange-500/25 transition"
                :title="scPlaying ? '{{ __('Pause') }}' : '{{ __('Play') }}'"
            >
                <template x-if="scPlaying">
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                    </svg>
                </template>
                <template x-if="!scPlaying">
                    <svg class="w-4 h-4 fill-current translate-x-0.5" viewBox="0 0 24 24">
                        <path d="M8 5v14l11-7z"/>
                    </svg>
                </template>
            </button>

            {{-- Next Track --}}
            <button
                type="button"
                @click="handleNext()"
                class="p-1.5 rounded-full text-zinc-600 dark:text-zinc-300 hover:text-black dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/10 active:scale-90 transition"
                title="{{ __('Next') }}"
            >
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                    <path d="m6 18 8.5-6L6 6v12zM16 6v12h2V6h-2z"/>
                </svg>
            </button>
        </div>

        {{-- Dismiss Button --}}
        <button
            type="button"
            @click.stop="handleDismiss()"
            class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-black/5 dark:bg-white/10 hover:bg-black/15 dark:hover:bg-white/20 text-zinc-500 dark:text-zinc-400 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity text-[11px] leading-none"
            title="{{ __('Tutup Mini Player') }}"
        >
            &times;
        </button>

        {{-- Bottom Progress Bar --}}
        <div class="absolute bottom-0 left-0 right-0 h-[2.5px] bg-black/5 dark:bg-white/10 overflow-hidden">
            <div
                class="h-full bg-gradient-to-r from-[#ff5500] to-[#ff3300] transition-all duration-300"
                :style="'width: ' + progressPercent + '%'"
            ></div>
        </div>
    </div>
</div>
