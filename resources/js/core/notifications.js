export function createNotificationManager() {
    return {
        notifications: [],
        activeToasts: [],
        toastGroupHovered: false,
        toastTicker: null,
        notificationCenterOpen: false,
        audioContext: null,
        audioUnlocked: false,
        audioDropdownOpen: false,
        globalVolume: 0.8,
        globalMuted: false,
        volumeHudVisible: false,
        volumeHudTimer: null,
        recentNotifIds: null,

        get unreadNotificationsCount() {
            return this.notifications.filter(n => !n.read).length;
        },

        get isAudioActive() {
            const soundSetting = this.settings?.notifications?.sound !== false;
            return soundSetting && this.audioUnlocked;
        },

        initNotificationListener() {
            this.recentNotifIds = new Set();

            const handleEvent = (data, defaultVariant = 'info') => {
                if (!data) return;
                const payload = Array.isArray(data) ? data[0] : data;
                if (!payload) return;

                const item = (typeof payload === 'string')
                    ? { message: payload, variant: defaultVariant }
                    : { ...payload };

                if (!item.variant && defaultVariant !== 'info') {
                    item.variant = defaultVariant;
                }

                // Deduplicate rapidly repeated notifications
                const dedupKey = item.id || (item.message ? ('msg_' + item.message + '_' + (item.variant || '')) : null);
                if (dedupKey) {
                    if (this.recentNotifIds.has(dedupKey)) {
                        return;
                    }
                    this.recentNotifIds.add(dedupKey);
                    setTimeout(() => {
                        this.recentNotifIds.delete(dedupKey);
                    }, 800);
                }

                this.handleOsNotify(item);
            };

            // 1. Primary os-notify on window / Alpine $dispatch
            window.addEventListener('os-notify', (event) => handleEvent(event.detail));

            // 2. Convenience aliases for window / Alpine $dispatch
            window.addEventListener('toast', (e) => handleEvent(e.detail));
            window.addEventListener('toast-success', (e) => handleEvent(e.detail, 'success'));
            window.addEventListener('toast-error', (e) => handleEvent(e.detail, 'danger'));
            window.addEventListener('toast-warning', (e) => handleEvent(e.detail, 'warning'));
            window.addEventListener('toast-info', (e) => handleEvent(e.detail, 'info'));

            // 3. Livewire listeners
            if (window.Livewire) {
                Livewire.on('os-notify', (data) => handleEvent(data));
                Livewire.on('toast', (data) => handleEvent(data));
                Livewire.on('toast-success', (data) => handleEvent(data, 'success'));
                Livewire.on('toast-error', (data) => handleEvent(data, 'danger'));
                Livewire.on('toast-warning', (data) => handleEvent(data, 'warning'));
                Livewire.on('toast-info', (data) => handleEvent(data, 'info'));
            }
        },

        handleOsNotify(detail) {
            const data = Array.isArray(detail) ? detail[0] : detail;
            if (!data) return;

            const appKey = (data.app_id || data.appId || data.app || '').toLowerCase();
            const matchedApp = this.applications[appKey] || Object.values(this.applications).find(a => a.name?.toLowerCase() === appKey);
            const iconName = data.icon || matchedApp?.icon || (this.applications[appKey] ? this.applications[appKey].icon : null);

            const notification = {
                id: data.id || ('notif_' + Date.now() + '_' + Math.random().toString(36).substr(2, 5)),
                title: data.title || '',
                text: data.text || data.message || '',
                variant: data.variant || data.type || 'info',
                app: data.app || matchedApp?.name || 'MiniOS',
                appId: matchedApp?.id || appKey || 'minios',
                icon: iconName,
                icon_url: data.icon_url || null,
                timestamp: data.timestamp || new Date().toISOString(),
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                read: false,
            };

            this.notifications.unshift(notification);

            if (this.notifications.length > 50) {
                this.notifications = this.notifications.slice(0, 50);
            }

            // Pop standalone MiniOS native toast banner instantly
            if (data.show_toast !== false) {
                this.showToast(notification);
            }

            this.playNotificationChime();
        },

        showToast(notification) {
            const duration = notification.duration || 5000;
            const toast = {
                ...notification,
                duration: duration,
                remaining: duration,
                progress: 100,
                paused: false,
                visible: true,
                leaving: false,
                entering: true,
            };

            // Limit active non-leaving toasts visible at once to 3
            const visibleToasts = this.activeToasts.filter(t => !t.leaving);
            if (visibleToasts.length >= 3) {
                const oldest = visibleToasts[0];
                if (oldest) {
                    this.dismissToast(oldest.id);
                }
            }

            this.activeToasts.push(toast);

            // Clear entrance animation lock after 200ms
            setTimeout(() => {
                toast.entering = false;
            }, 200);

            this.ensureToastTicker();
        },

        ensureToastTicker() {
            if (this.toastTicker) return;

            this.toastTicker = setInterval(() => {
                if (this.activeToasts.length === 0) {
                    clearInterval(this.toastTicker);
                    this.toastTicker = null;
                    return;
                }

                const delta = 50;
                this.activeToasts.forEach(toast => {
                    if (toast.leaving) return;
                    if (toast.paused || this.toastGroupHovered) return;

                    toast.remaining -= delta;
                    if (toast.duration > 0) {
                        toast.progress = Math.max(0, (toast.remaining / toast.duration) * 100);
                    }

                    if (toast.remaining <= 0) {
                        this.dismissToast(toast.id);
                    }
                });
            }, 50);
        },

        dismissToast(id) {
            const toast = this.activeToasts.find(t => t.id === id);
            if (!toast || toast.leaving) return;

            toast.leaving = true;
            toast.visible = false;
            toast.entering = false;

            // Wait for exit transition (180ms) before splicing
            setTimeout(() => {
                const index = this.activeToasts.findIndex(t => t.id === id);
                if (index !== -1) {
                    this.activeToasts.splice(index, 1);
                }
            }, 180);
        },

        pauseToast(id) {
            const toast = this.activeToasts.find(t => t.id === id);
            if (toast) {
                toast.paused = true;
            }
        },

        resumeToast(id) {
            const toast = this.activeToasts.find(t => t.id === id);
            if (toast) {
                toast.paused = false;
                if (toast.remaining < 2500) {
                    toast.remaining = 2500;
                    toast.duration = Math.max(toast.duration, 2500);
                }
            }
        },

        pauseAllToasts() {
            this.toastGroupHovered = true;
            this.activeToasts.forEach(t => {
                t.paused = true;
            });
        },

        resumeAllToasts() {
            this.toastGroupHovered = false;
            this.activeToasts.forEach(t => {
                t.paused = false;
                if (t.remaining < 2500) {
                    t.remaining = 2500;
                    t.duration = Math.max(t.duration, 2500);
                }
            });
        },

        getToastStyle(toast) {
            const pos = (this.settings?.notifications?.position || 'bottom end');
            const isStart = pos.includes('start');
            const isTop = pos.includes('top');

            const visibleToasts = this.activeToasts.filter(t => !t.leaving);
            const itemIdx = visibleToasts.indexOf(toast);
            const revIndex = itemIdx !== -1 ? Math.max(0, visibleToasts.length - 1 - itemIdx) : 0;

            const offscreenX = isStart ? '-125%' : '125%';
            const tx = toast.leaving ? offscreenX : '0px';

            if (this.toastGroupHovered) {
                return {
                    transform: `translate3d(${tx}, 0, 0) scale(1)`,
                    opacity: toast.leaving ? 0 : 1,
                    zIndex: 50 - revIndex,
                    position: 'relative',
                    marginBottom: '0.625rem',
                };
            }

            // Collapsed 3D stack
            const yOffset = isTop ? (revIndex * 12) : (-revIndex * 12);
            const scale = Math.max(0.85, 1 - (revIndex * 0.05));
            const opacity = toast.leaving ? 0 : Math.max(0.65, 1 - (revIndex * 0.16));

            return {
                transform: `translate3d(${tx}, ${yOffset}px, 0) scale(${scale})`,
                opacity: opacity,
                zIndex: 50 - revIndex,
                position: revIndex === 0 ? 'relative' : 'absolute',
                bottom: isTop ? 'auto' : '0',
                top: isTop ? '0' : 'auto',
                right: isStart ? 'auto' : '0',
                left: isStart ? '0' : 'auto',
                marginBottom: '0px',
            };
        },

        getAudioContext() {
            if (!this.audioContext) {
                const AudioCtx = window.AudioContext || window.webkitAudioContext;
                if (AudioCtx) {
                    this.audioContext = new AudioCtx();
                    this.audioContext.onstatechange = () => {
                        this.audioUnlocked = this.audioContext.state === 'running';
                    };
                }
            }
            return this.audioContext;
        },

        checkAudioStatus() {
            const ctx = this.getAudioContext();
            if (ctx) {
                this.audioUnlocked = ctx.state === 'running';
            }
        },

        async toggleAudio() {
            const ctx = this.getAudioContext();
            if (!ctx) return;

            if (!this.settings) this.settings = {};
            if (!this.settings.notifications) this.settings.notifications = {};

            if (this.isAudioActive) {
                this.settings.notifications.sound = false;
                this.audioUnlocked = false;
                try {
                    await ctx.suspend();
                } catch (e) {}
            } else {
                this.settings.notifications.sound = true;
                try {
                    if (ctx.state === 'suspended') {
                        await ctx.resume();
                    }
                    this.audioUnlocked = ctx.state === 'running';
                } catch (e) {
                    this.audioUnlocked = false;
                }

                if (this.audioUnlocked) {
                    this.playNotificationChime();
                }
            }

            if (window.Livewire) {
                Livewire.dispatch('os-setting-updated', {
                    category: 'notifications',
                    key: 'sound',
                    value: this.settings.notifications.sound,
                });
            }
        },

        initAudioSystem() {
            this.checkAudioStatus();

            try {
                const savedVol = localStorage.getItem('minios_global_volume');
                if (savedVol !== null) {
                    const parsed = parseFloat(savedVol);
                    if (!isNaN(parsed)) {
                        this.globalVolume = Math.max(0, Math.min(1, parsed));
                    }
                }
                const savedMuted = localStorage.getItem('minios_global_muted');
                if (savedMuted !== null) {
                    this.globalMuted = savedMuted === 'true';
                }
            } catch (e) {}

            window.addEventListener('minios-set-volume', (e) => {
                if (!e.detail) return;
                if (typeof e.detail.volume === 'number' || typeof e.detail.volume === 'string') {
                    const vol = parseFloat(e.detail.volume);
                    if (!isNaN(vol)) {
                        this.globalVolume = Math.max(0, Math.min(1, vol));
                    }
                }
                if (typeof e.detail.muted === 'boolean') {
                    this.globalMuted = e.detail.muted;
                } else if (this.globalVolume > 0 && this.globalMuted) {
                    this.globalMuted = false;
                }
                this.saveGlobalVolume();
                this.broadcastGlobalVolume(e.detail.source || null);
            });

            window.addEventListener('minios-request-volume', () => {
                this.broadcastGlobalVolume();
            });

            const unlockHandler = () => {
                const ctx = this.getAudioContext();
                if (ctx && ctx.state === 'suspended' && this.settings?.notifications?.sound !== false) {
                    ctx.resume().then(() => {
                        this.audioUnlocked = ctx.state === 'running';
                    }).catch(() => {});
                }
            };

            window.addEventListener('pointerdown', unlockHandler, { once: true });
            window.addEventListener('keydown', unlockHandler, { once: true });

            setTimeout(() => {
                this.broadcastGlobalVolume();
            }, 100);
        },

        toggleAudioDropdown() {
            const willOpen = !this.audioDropdownOpen;
            if (typeof this.closeAll === 'function') {
                this.closeAll();
            }
            this.audioDropdownOpen = willOpen;
        },

        saveGlobalVolume() {
            try {
                localStorage.setItem('minios_global_volume', this.globalVolume.toString());
                localStorage.setItem('minios_global_muted', this.globalMuted ? 'true' : 'false');
            } catch (e) {}
        },

        setGlobalVolume(val, source = 'system') {
            const vol = parseFloat(val);
            if (isNaN(vol)) return;
            this.globalVolume = Math.max(0, Math.min(1, Math.round(vol * 100) / 100));
            if (this.globalVolume > 0 && this.globalMuted) {
                this.globalMuted = false;
            } else if (this.globalVolume === 0) {
                this.globalMuted = true;
            }
            this.saveGlobalVolume();
            this.broadcastGlobalVolume(source);
        },

        toggleGlobalMute(source = 'system') {
            this.globalMuted = !this.globalMuted;
            this.saveGlobalVolume();
            this.broadcastGlobalVolume(source);
        },

        increaseGlobalVolume(step = 0.05) {
            let next = Math.min(1, Math.round((this.globalVolume + step) * 100) / 100);
            this.globalMuted = false;
            this.setGlobalVolume(next);
            this.showVolumeHud();
        },

        decreaseGlobalVolume(step = 0.05) {
            let next = Math.max(0, Math.round((this.globalVolume - step) * 100) / 100);
            if (next === 0) {
                this.globalMuted = true;
            }
            this.setGlobalVolume(next);
            this.showVolumeHud();
        },

        showVolumeHud() {
            this.volumeHudVisible = true;
            if (this.volumeHudTimer) {
                clearTimeout(this.volumeHudTimer);
            }
            this.volumeHudTimer = setTimeout(() => {
                this.volumeHudVisible = false;
            }, 1500);
        },

        broadcastGlobalVolume(source = null) {
            const effectiveVolume = this.globalMuted ? 0 : this.globalVolume;

            document.querySelectorAll('audio, video').forEach(media => {
                try {
                    media.volume = effectiveVolume;
                } catch (e) {}
            });

            window.dispatchEvent(new CustomEvent('minios-volume-changed', {
                detail: {
                    volume: this.globalVolume,
                    muted: this.globalMuted,
                    effectiveVolume: effectiveVolume,
                    source: source,
                }
            }));
        },

        playNotificationChime() {
            try {
                const isSoundEnabled = this.settings?.notifications?.sound !== false;
                if (!isSoundEnabled) return;

                const volScale = (this.globalMuted ? 0 : this.globalVolume);
                if (volScale <= 0) return;

                const ctx = this.getAudioContext();
                if (!ctx) return;

                if (ctx.state === 'suspended') {
                    ctx.resume().then(() => {
                        this.audioUnlocked = ctx.state === 'running';
                    }).catch(() => {});
                } else if (ctx.state === 'running') {
                    this.audioUnlocked = true;
                }

                const now = ctx.currentTime;

                const playTone = (freq, startTime, duration) => {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();

                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(freq, startTime);

                    gain.gain.setValueAtTime(0, startTime);
                    gain.gain.linearRampToValueAtTime(0.12 * volScale, startTime + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(startTime);
                    osc.stop(startTime + duration);
                };

                // Gentle dual chime: D5 (587.33Hz) -> A5 (880Hz)
                playTone(587.33, now, 0.18);
                playTone(880.00, now + 0.09, 0.28);
            } catch (e) {
                // Ignore audio context errors gracefully
            }
        },

        toggleNotificationCenter() {
            const willOpen = !this.notificationCenterOpen;
            if (typeof this.closeAll === 'function') {
                this.closeAll();
            }
            this.notificationCenterOpen = willOpen;
            if (willOpen) {
                this.notifications.forEach(n => { n.read = true; });
            }
        },

        removeNotification(id) {
            this.notifications = this.notifications.filter(n => n.id !== id);
        },

        clearAllNotifications() {
            this.notifications = [];
        },
    };
}
