<div class="flex flex-col h-full w-full bg-slate-950 text-slate-100 select-none overflow-hidden font-sans relative"
     x-data="{
        playing: false,
        currentTime: 0,
        duration: 0,
        volume: 1,
        muted: false,
        loop: false,
        speed: 1,
        speeds: [0.5, 1, 1.25, 1.5, 2],
        speedIndex: 1,
        isFullscreen: false,
        isSeeking: false,
        controlsVisible: true,
        hideTimeout: null,

        init() {
            this.$watch('$wire.mediaData', () => {
                this.$nextTick(() => this.resetMedia());
            });

            window.addEventListener('player-media-loaded', () => {
                this.$nextTick(() => this.resetMedia());
            });

            window.addEventListener('player-media-closed', () => {
                this.playing = false;
                this.currentTime = 0;
                this.duration = 0;
            });
        },

        getMediaEl() {
            return this.$refs.videoEl || this.$refs.audioEl;
        },

        resetMedia() {
            this.playing = false;
            this.currentTime = 0;
            this.duration = 0;
            const el = this.getMediaEl();
            if (el) {
                el.currentTime = 0;
                el.volume = this.volume;
                el.muted = this.muted;
                el.playbackRate = this.speed;
                el.loop = this.loop;
            }
        },

        togglePlay() {
            const el = this.getMediaEl();
            if (!el) return;
            if (el.paused) {
                el.play().then(() => {
                    this.playing = true;
                }).catch(() => {
                    this.playing = false;
                });
            } else {
                el.pause();
                this.playing = false;
            }
        },

        onTimeUpdate() {
            const el = this.getMediaEl();
            if (el && !this.isSeeking) {
                this.currentTime = el.currentTime;
                if (!this.duration || isNaN(this.duration)) {
                    this.duration = el.duration || 0;
                }
            }
        },

        onLoadedMetadata() {
            const el = this.getMediaEl();
            if (el) {
                this.duration = el.duration || 0;
                this.currentTime = el.currentTime || 0;
            }
        },

        onEnded() {
            this.playing = false;
            if (!this.loop) {
                this.currentTime = 0;
            }
        },

        seek(e) {
            const el = this.getMediaEl();
            if (el) {
                el.currentTime = parseFloat(e.target.value);
                this.currentTime = el.currentTime;
            }
        },

        skip(seconds) {
            const el = this.getMediaEl();
            if (el) {
                el.currentTime = Math.max(0, Math.min(this.duration || 0, el.currentTime + seconds));
                this.currentTime = el.currentTime;
            }
        },

        setVolume(val) {
            this.volume = parseFloat(val);
            const el = this.getMediaEl();
            if (el) {
                el.volume = this.volume;
                if (this.volume > 0 && this.muted) {
                    this.muted = false;
                    el.muted = false;
                }
            }
        },

        toggleMute() {
            this.muted = !this.muted;
            const el = this.getMediaEl();
            if (el) {
                el.muted = this.muted;
            }
        },

        toggleLoop() {
            this.loop = !this.loop;
            const el = this.getMediaEl();
            if (el) {
                el.loop = this.loop;
            }
        },

        cycleSpeed() {
            this.speedIndex = (this.speedIndex + 1) % this.speeds.length;
            this.speed = this.speeds[this.speedIndex];
            const el = this.getMediaEl();
            if (el) {
                el.playbackRate = this.speed;
            }
        },

        toggleFullscreen() {
            const container = this.$refs.playerContainer;
            if (!container) return;
            if (!document.fullscreenElement) {
                container.requestFullscreen().then(() => {
                    this.isFullscreen = true;
                }).catch(() => {});
            } else {
                document.exitFullscreen().then(() => {
                    this.isFullscreen = false;
                }).catch(() => {});
            }
        },

        formatTime(sec) {
            if (!sec || isNaN(sec)) return '00:00';
            const s = Math.floor(sec % 60);
            const m = Math.floor((sec / 60) % 60);
            const h = Math.floor(sec / 3600);
            const pad = (n) => String(n).padStart(2, '0');
            if (h > 0) {
                return `${pad(h)}:${pad(m)}:${pad(s)}`;
            }
            return `${pad(m)}:${pad(s)}`;
        },

        handleMouseMove() {
            this.controlsVisible = true;
            clearTimeout(this.hideTimeout);
            if (this.playing && this.$wire.mediaType === 'video') {
                this.hideTimeout = setTimeout(() => {
                    this.controlsVisible = false;
                }, 2800);
            }
        }
     }"
     @keydown.window.prevent.space="if ($el.closest('.active-window') || $el.contains(document.activeElement)) togglePlay()"
     @keydown.window.prevent.arrow-left="if ($el.closest('.active-window') || $el.contains(document.activeElement)) skip(-5)"
     @keydown.window.prevent.arrow-right="if ($el.closest('.active-window') || $el.contains(document.activeElement)) skip(5)"
     @keydown.window.prevent.m="if ($el.closest('.active-window') || $el.contains(document.activeElement)) toggleMute()"
     @mousemove="handleMouseMove()"
     x-ref="playerContainer">

    {{-- Top Window Toolbar --}}
    <div class="h-11 shrink-0 px-3 bg-slate-900/90 backdrop-blur border-b border-white/10 flex items-center justify-between text-xs transition-opacity duration-300 z-20"
         :class="{ 'opacity-0 pointer-events-none': !controlsVisible && isFullscreen }">
        
        {{-- Left: File Info & Badge --}}
        <div class="flex items-center space-x-2.5 min-w-0 mr-2">
            <div class="size-6 shrink-0 rounded-lg bg-gradient-to-tr from-purple-600 to-pink-500 flex items-center justify-center text-white shadow-sm">
                @if($mediaType === 'video')
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                @else
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 18V5l12-2v13"></path>
                        <circle cx="6" cy="18" r="3"></circle>
                        <circle cx="18" cy="16" r="3"></circle>
                    </svg>
                @endif
            </div>

            <div class="truncate font-medium text-slate-200" title="{{ $fileName ?? $this->trans('app_name') }}">
                {{ $fileName ?? $this->trans('no_media_loaded') }}
            </div>

            @if($extension)
                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-white/10 text-slate-300 border border-white/10 shrink-0">
                    {{ $extension }}
                </span>
            @endif

            @if($fileSize)
                <span class="text-[11px] text-slate-400 shrink-0 hidden sm:inline">
                    {{ $fileSize }}
                </span>
            @endif
        </div>

        {{-- Right: Actions --}}
        <div class="flex items-center space-x-1 shrink-0">
            @if($mediaType !== 'empty')
                {{-- Info Inspector Button --}}
                <button type="button"
                        wire:click="toggleInfo"
                        class="p-1.5 rounded-md hover:bg-white/10 text-slate-400 hover:text-white transition"
                        title="{{ $this->trans('btn_info') }}">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="16" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                    </svg>
                </button>

                {{-- Download Button --}}
                <button type="button"
                        wire:click="downloadMedia"
                        class="p-1.5 rounded-md hover:bg-white/10 text-slate-400 hover:text-white transition"
                        title="{{ $this->trans('btn_download') }}">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                </button>

                {{-- Close File Button --}}
                <button type="button"
                        wire:click="closeMedia"
                        class="p-1.5 rounded-md hover:bg-rose-500/20 text-slate-400 hover:text-rose-300 transition"
                        title="{{ $this->trans('btn_close') }}">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- Info Inspector Drawer / Popover --}}
    @if($showInfo && $mediaType !== 'empty')
        <div class="px-4 py-3 bg-slate-900/95 border-b border-white/10 text-xs text-slate-300 flex flex-wrap items-center gap-x-6 gap-y-2 shrink-0 animate-fadeIn">
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold">{{ $this->trans('lbl_file_name') }}</span>
                <span class="font-medium text-slate-200">{{ $fileName }}</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold">{{ $this->trans('lbl_file_path') }}</span>
                <span class="font-mono text-slate-300">{{ $filePath }}</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold">{{ $this->trans('lbl_file_size') }}</span>
                <span>{{ $fileSize }}</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold">{{ $this->trans('lbl_type') }}</span>
                <span class="uppercase">{{ $extension }} ({{ $mimeType }})</span>
            </div>
            <div>
                <span class="text-slate-500 block text-[10px] uppercase font-semibold">{{ $this->trans('lbl_duration') }}</span>
                <span x-text="formatTime(duration)">--:--</span>
            </div>
        </div>
    @endif

    {{-- Main Viewport Area --}}
    <div class="flex-1 min-h-0 relative flex items-center justify-center bg-black/60 overflow-hidden">

        @if($mediaType === 'video' && $mediaData)
            {{-- Video Player Container --}}
            <div class="relative size-full flex items-center justify-center group"
                 @dblclick="toggleFullscreen()">
                <video x-ref="videoEl"
                       src="{{ $mediaData }}"
                       class="max-w-full max-h-full object-contain cursor-pointer shadow-2xl"
                       @timeupdate="onTimeUpdate()"
                       @loadedmetadata="onLoadedMetadata()"
                       @ended="onEnded()"
                       @click="togglePlay()"
                       playsinline>
                </video>

                {{-- Center Play/Pause Overlay on Hover or Paused --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none transition-opacity duration-300"
                     :class="{ 'opacity-100': !playing, 'opacity-0': playing && !controlsVisible }">
                    <button type="button"
                            @click="togglePlay()"
                            class="size-16 rounded-full bg-black/50 backdrop-blur-md border border-white/20 text-white flex items-center justify-center pointer-events-auto hover:scale-110 hover:bg-black/70 active:scale-95 transition shadow-2xl">
                        <template x-if="!playing">
                            <svg class="size-8 ml-1" viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </template>
                        <template x-if="playing">
                            <svg class="size-8" viewBox="0 0 24 24" fill="currentColor">
                                <rect x="6" y="4" width="4" height="16"></rect>
                                <rect x="14" y="4" width="4" height="16"></rect>
                            </svg>
                        </template>
                    </button>
                </div>
            </div>

        @elseif($mediaType === 'audio' && $mediaData)
            {{-- Audio Player Vinyl / Visualization Canvas --}}
            <div class="flex flex-col items-center justify-center p-6 text-center select-none max-w-sm">
                <audio x-ref="audioEl"
                       src="{{ $mediaData }}"
                       @timeupdate="onTimeUpdate()"
                       @loadedmetadata="onLoadedMetadata()"
                       @ended="onEnded()">
                </audio>

                {{-- Vinyl Record Disc Visualizer --}}
                <div class="relative size-44 sm:size-52 rounded-full p-2.5 bg-gradient-to-tr from-slate-900 via-slate-800 to-slate-950 border-4 border-slate-700/60 shadow-[0_0_50px_rgba(168,85,247,0.25)] flex items-center justify-center transition duration-500"
                     :class="{ 'rotate-animation': playing }">
                    {{-- Vinyl Grooves Ring --}}
                    <div class="size-full rounded-full border border-dashed border-white/10 flex items-center justify-center">
                        <div class="size-3/4 rounded-full border border-dashed border-white/10 flex items-center justify-center">
                            {{-- Center Label Hub --}}
                            <div class="size-20 sm:size-24 rounded-full bg-gradient-to-tr from-purple-600 via-pink-600 to-indigo-600 flex items-center justify-center shadow-inner border-2 border-slate-950 text-white relative">
                                <div class="size-4 rounded-full bg-slate-950 border border-white/20"></div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Track Info --}}
                <div class="mt-6">
                    <h3 class="font-semibold text-base sm:text-lg text-slate-100 truncate max-w-xs" title="{{ $fileName }}">
                        {{ $fileName }}
                    </h3>
                    <p class="text-xs text-purple-400 font-medium mt-1 uppercase tracking-wider">
                        {{ $this->trans('audio_track') }} &bull; {{ $extension }}
                    </p>
                </div>

                {{-- Waveform Bars Micro-Animation --}}
                <div class="flex items-end justify-center space-x-1.5 h-6 mt-4">
                    <span class="w-1 bg-purple-500 rounded-full transition-all duration-300" :class="playing ? 'h-5 animate-pulse' : 'h-1.5 opacity-40'"></span>
                    <span class="w-1 bg-purple-400 rounded-full transition-all duration-300" :class="playing ? 'h-6 animate-pulse delay-75' : 'h-2.5 opacity-40'"></span>
                    <span class="w-1 bg-pink-500 rounded-full transition-all duration-300" :class="playing ? 'h-3 animate-pulse delay-150' : 'h-1 opacity-40'"></span>
                    <span class="w-1 bg-indigo-400 rounded-full transition-all duration-300" :class="playing ? 'h-5 animate-pulse delay-100' : 'h-2 opacity-40'"></span>
                    <span class="w-1 bg-purple-500 rounded-full transition-all duration-300" :class="playing ? 'h-4 animate-pulse delay-200' : 'h-1.5 opacity-40'"></span>
                </div>
            </div>

        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center p-8 text-center max-w-md">
                <div class="size-20 rounded-3xl bg-gradient-to-tr from-purple-600/20 via-pink-600/20 to-indigo-600/20 border border-white/10 flex items-center justify-center text-purple-400 mb-4 shadow-xl">
                    <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75">
                        <polygon points="5 3 19 12 5 21 5 3"></polygon>
                    </svg>
                </div>
                <h2 class="text-base font-semibold text-slate-200">
                    {{ $this->trans('no_media_loaded') }}
                </h2>
                <p class="text-xs text-slate-400 mt-2 leading-relaxed">
                    {{ $this->trans('empty_desc') }}
                </p>
                <button type="button"
                        @click="$dispatch('open-app', { app: 'files' })"
                        class="mt-5 px-4 py-2 rounded-xl bg-purple-600 hover:bg-purple-500 active:bg-purple-700 text-white font-medium text-xs shadow-lg shadow-purple-600/30 flex items-center space-x-2 transition">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>{{ $this->trans('btn_open_files') }}</span>
                </button>
            </div>
        @endif

    </div>

    {{-- Bottom Playback Controls Bar --}}
    @if($mediaType !== 'empty')
        <div class="shrink-0 bg-slate-900/95 backdrop-blur-md border-t border-white/10 px-4 py-2.5 flex flex-col space-y-1.5 transition-opacity duration-300 z-20"
             :class="{ 'opacity-0 pointer-events-none': !controlsVisible && isFullscreen }">

            {{-- Seek Timeline Track --}}
            <div class="flex items-center space-x-3 text-[11px] text-slate-400 font-mono select-none">
                <span class="w-11 text-right shrink-0" x-text="formatTime(currentTime)">00:00</span>
                <div class="flex-1 relative flex items-center group py-1 cursor-pointer">
                    <input type="range"
                           min="0"
                           :max="duration || 100"
                           step="0.1"
                           :value="currentTime"
                           @input="isSeeking = true; currentTime = parseFloat($event.target.value)"
                           @change="seek($event); isSeeking = false"
                           class="w-full h-1.5 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-purple-500 focus:outline-none group-hover:h-2 transition-all">
                </div>
                <span class="w-11 text-left shrink-0" x-text="formatTime(duration)">00:00</span>
            </div>

            {{-- Control Buttons Row --}}
            <div class="flex items-center justify-between">
                
                {{-- Left: Speed & Loop --}}
                <div class="flex items-center space-x-1 sm:space-x-2 w-1/4">
                    {{-- Playback Speed --}}
                    <button type="button"
                            @click="cycleSpeed()"
                            class="px-2 py-1 rounded-md text-[11px] font-semibold text-slate-400 hover:text-white hover:bg-white/10 transition"
                            :title="'{{ $this->trans('btn_speed') }}: ' + speed + 'x'">
                        <span x-text="speed + 'x'">1x</span>
                    </button>

                    {{-- Loop Toggle --}}
                    <button type="button"
                            @click="toggleLoop()"
                            class="p-1.5 rounded-md transition"
                            :class="loop ? 'text-purple-400 bg-purple-500/20' : 'text-slate-400 hover:text-white hover:bg-white/10'"
                            title="{{ $this->trans('btn_loop') }}">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="17 1 21 5 17 9"></polyline>
                            <path d="M3 11V9a4 4 0 0 1 4-4h14"></path>
                            <polyline points="7 23 3 19 7 15"></polyline>
                            <path d="M21 13v2a4 4 0 0 1-4 4H3"></path>
                        </svg>
                    </button>
                </div>

                {{-- Center: Transport Controls (Rewind, Play/Pause, Forward) --}}
                <div class="flex items-center space-x-2 sm:space-x-3 justify-center flex-1">
                    {{-- Skip -10s --}}
                    <button type="button"
                            @click="skip(-10)"
                            class="p-2 rounded-full text-slate-300 hover:text-white hover:bg-white/10 active:scale-95 transition"
                            title="-10 detik">
                        <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 17l-5-5 5-5"></path>
                            <path d="M18 17l-5-5 5-5"></path>
                        </svg>
                    </button>

                    {{-- Play / Pause Big Button --}}
                    <button type="button"
                            @click="togglePlay()"
                            class="size-10 sm:size-11 rounded-full bg-gradient-to-tr from-purple-600 to-pink-500 hover:from-purple-500 hover:to-pink-400 active:scale-95 text-white flex items-center justify-center shadow-lg shadow-purple-600/30 transition"
                            :title="playing ? '{{ $this->trans('btn_pause') }}' : '{{ $this->trans('btn_play') }}'">
                        <template x-if="!playing">
                            <svg class="size-5 ml-0.5" viewBox="0 0 24 24" fill="currentColor">
                                <polygon points="5 3 19 12 5 21 5 3"></polygon>
                            </svg>
                        </template>
                        <template x-if="playing">
                            <svg class="size-5" viewBox="0 0 24 24" fill="currentColor">
                                <rect x="6" y="4" width="4" height="16"></rect>
                                <rect x="14" y="4" width="4" height="16"></rect>
                            </svg>
                        </template>
                    </button>

                    {{-- Skip +10s --}}
                    <button type="button"
                            @click="skip(10)"
                            class="p-2 rounded-full text-slate-300 hover:text-white hover:bg-white/10 active:scale-95 transition"
                            title="+10 detik">
                        <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M13 17l5-5-5-5"></path>
                            <path d="M6 17l5-5-5-5"></path>
                        </svg>
                    </button>
                </div>

                {{-- Right: Volume & Fullscreen --}}
                <div class="flex items-center justify-end space-x-2 sm:space-x-3 w-1/4">
                    {{-- Volume Group --}}
                    <div class="flex items-center space-x-1.5">
                        <button type="button"
                                @click="toggleMute()"
                                class="p-1.5 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition"
                                :title="muted ? '{{ $this->trans('btn_unmute') }}' : '{{ $this->trans('btn_mute') }}'">
                            <template x-if="muted || volume == 0">
                                <svg class="size-4 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                    <line x1="23" y1="9" x2="17" y2="15"></line>
                                    <line x1="17" y1="9" x2="23" y2="15"></line>
                                </svg>
                            </template>
                            <template x-if="!muted && volume > 0 && volume < 0.5">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                    <path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                </svg>
                            </template>
                            <template x-if="!muted && volume >= 0.5">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                    <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                                </svg>
                            </template>
                        </button>
                        
                        <input type="range"
                               min="0"
                               max="1"
                               step="0.05"
                               :value="muted ? 0 : volume"
                               @input="setVolume($event.target.value)"
                               class="w-16 sm:w-20 h-1.5 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-purple-500 hidden sm:block">
                    </div>

                    {{-- Fullscreen Toggle (If Video) --}}
                    @if($mediaType === 'video')
                        <button type="button"
                                @click="toggleFullscreen()"
                                class="p-1.5 rounded-md text-slate-400 hover:text-white hover:bg-white/10 transition"
                                title="{{ $this->trans('btn_fullscreen') }}">
                            <template x-if="!isFullscreen">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"></path>
                                </svg>
                            </template>
                            <template x-if="isFullscreen">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                                </svg>
                            </template>
                        </button>
                    @endif

                </div>

            </div>

        </div>
    @endif

    <style>
        @keyframes spinSlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .rotate-animation {
            animation: spinSlow 12s linear infinite;
        }
    </style>
</div>
