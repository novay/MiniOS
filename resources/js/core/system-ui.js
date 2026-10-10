export function createSystemUI() {
    return {
        clock: '',
        clockTimer: null,
        activitiesOpen: false,
        applicationsOpen: false,
        systemMenuOpen: false,
        aboutOpen: false,
        selectedShortcut: null,
        isFullscreen: false,

        get isDarkMode() {
            const theme = this.settings?.appearance?.theme || 'system';
            return theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        },

        initClock() {
            this.updateClock();

            this.clockTimer = setInterval(() => {
                this.updateClock();
            }, 1000);
        },

        updateClock() {
            const now = new Date();
            const is12h = this.settings?.locale_time?.time_format === '12h';
            const locale = (this.settings?.locale_time?.locale === 'id') ? 'id-ID' : 'en-US';
            const timeZone = this.settings?.locale_time?.timezone || undefined;

            let weekday = '';
            let time = '';

            try {
                weekday = new Intl.DateTimeFormat(locale, {
                    weekday: 'short',
                    timeZone,
                }).format(now);

                time = new Intl.DateTimeFormat(locale, {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: is12h,
                    timeZone,
                }).format(now);
            } catch (e) {
                weekday = new Intl.DateTimeFormat('en-US', { weekday: 'short' }).format(now);
                time = new Intl.DateTimeFormat('en-US', { hour: '2-digit', minute: '2-digit', hour12: is12h }).format(now);
            }

            this.clock = `${weekday} ${time}`;
        },

        initSettingsListener() {
            this.applyTheme();

            if (window.Livewire) {
                Livewire.on('os-setting-updated', (data) => {
                    const payload = Array.isArray(data) ? data[0] : data;
                    if (payload && payload.category && payload.key) {
                        if (!this.settings[payload.category]) {
                            this.settings[payload.category] = {};
                        }
                        this.settings[payload.category][payload.key] = payload.value;
                        this.applyTheme();
                        this.updateClock();
                    }
                });

                Livewire.on('os-setting-reset', (data) => {
                    const payload = Array.isArray(data) ? data[0] : data;
                    if (payload && payload.category) {
                        this.applyTheme();
                        this.updateClock();
                    }
                });
            }
        },

        toggleTheme() {
            this.closeAll();

            if (!this.settings) this.settings = {};
            if (!this.settings.appearance) this.settings.appearance = {};

            const isDark = this.isDarkMode;
            const newTheme = isDark ? 'light' : 'dark';

            this.settings.appearance.theme = newTheme;
            this.applyTheme();

            if (window.Livewire) {
                Livewire.dispatch('toggle-dark-mode', { theme: newTheme });
            }
        },

        applyTheme() {
            const theme = this.settings?.appearance?.theme || 'system';
            const isDark = theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

            if (isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            const accentColor = this.settings?.appearance?.accent_color || 'indigo';
            document.documentElement.setAttribute('data-accent', accentColor);

            const colors = {
                zinc: '#27272a',
                indigo: '#6366f1',
                emerald: '#10b981',
                sky: '#0ea5e9',
                amber: '#f59e0b',
                rose: '#f43f5e',
                violet: '#8b5cf6',
            };

            const hex = colors[accentColor] || colors.indigo;
            document.documentElement.style.setProperty('--accent-color', hex);

            const panelBlur = this.settings?.appearance?.panel_blur ?? true;
            if (!panelBlur) {
                document.documentElement.classList.add('no-blur');
            } else {
                document.documentElement.classList.remove('no-blur');
            }

            const fontFamily = this.settings?.appearance?.font_family || 'inter';
            document.documentElement.setAttribute('data-font', fontFamily);

            const fontStacks = {
                'ubuntu': "'Ubuntu', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
                'segoe': "'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif",
                'system-ui': "system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
                'inter': "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
                'google': "'Google Sans', 'Product Sans', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif",
                'san-francisco': "-apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', sans-serif",
            };

            const activeFont = fontStacks[fontFamily] || fontStacks['inter'];
            document.documentElement.style.setProperty('--font-sans', activeFont);
            if (document.body) {
                document.body.style.fontFamily = activeFont;
            }

            this.applyWallpaper();
        },

        applyWallpaper() {
            const wallpaper = this.settings?.appearance?.wallpaper || 'wall-1';
            const el = document.querySelector('.desktop-wallpaper');
            if (el) {
                const filename = wallpaper.endsWith('.webp') ? wallpaper : `${wallpaper}.webp`;
                el.style.backgroundImage = `url('/minios/wallpapers/${filename}')`;
                el.style.backgroundSize = 'cover';
                el.style.backgroundPosition = 'center';
                el.style.backgroundRepeat = 'no-repeat';
            }
        },

        toggleAboutModal() {
            const willOpen = !this.aboutOpen;
            this.closeAll();
            this.aboutOpen = willOpen;
        },

        toggleApplications() {
            const willOpen = !this.applicationsOpen;
            this.closeAll();
            this.applicationsOpen = willOpen;
        },

        toggleSystemMenu() {
            const willOpen = !this.systemMenuOpen;
            this.closeAll();
            this.systemMenuOpen = willOpen;
        },

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen().then(() => {
                        if (navigator.keyboard?.lock) {
                            navigator.keyboard.lock(['KeyW']).catch(() => {});
                        }
                    }).catch(() => {});
                }
                this.isFullscreen = true;
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().then(() => {
                        if (navigator.keyboard?.unlock) {
                            navigator.keyboard.unlock();
                        }
                    }).catch(() => {});
                    this.isFullscreen = false;
                }
            }
        },

        closeAll() {
            this.activitiesOpen = false;
            this.applicationsOpen = false;
            this.systemMenuOpen = false;
            this.notificationCenterOpen = false;
            this.audioDropdownOpen = false;
            this.aboutOpen = false;
            this.selectedShortcut = null;

            if (typeof this.closeContextMenu === 'function') {
                this.closeContextMenu();
            }
        },
    };
}
