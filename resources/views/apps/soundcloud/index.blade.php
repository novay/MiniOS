<div
    x-data="{
        currentSounds: {{ json_encode($tracks) }},
        currentTrackIndex: 0,
        isPlaying: false,
        currentPositionSec: 0,
        volume: 0.8,
        isMuted: false,
        prevVolume: 0.8,
        isTracklistVisible: true,
        totalBars: 70,
        waveBarHeights: [],
        tooltip: { visible: false, x: 0, text: '0:00' },
        STORAGE_KEY: 'minios_sc_playback_state',
        _isRestoring: false,
        _needsInitialSeek: false,

        get currentTrack() {
            return this.currentSounds[this.currentTrackIndex] || {
                title: {{ json_encode($this->t('lbl_no_track')) }},
                artist: 'SoundCloud',
                uploader: 'SoundCloud',
                artwork: null,
                durationSec: 180,
                durationFormatted: '0:00',
                plays: '0'
            };
        },
        get currentDurationSec() {
            const t = this.currentTrack;
            return t ? (t.durationSec || 180) : 180;
        },
        get progressPercent() {
            const dur = this.currentDurationSec;
            if (!dur) return 0;
            return Math.min(100, Math.max(0, (this.currentPositionSec / dur) * 100));
        },

        savePlaybackState() {
            try {
                const state = {
                    playlistUrl: {{ json_encode($playerUrl) }},
                    trackIndex: this.currentTrackIndex,
                    positionSec: this.currentPositionSec,
                    updatedAt: Date.now()
                };
                localStorage.setItem(this.STORAGE_KEY, JSON.stringify(state));
            } catch (e) {}
        },

        getSavedPlaybackState() {
            try {
                const raw = localStorage.getItem(this.STORAGE_KEY);
                if (!raw) return null;
                const parsed = JSON.parse(raw);
                if (parsed.playlistUrl && parsed.playlistUrl !== {{ json_encode($playerUrl) }}) {
                    return null;
                }
                return parsed;
            } catch (e) {
                return null;
            }
        },

        restoreSavedPlayback() {
            const saved = this.getSavedPlaybackState();
            if (!saved) return;

            if (typeof saved.trackIndex === 'number' && saved.trackIndex >= 0 && saved.trackIndex < this.currentSounds.length) {
                this.currentTrackIndex = saved.trackIndex;
            }

            if (typeof saved.positionSec === 'number' && saved.positionSec >= 0) {
                this.currentPositionSec = saved.positionSec;
                this._needsInitialSeek = true;
            }
        },

        applySavedPlaybackToWidget() {
            if (!window.scWidget) return;

            const saved = this.getSavedPlaybackState();
            if (!saved) return;

            const targetIndex = (typeof saved.trackIndex === 'number' && saved.trackIndex >= 0) ? saved.trackIndex : 0;
            const targetSec = (typeof saved.positionSec === 'number' && saved.positionSec > 0) ? saved.positionSec : 0;

            if (targetIndex === 0 && targetSec === 0) return;

            this._isRestoring = true;

            if (targetIndex > 0) {
                try {
                    window.scWidget.skip(targetIndex);
                } catch (e) {}
            }

            setTimeout(() => {
                try {
                    window.scWidget.pause();
                    this.isPlaying = false;
                    if (targetSec > 0) {
                        window.scWidget.seekTo(targetSec * 1000);
                    }
                    this.broadcastPlayback();
                } catch (e) {}
                setTimeout(() => {
                    this._isRestoring = false;
                }, 400);
            }, targetIndex > 0 ? 500 : 200);
        },

        init() {
            this.generateWaveformBars();
            this.restoreSavedPlayback();
            this.loadSoundCloudWidgetApi();

            window.addEventListener('beforeunload', () => {
                this.savePlaybackState();
            });

            window.addEventListener('soundcloud-load-url', (e) => {
                this.currentTrackIndex = 0;
                this.currentPositionSec = 0;
                this._needsInitialSeek = false;
                this.savePlaybackState();
                if (e.detail && e.detail.tracks && e.detail.tracks.length > 0) {
                    this.currentSounds = e.detail.tracks;
                    this.broadcastPlayback();
                }
                if (e.detail && e.detail.url) {
                    this.loadPlaylistUrl(e.detail.url);
                }
            });

            window.addEventListener('minios-sc-play-toggle', () => this.togglePlay());
            window.addEventListener('minios-sc-next', () => this.nextTrack(this.isPlaying));
            window.addEventListener('minios-sc-prev', () => this.prevTrack());
            window.addEventListener('minios-sc-toggle-list', () => { this.isTracklistVisible = !this.isTracklistVisible; });
            window.addEventListener('minios-sc-request-state', () => this.broadcastPlayback());

            this.broadcastPlayback();
        },

        broadcastPlayback() {
            const cur = this.currentTrack;
            window.dispatchEvent(new CustomEvent('minios-sc-playback', {
                detail: {
                    isPlaying: this.isPlaying,
                    track: cur ? {
                        title: cur.title || 'SoundCloud Track',
                        artist: cur.artist || cur.uploader || 'SoundCloud',
                        uploader: cur.uploader || cur.artist || 'SoundCloud',
                        artwork: cur.artwork || 'https://i1.sndcdn.com/artworks-XKcM15C0Q7C6On4B-pgVVDw-t500x500.jpg',
                        durationFormatted: cur.durationFormatted || '0:00',
                        durationSec: this.currentDurationSec
                    } : null,
                    positionSec: this.currentPositionSec,
                    durationSec: this.currentDurationSec
                }
            }));
        },

        generateWaveformBars() {
            this.waveBarHeights = [];
            for (let i = 0; i < this.totalBars; i++) {
                let h = Math.sin(i * 0.16) * 35 + Math.cos(i * 0.32) * 25 + Math.sin(i * 0.06) * 20 + 35;
                h = Math.max(18, Math.min(94, Math.round(h)));
                this.waveBarHeights.push(h);
            }
        },

        loadSoundCloudWidgetApi() {
            if (window.SC && window.SC.Widget) {
                this.bindWidget();
                return;
            }

            if (!document.getElementById('sc-widget-api-script')) {
                const script = document.createElement('script');
                script.id = 'sc-widget-api-script';
                script.src = 'https://w.soundcloud.com/player/api.js';
                script.onload = () => this.bindWidget();
                document.head.appendChild(script);
            } else {
                const interval = setInterval(() => {
                    if (window.SC && window.SC.Widget) {
                        clearInterval(interval);
                        this.bindWidget();
                    }
                }, 200);
            }
        },

        bindWidget() {
            const iframe = document.getElementById('sc-widget');
            if (!iframe || !window.SC || !window.SC.Widget) return;

            try {
                window.scWidget = window.SC.Widget(iframe);

                window.scWidget.bind(window.SC.Widget.Events.READY, () => {
                    this.fetchSounds();
                    this.applySavedPlaybackToWidget();
                    this.broadcastPlayback();
                });

                window.scWidget.bind(window.SC.Widget.Events.PLAY, () => {
                    if (this._isRestoring) {
                        try { window.scWidget.pause(); } catch(e){}
                        this.isPlaying = false;
                        return;
                    }
                    this.isPlaying = true;
                    this._needsInitialSeek = false;
                    this.broadcastPlayback();
                    try {
                        window.scWidget.getCurrentSoundIndex((idx) => {
                            if (typeof idx === 'number' && idx >= 0 && idx < this.currentSounds.length) {
                                this.currentTrackIndex = idx;
                                this.savePlaybackState();
                            }
                        });
                        window.scWidget.getCurrentSound((sound) => {
                            if (sound && sound.title && this.currentSounds[this.currentTrackIndex]) {
                                const cur = this.currentSounds[this.currentTrackIndex];
                                if (!cur.title || /^Lagu\s+\d+$/i.test(cur.title)) {
                                    cur.title = sound.title;
                                }
                                if (sound.user && sound.user.username) {
                                    cur.artist = sound.user.username;
                                    cur.uploader = sound.user.username;
                                }
                                if (sound.duration) {
                                    cur.durationSec = Math.floor(sound.duration / 1000);
                                    cur.durationFormatted = this.formatTime(cur.durationSec);
                                }
                                if (sound.artwork_url) {
                                    cur.artwork = sound.artwork_url.replace('-large', '-t500x500');
                                }
                                this.broadcastPlayback();
                            }
                        });
                    } catch (e) {}
                });

                window.scWidget.bind(window.SC.Widget.Events.PAUSE, () => {
                    if (!this._isRestoring) {
                        this.isPlaying = false;
                        this.savePlaybackState();
                        this.broadcastPlayback();
                    }
                });

                window.scWidget.bind(window.SC.Widget.Events.PLAY_PROGRESS, (data) => {
                    if (data && typeof data.currentPosition === 'number') {
                        this.currentPositionSec = Math.floor(data.currentPosition / 1000);
                        if (Math.abs(this.currentPositionSec - (this._lastProgressSec || 0)) >= 1) {
                            this._lastProgressSec = this.currentPositionSec;
                            window.dispatchEvent(new CustomEvent('minios-sc-progress', {
                                detail: {
                                    positionSec: this.currentPositionSec,
                                    durationSec: this.currentDurationSec
                                }
                            }));
                            if (this.currentPositionSec % 2 === 0) {
                                this.savePlaybackState();
                            }
                        }
                    }
                });

                window.scWidget.bind(window.SC.Widget.Events.FINISH, () => {
                    this.nextTrack(true);
                });
            } catch (err) {
                console.log('SoundCloud Widget bind note:', err);
            }
        },

        fetchSounds() {
            if (!window.scWidget) return;

            try {
                window.scWidget.getSounds((sounds) => {
                    if (sounds && sounds.length > 0) {
                        this.currentSounds = sounds.map((s, idx) => {
                            const existing = this.currentSounds[idx];
                            const existingTitle = existing && existing.title && !/^Lagu\s+\d+$/i.test(existing.title) ? existing.title : null;
                            const title = s.title || existingTitle || ({{ json_encode($this->t('lbl_track_prefix')) }} + ' ' + (idx + 1));
                            const artist = (s.user && s.user.username) || s.uploader || (existing ? existing.artist : 'SoundCloud Artist');
                            const uploader = (s.user && s.user.username) || (existing ? existing.uploader : 'SoundCloud Artist');
                            const durationSec = s.duration ? Math.floor(s.duration / 1000) : (existing ? existing.durationSec : 180);
                            const artwork = s.artwork_url ? s.artwork_url.replace('-large', '-t500x500') : (existing && existing.artwork ? existing.artwork : 'https://i1.sndcdn.com/artworks-XKcM15C0Q7C6On4B-pgVVDw-t500x500.jpg');

                            return {
                                id: s.id || (existing ? existing.id : (idx + 1)),
                                title: title,
                                artist: artist,
                                uploader: uploader,
                                plays: this.formatNumber(s.playback_count) || (existing ? existing.plays : ''),
                                durationSec: durationSec,
                                durationFormatted: this.formatTime(durationSec),
                                artwork: artwork,
                                scUrl: s.permalink_url || (existing ? existing.scUrl : 'https://soundcloud.com')
                            };
                        });
                        this.broadcastPlayback();
                    }
                });
            } catch (err) {
                console.log('Error fetching sounds from widget:', err);
            }
        },

        selectTrack(index, autoPlay = true) {
            if (index < 0 || index >= this.currentSounds.length) return;
            this.currentTrackIndex = index;
            this.currentPositionSec = 0;
            this._needsInitialSeek = false;
            this._isRestoring = false;
            this.savePlaybackState();

            if (window.scWidget) {
                try {
                    window.scWidget.skip(index);
                    if (autoPlay) {
                        window.scWidget.play();
                        this.isPlaying = true;
                    }
                } catch (e) {}
            }
            this.broadcastPlayback();
        },

        togglePlay() {
            if (this.isPlaying) {
                this.pause();
            } else {
                this.play();
            }
        },

        play() {
            this._isRestoring = false;
            this.isPlaying = true;
            if (window.scWidget) {
                try {
                    if (this._needsInitialSeek && this.currentPositionSec > 0) {
                        window.scWidget.seekTo(this.currentPositionSec * 1000);
                        this._needsInitialSeek = false;
                    }
                    window.scWidget.play();
                } catch(e){}
            }
            this.savePlaybackState();
            this.broadcastPlayback();
        },

        pause() {
            this.isPlaying = false;
            if (window.scWidget) {
                try { window.scWidget.pause(); } catch(e){}
            }
            this.savePlaybackState();
            this.broadcastPlayback();
        },

        prevTrack() {
            const len = this.currentSounds.length;
            if (len === 0) return;
            const prev = (this.currentTrackIndex - 1 + len) % len;
            this.selectTrack(prev, this.isPlaying);
        },

        nextTrack(autoPlay = false) {
            const len = this.currentSounds.length;
            if (len === 0) return;
            const next = (this.currentTrackIndex + 1) % len;
            this.selectTrack(next, autoPlay || this.isPlaying);
        },

        seek(percentage) {
            const targetSec = Math.floor(percentage * this.currentDurationSec);
            this.currentPositionSec = targetSec;
            this._needsInitialSeek = false;
            this.savePlaybackState();
            if (window.scWidget) {
                try { window.scWidget.seekTo(targetSec * 1000); } catch(e){}
            }
        },

        handleWaveHover(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
            const pct = x / rect.width;
            this.tooltip.x = x;
            this.tooltip.text = this.formatTime(Math.floor(pct * this.currentDurationSec));
            this.tooltip.visible = true;
        },

        handleWaveClick(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = Math.max(0, Math.min(e.clientX - rect.left, rect.width));
            const pct = x / rect.width;
            this.seek(pct);
        },

        setVolume(val) {
            this.volume = parseFloat(val);
            this.isMuted = this.volume === 0;
            if (window.scWidget) {
                try { window.scWidget.setVolume(this.volume * 100); } catch(e){}
            }
        },

        toggleMute() {
            if (this.isMuted || this.volume === 0) {
                this.volume = this.prevVolume || 0.8;
                this.isMuted = false;
            } else {
                this.prevVolume = this.volume;
                this.volume = 0;
                this.isMuted = true;
            }
            if (window.scWidget) {
                try { window.scWidget.setVolume(this.volume * 100); } catch(e){}
            }
        },

        loadPlaylistUrl(url) {
            if (!url) return;
            this.apiStatus = 'loading';
            this.apiStatusText = 'Memuat...';

            if (window.scWidget) {
                try {
                    window.scWidget.load(url, {
                        auto_play: this.isPlaying,
                        callback: () => {
                            this.fetchSounds();
                            this.selectTrack(0, this.isPlaying);
                        }
                    });
                } catch(e) {
                    console.log('Error loading SoundCloud URL:', e);
                }
            }
        },

        formatTime(seconds) {
            if (isNaN(seconds) || seconds < 0) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs < 10 ? '0' : ''}${secs}`;
        },

        formatNumber(num) {
            if (!num) return '0';
            if (typeof num === 'string') return num;
            if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
            if (num >= 1000) return (num / 1000).toFixed(0) + 'K';
            return num.toString();
        }
    }"
    class="flex h-full w-full flex-col bg-[#f5f5f7] dark:bg-[#111113] text-neutral-800 dark:text-neutral-100 select-none overflow-hidden font-sans relative"
>
    <style>
        :root {
            --sc-wave-unplayed: rgba(0, 0, 0, 0.22);
            --sc-slider-track: rgba(0, 0, 0, 0.25);
        }
        .dark, :is(.dark, [data-theme="dark"]) {
            --sc-wave-unplayed: rgba(255, 255, 255, 0.38);
            --sc-slider-track: rgba(255, 255, 255, 0.28);
        }
        .sc-hero-overlay {
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.45) 0%, rgba(245, 245, 247, 0.90) 70%, rgba(245, 245, 247, 1) 100%);
        }
        .dark .sc-hero-overlay {
            background: linear-gradient(180deg, rgba(0, 0, 0, 0.45) 0%, rgba(18, 18, 20, 0.90) 70%, rgba(18, 18, 20, 1) 100%);
        }
        .sc-track-active {
            box-shadow: inset 4px 0 0 0 #ff5500;
        }

        /* Custom Volume Slider */
        .sc-slider {
            -webkit-appearance: none;
            appearance: none;
            background: transparent;
            cursor: pointer;
            outline: none;
            height: 18px;
            vertical-align: middle;
        }
        .sc-slider:focus {
            outline: none;
        }
        .sc-slider::-webkit-slider-runnable-track {
            width: 100%;
            height: 4px;
            cursor: pointer;
            background: var(--sc-slider-track);
            border-radius: 9999px;
            border: none;
        }
        .sc-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            height: 15px;
            width: 15px;
            border-radius: 50%;
            background-color: #ff5500;
            border: none;
            cursor: pointer;
            margin-top: -5.5px;
            box-shadow: 0 0 9px 2px rgba(255, 85, 0, 0.75);
            transition: transform 0.1s ease, box-shadow 0.15s ease;
        }
        .sc-slider:hover::-webkit-slider-thumb,
        .sc-slider:active::-webkit-slider-thumb {
            transform: scale(1.12);
            box-shadow: 0 0 12px 3px rgba(255, 85, 0, 0.9);
        }
        .sc-slider::-moz-range-track {
            width: 100%;
            height: 4px;
            cursor: pointer;
            background: var(--sc-slider-track);
            border-radius: 9999px;
            border: none;
        }
        .sc-slider::-moz-range-thumb {
            height: 15px;
            width: 15px;
            border-radius: 50%;
            background-color: #ff5500;
            border: none;
            cursor: pointer;
            box-shadow: 0 0 9px 2px rgba(255, 85, 0, 0.75);
            transition: transform 0.1s ease, box-shadow 0.15s ease;
        }
        .sc-slider:hover::-moz-range-thumb,
        .sc-slider:active::-moz-range-thumb {
            transform: scale(1.12);
            box-shadow: 0 0 12px 3px rgba(255, 85, 0, 0.9);
        }

        .sc-wave-bar {
            transition: height 0.15s ease, background-color 0.1s ease;
        }
    </style>

    {{-- Window App Header Toolbar with Application Menu --}}
    <header class="relative z-40 flex h-9 shrink-0 items-center justify-between border-b border-black/10 dark:border-white/10 px-1.5 bg-white/90 dark:bg-[#18181b]/95 backdrop-blur-md">
        <div class="flex items-center gap-2">
            {{-- Application Menubar --}}
            <x-minios.menubar>
                {{-- Menu: Berkas / Playlist --}}
                <x-minios.menubar.menu label="{{ $this->t('menu_playlist') }}">
                    <x-minios.menubar.item
                        wire:click="openConfig"
                        icon="arrow-path"
                        shortcut="Ctrl+P"
                    >
                        {{ $this->t('menu_change_playlist') }}
                    </x-minios.menubar.item>

                    <x-minios.menubar.submenu label="{{ $this->t('menu_sample_playlist') }}" icon="sparkles">
                        <x-minios.menubar.item wire:click="useSample">
                            Dystopia Raya (AI Cover)
                        </x-minios.menubar.item>
                    </x-minios.menubar.submenu>

                    <x-minios.menubar.separator />

                    @if ($playerUrl)
                        <x-minios.menubar.item
                            @click="navigator.clipboard.writeText('{{ $playerUrl }}'); $dispatch('toast-success', { message: '{{ addslashes($this->t('notif_url_copied')) }}', app: 'SoundCloud', icon: 'soundcloud' })"
                            icon="document-duplicate"
                            shortcut="Ctrl+C"
                        >
                            {{ $this->t('menu_copy_player_url') }}
                        </x-minios.menubar.item>
                    @endif

                    <x-minios.menubar.separator />

                    <x-minios.menubar.item
                        wire:click="resetPlaylist"
                        icon="arrow-path"
                    >
                        {{ $this->t('menu_reset_sample') }}
                    </x-minios.menubar.item>
                </x-minios.menubar.menu>

                {{-- Menu: Pemutar --}}
                <x-minios.menubar.menu label="{{ $this->t('menu_playback') }}">
                    <x-minios.menubar.item
                        @click="$dispatch('minios-sc-play-toggle')"
                        icon="play"
                        shortcut="Space"
                    >
                        {{ $this->t('menu_play_pause') }}
                    </x-minios.menubar.item>
                    <x-minios.menubar.item
                        @click="$dispatch('minios-sc-next')"
                        icon="forward"
                        shortcut="Ctrl+Right"
                    >
                        {{ $this->t('menu_next') }}
                    </x-minios.menubar.item>
                    <x-minios.menubar.item
                        @click="$dispatch('minios-sc-prev')"
                        icon="backward"
                        shortcut="Ctrl+Left"
                    >
                        {{ $this->t('menu_prev') }}
                    </x-minios.menubar.item>
                    <x-minios.menubar.separator />
                    <x-minios.menubar.item
                        @click="$dispatch('minios-sc-toggle-list')"
                        icon="queue-list"
                    >
                        {{ $this->t('menu_toggle_tracklist') }}
                    </x-minios.menubar.item>
                    <x-minios.menubar.separator />
                    <x-minios.menubar.checkbox
                        :checked="true"
                        disabled
                    >
                        {{ $this->t('menu_persistent_audio') }}
                    </x-minios.menubar.checkbox>
                </x-minios.menubar.menu>

                {{-- Menu: Bantuan --}}
                <x-minios.menubar.menu label="{{ $this->t('menu_help') }}">
                    <x-minios.menubar.item
                        href="https://soundcloud.com"
                        target="_blank"
                        icon="arrow-top-right-on-square"
                    >
                        {{ $this->t('menu_open_soundcloud') }}
                    </x-minios.menubar.item>
                    <x-minios.menubar.separator />
                    <x-minios.menubar.item
                        wire:click="openAbout"
                        icon="information-circle"
                    >
                        {{ $this->t('menu_about') }}
                    </x-minios.menubar.item>
                </x-minios.menubar.menu>
            </x-minios.menubar>
        </div>
    </header>

    {{-- Main Music Player Body --}}
    <main class="relative flex-1 w-full h-full overflow-hidden flex flex-col bg-[#f5f5f7] dark:bg-[#121214]">
        
        {{-- HERO SECTION WITH ARTWORK BACKGROUND --}}
        <div
            class="relative w-full shrink-0 flex flex-col justify-between p-4 sm:p-5 sm:pb-4 bg-cover bg-center transition-all duration-500 min-h-[260px] sm:min-h-[290px]"
            :style="`background-image: url('${currentTrack.artwork || 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?q=80&w=1200&auto=format&fit=crop'}')`"
        >
            <!-- Hero Overlay (adapts to light/dark) -->
            <div class="absolute inset-0 sc-hero-overlay pointer-events-none"></div>

            <!-- Top Hero Details & Big Play Button -->
            <div class="relative z-10 flex items-start gap-4">
                
                <!-- Circular Track Count Badge -->
                <div class="mt-1 size-15 sm:size-17 rounded-full bg-white/80 dark:bg-black/80 border border-black/10 dark:border-white/10 backdrop-blur-md flex flex-col items-center justify-center shrink-0 shadow-lg">
                    <span class="text-xl sm:text-2xl font-bold text-neutral-900 dark:text-white leading-none" x-text="currentSounds.length"></span>
                    <span class="text-[8px] sm:text-[9px] font-semibold text-neutral-500 dark:text-neutral-400 tracking-widest uppercase mt-0.5">{{ $this->t('hero_tracks_badge') }}</span>
                </div>

                <div class="flex-1 min-w-0">
                    <span class="inline-block text-[10px] font-bold text-[#ff5500] uppercase tracking-widest mb-0.5">{{ $this->t('hero_playlist') }}</span>
                    <h1 class="text-base sm:text-xl font-bold text-neutral-900 dark:text-white leading-tight truncate drop-shadow-xs dark:drop-shadow-md" x-text="currentTrack.title"></h1>
                    <p class="text-xs text-neutral-600 dark:text-neutral-300 font-medium mt-0.5 truncate" x-text="currentTrack.artist || currentTrack.uploader"></p>
                </div>
            </div>

            <!-- Waveform Visualizer & Track Badge -->
            <div class="relative z-10 my-3 flex items-center gap-3">
                
                <button
                    type="button"
                    @click="togglePlay()"
                    class="size-13 sm:size-15 rounded-full bg-[#ff5500] hover:bg-[#e64d00] text-white flex items-center justify-center shadow-xl shadow-[#ff5500]/30 transition transform active:scale-95 shrink-0 cursor-pointer"
                    :title="isPlaying ? '{{ addslashes($this->t('btn_pause')) }}' : '{{ addslashes($this->t('btn_play')) }}'"
                >
                    <template x-if="isPlaying">
                        <svg class="size-6 text-white" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M176 96c-26.5 0-48 21.5-48 48v352c0 26.5 21.5 48 48 48h64c26.5 0 48-21.5 48-48V144c0-26.5-21.5-48-48-48zm224 0c-26.5 0-48 21.5-48 48v352c0 26.5 21.5 48 48 48h64c26.5 0 48-21.5 48-48V144c0-26.5-21.5-48-48-48z"/></svg>
                    </template>
                    <template x-if="!isPlaying">
                        <svg class="size-6 text-white ml-0.5" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M187.2 100.9c-12.4-6.8-27.4-6.5-39.6.7S128 121.9 128 136v368c0 14.1 7.5 27.2 19.6 34.4s27.2 7.5 39.6.7l336-184c12.8-7 20.8-20.5 20.8-35.1s-8-28.1-20.8-35.1z"/></svg>
                    </template>
                </button>

                <!-- Waveform Bars Container -->
                <div
                    class="flex-1 relative group cursor-pointer py-1"
                    @mousemove="handleWaveHover($event)"
                    @mouseleave="tooltip.visible = false"
                    @click="handleWaveClick($event)"
                >
                    <!-- Time Tooltip -->
                    <div
                        x-show="tooltip.visible"
                        x-cloak
                        class="absolute -top-7 pointer-events-none bg-neutral-900 text-white text-[10px] font-mono px-2 py-0.5 rounded border border-neutral-700 -translate-x-1/2 transition-opacity z-20 shadow-md"
                        :style="`left: ${tooltip.x}px`"
                        x-text="tooltip.text"
                    ></div>

                    <!-- Procedural Waveform Bars -->
                    <div class="w-full h-11 sm:h-13 relative flex items-center gap-[2px] overflow-hidden">
                        <template x-for="(barHeight, idx) in waveBarHeights" :key="idx">
                            <div
                                class="sc-wave-bar flex-1 rounded-full cursor-pointer"
                                :style="`height: ${barHeight}%; background-color: ${(idx / totalBars) <= (progressPercent / 100) ? '#ff5500' : 'var(--sc-wave-unplayed)'}; opacity: ${(idx / totalBars) <= (progressPercent / 100) ? '1' : '0.7'}`"
                            ></div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Playback Controls Bar -->
            <div class="relative z-10 flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-black/10 dark:border-white/10">
                <div class="flex items-center gap-1.5">
                    <button
                        type="button"
                        @click="prevTrack()"
                        class="text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white transition cursor-pointer p-1"
                        title="{{ $this->t('btn_prev') }}"
                    >
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M491 100.8c-12.9-7-28.7-6.3-41 1.8L192 272.1V128c0-17.7-14.3-32-32-32s-32 14.3-32 32v384c0 17.7 14.3 32 32 32s32-14.3 32-32V367.9l258 169.6c12.3 8.1 28 8.8 41 1.8s21-20.5 21-35.2v-368c0-14.7-8.1-28.2-21-35.2z"/></svg>
                    </button>

                    <button
                        type="button"
                        @click="togglePlay()"
                        class="size-5 sm:size-7 rounded-full bg-[#ff5500] hover:bg-[#e64d00] text-white flex items-center justify-center transition active:scale-90 cursor-pointer shadow-md"
                    >
                        <template x-if="isPlaying">
                            <svg class="size-3.5 text-white" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M176 96c-26.5 0-48 21.5-48 48v352c0 26.5 21.5 48 48 48h64c26.5 0 48-21.5 48-48V144c0-26.5-21.5-48-48-48zm224 0c-26.5 0-48 21.5-48 48v352c0 26.5 21.5 48 48 48h64c26.5 0 48-21.5 48-48V144c0-26.5-21.5-48-48-48z"/></svg>
                        </template>
                        <template x-if="!isPlaying">
                            <svg class="size-3.5 text-white ml-0.5" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M187.2 100.9c-12.4-6.8-27.4-6.5-39.6.7S128 121.9 128 136v368c0 14.1 7.5 27.2 19.6 34.4s27.2 7.5 39.6.7l336-184c12.8-7 20.8-20.5 20.8-35.1s-8-28.1-20.8-35.1z"/></svg>
                        </template>
                    </button>

                    <button
                        type="button"
                        @click="nextTrack()"
                        class="text-neutral-600 hover:text-neutral-900 dark:text-neutral-300 dark:hover:text-white transition cursor-pointer p-1"
                        title="{{ $this->t('btn_next') }}"
                    >
                        <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M149 100.8c12.9-7 28.7-6.3 41 1.8l258 169.5V128c0-17.7 14.3-32 32-32s32 14.3 32 32v384c0 17.7-14.3 32-32 32s-32-14.3-32-32V367.9L190 537.5c-12.3 8.1-28 8.8-41 1.8s-21-20.6-21-35.3V136c0-14.7 8.1-28.2 21-35.2"/></svg>
                    </button>

                    <div class="mt-0.5 text-[12px] font-mono font-semibold text-neutral-700 dark:text-neutral-300 ml-1">
                        <span x-text="formatTime(currentPositionSec)"></span><span class="text-neutral-400 dark:text-neutral-500"> / </span><span x-text="formatTime(currentDurationSec)"></span>
                    </div>

                    {{-- <template x-if="currentTrack.plays">
                        <div class="hidden sm:flex items-center gap-1 text-[11px] text-neutral-500 dark:text-neutral-400 ml-2">
                            <span>{{ $this->t('lbl_played') }}</span>
                            <span class="font-semibold text-neutral-800 dark:text-neutral-200" x-text="currentTrack.plays"></span>
                        </div>
                    </template> --}}
                </div>

                <div class="flex items-center gap-3">

                    <!-- Open on SoundCloud external link -->
                    <a
                        :href="currentTrack.scUrl || 'https://soundcloud.com'"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="bg-white/80 hover:bg-white dark:bg-black/50 dark:hover:bg-black/80 border border-black/10 hover:border-black/20 dark:border-white/20 dark:hover:border-white/40 backdrop-blur-md rounded-full px-2 py-0.5 text-[10px] font-medium text-neutral-700 hover:text-neutral-900 dark:text-neutral-200 dark:hover:text-white transition flex items-center gap-1 shadow-xs"
                    >
                        <span>Open on Soundcloud</span>
                    </a>
                    <!-- Volume Slider -->
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="toggleMute()"
                            class="text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white transition text-xs cursor-pointer p-1"
                            title="{{ $this->t('btn_mute_toggle') }}"
                        >
                            <template x-if="isMuted || volume === 0">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M80 416h48l134.1 119.2c6.4 5.7 14.6 8.8 23.1 8.8c19.2 0 34.8-15.6 34.8-34.8V130.8c0-19.2-15.6-34.8-34.8-34.8c-8.5 0-16.7 3.1-23.1 8.8L128 224H80c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48m319-177c-9.4 9.4-9.4 24.6 0 33.9l47 47l-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47l47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47l47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47l-47-47c-9.4-9.4-24.6-9.4-33.9 0"/></svg>
                            </template>
                            <template x-if="!isMuted && volume > 0 && volume < 0.4">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M144 416h48l134.1 119.2c6.4 5.7 14.6 8.8 23.1 8.8c19.2 0 34.8-15.6 34.8-34.8V130.8c0-19.2-15.6-34.8-34.8-34.8c-8.5 0-16.7 3.1-23.1 8.8L192 224h-48c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48m332.6-170.5c-10.3-8.4-25.4-6.8-33.8 3.5s-6.8 25.4 3.5 33.8C457.1 291.6 464 305 464 320s-6.9 28.4-17.7 37.3c-10.3 8.4-11.8 23.5-3.5 33.8s23.5 11.8 33.8 3.5c21.5-17.7 35.4-44.5 35.4-74.6s-13.9-56.9-35.5-74.5z"/></svg>
                            </template>
                            <template x-if="!isMuted && volume >= 0.4 && volume < 0.8">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M112 416h48l134.1 119.2c6.4 5.7 14.6 8.8 23.1 8.8c19.2 0 34.8-15.6 34.8-34.8V130.8c0-19.2-15.6-34.8-34.8-34.8c-8.5 0-16.7 3.1-23.1 8.8L160 224h-48c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48m393.1-245c-10.3-8.4-25.4-6.8-33.8 3.5s-6.8 25.4 3.5 33.8C507.3 234.7 528 274.9 528 320s-20.7 85.3-53.2 111.8c-10.3 8.4-11.8 23.5-3.5 33.8s23.5 11.8 33.8 3.5c43.2-35.2 70.9-88.9 70.9-149s-27.7-113.8-70.9-149zm-60.5 74.5c-10.3-8.4-25.4-6.8-33.8 3.5s-6.8 25.4 3.5 33.8C425.1 291.6 432 305 432 320s-6.9 28.4-17.7 37.3c-10.3 8.4-11.8 23.5-3.5 33.8s23.5 11.8 33.8 3.5c21.5-17.7 35.4-44.5 35.4-74.6s-13.9-56.9-35.5-74.5z"/></svg>
                            </template>
                            <template x-if="!isMuted && volume >= 0.8">
                                <svg class="size-4" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 640 640"><path d="M0 0h640v640H0z" fill="none"/><path fill="currentColor" d="M533.6 96.5c-10.3-8.4-25.4-6.8-33.8 3.5s-6.8 25.4 3.5 33.8c54.2 44 88.7 111 88.7 186.2s-34.5 142.2-88.7 186.3c-10.3 8.4-11.8 23.5-3.5 33.8s23.5 11.8 33.8 3.5C598.5 490.7 640 410.2 640 320S598.5 149.2 533.6 96.5M473.1 171c-10.3-8.4-25.4-6.8-33.8 3.5s-6.8 25.4 3.5 33.8C475.3 234.7 496 274.9 496 320s-20.7 85.3-53.2 111.8c-10.3 8.4-11.8 23.5-3.5 33.8s23.5 11.8 33.8 3.5c43.2-35.2 70.9-88.9 70.9-149s-27.7-113.8-70.9-149zm-60.5 74.5c-10.3-8.4-25.4-6.8-33.8 3.5s-6.8 25.4 3.5 33.8C393.1 291.6 400 305 400 320s-6.9 28.4-17.7 37.3c-10.3 8.4-11.8 23.5-3.5 33.8s23.5 11.8 33.8 3.5c21.5-17.7 35.4-44.5 35.4-74.6s-13.9-56.9-35.4-74.5M80 416h48l134.1 119.2c6.4 5.7 14.6 8.8 23.1 8.8c19.2 0 34.8-15.6 34.8-34.8V130.8c0-19.2-15.6-34.8-34.8-34.8c-8.5 0-16.7 3.1-23.1 8.8L128 224H80c-26.5 0-48 21.5-48 48v96c0 26.5 21.5 48 48 48"/></svg>
                            </template>
                        </button>
                        <input
                            type="range"
                            min="0"
                            max="1"
                            step="0.01"
                            :value="volume"
                            @input="setVolume($event.target.value)"
                            class="sc-slider w-14 sm:w-20"
                        />
                    </div>
                </div>
            </div>
        </div>

        {{-- TRACKLIST SECTION --}}
        <div class="bg-white dark:bg-[#18181b] border-t border-neutral-200 dark:border-neutral-800 flex-1 flex flex-col min-h-0 overflow-hidden">
            <!-- Header of Tracklist -->
            <div class="px-4 py-2.5 flex items-center justify-between border-b border-neutral-200/80 dark:border-neutral-800/80 shrink-0">
                <button
                    type="button"
                    @click="isTracklistVisible = !isTracklistVisible"
                    class="text-xs text-neutral-600 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-neutral-200 flex items-center gap-1.5 transition font-medium cursor-pointer"
                >
                    <flux:icon
                        name="chevron-down"
                        class="size-3 transition-transform"
                        ::class="{ '-rotate-90': !isTracklistVisible }"
                    />
                    <span x-text="isTracklistVisible ? '{{ addslashes($this->t('tracklist_hide')) }}' : '{{ addslashes($this->t('tracklist_show')) }}'"></span>
                </button>
                <div class="text-[11px] text-neutral-500">
                    <span class="text-neutral-700 dark:text-neutral-300 font-semibold" x-text="currentTrackIndex + 1"></span>
                    <span>{{ $this->t('tracklist_of') }}</span>
                    <span class="text-neutral-700 dark:text-neutral-300 font-semibold" x-text="currentSounds.length"></span>
                    {{-- <span>{{ $this->t('tracklist_tracks') }}</span> --}}
                </div>
            </div>

            <!-- Scrollable Tracklist -->
            <div
                x-show="isTracklistVisible"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="flex-1 overflow-y-auto"
            >
                <template x-if="currentSounds.length === 0">
                    <div class="flex flex-col items-center justify-center p-8 text-center text-neutral-500 dark:text-neutral-400 space-y-2">
                        <flux:icon name="arrow-path" class="size-5 text-[#ff5500] animate-spin" />
                        <span class="text-xs">{{ $this->t('tracklist_loading') }}</span>
                    </div>
                </template>

                <ul x-show="currentSounds.length > 0" class="divide-y divide-neutral-100 dark:divide-neutral-800/60">
                    <template x-for="(track, index) in currentSounds" :key="track.id || index">
                        <li
                            @click="selectTrack(index, true)"
                            class="flex items-center justify-between px-4 sm:px-5 py-2.5 cursor-pointer transition-colors group"
                            :class="index === currentTrackIndex ? 'bg-orange-50/80 dark:bg-[#242428] sc-track-active' : 'hover:bg-neutral-50 dark:hover:bg-neutral-800/40'"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <span
                                    class="w-5 text-center text-xs font-mono shrink-0"
                                    :class="index === currentTrackIndex ? 'text-[#ff5500] font-bold' : 'text-neutral-400 dark:text-neutral-500'"
                                    x-text="index + 1"
                                ></span>

                                <div class="w-9 h-9 rounded overflow-hidden shrink-0 bg-neutral-200 dark:bg-neutral-800 relative">
                                    <img :src="track.artwork" :alt="track.title" class="w-full h-full object-cover">
                                    <template x-if="index === currentTrackIndex && isPlaying">
                                        <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                            <flux:icon name="speaker-wave" class="size-4 text-[#ff5500] animate-pulse" />
                                        </div>
                                    </template>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="mb-1 text-[10px] text-neutral-500 dark:text-neutral-400 font-medium truncate" x-text="track.artist || track.uploader"></p>
                                    <p
                                        class="text-xs font-semibold truncate"
                                        :class="index === currentTrackIndex ? 'text-[#ff5500]' : 'text-neutral-800 group-hover:text-neutral-900 dark:text-neutral-200 dark:group-hover:text-white'"
                                        x-text="track.title"
                                    ></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0 ml-2">
                                <span class="text-[12px] text-neutral-400 dark:text-neutral-500 font-mono font-semibold" x-text="track.durationFormatted || ''"></span>
                                {{-- <template x-if="track.plays && track.plays !== '0'">
                                    <span class="text-[10px] text-neutral-600 dark:text-neutral-400 font-mono bg-neutral-100 dark:bg-neutral-800 px-1.5 py-0.5 rounded" x-text="track.plays"></span>
                                </template> --}}
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>

        {{-- Persistent SoundCloud Player Iframe (Runs in background) --}}
        @persist('soundcloud-audio-player')
            <div id="sc-widget-persistent-container" class="sr-only pointer-events-none absolute w-px h-px overflow-hidden opacity-0">
                <iframe
                    id="sc-widget"
                    src="{{ $playerUrl }}"
                    class="w-1 h-1 border-0"
                    allow="autoplay; encrypted-media"
                ></iframe>
            </div>
        @endpersist
    </main>

    @include('minios::apps.soundcloud.change')
    @include('minios::apps.soundcloud.about')
</div>
