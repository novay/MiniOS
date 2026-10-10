export default function minios(applications = {}, userSettings = {}) {
    return {

        /*
        |--------------------------------------------------------------------------
        | Applications & Settings
        |--------------------------------------------------------------------------
        */

        applications,
        settings: userSettings,

        activeApplication: null,

        currentPath: '/',
        currentUrl: '/',


        /*
        |--------------------------------------------------------------------------
        | Window Manager
        |--------------------------------------------------------------------------
        */

        windows: {},

        activeWindow: null,

        zIndexCounter: 100,

        /*
        |--------------------------------------------------------------------------
        | Persistence
        |--------------------------------------------------------------------------
        */

        windowSessionKey:
            'web-desktop:window-session:v2',

        persistTimer: null,
        initialized: false,
        pagehideHandler: null,
        visibilityChangeHandler: null,


        /*
        |--------------------------------------------------------------------------
        | Pointer State
        |--------------------------------------------------------------------------
        */

        dragState: null,
        resizeState: null,
        dockDrag: null,
        dockDrop: null,
        dockDragSuppressedId: null,

        pointerMoveHandler: null,
        pointerUpHandler: null,
        viewportResizeHandler: null,
        popstateHandler: null,
        keydownHandler: null,


        /*
        |--------------------------------------------------------------------------
        | Clock
        |--------------------------------------------------------------------------
        */

        clock: '',
        clockTimer: null,


        /*
        |--------------------------------------------------------------------------
        | Desktop UI
        |--------------------------------------------------------------------------
        */

        activitiesOpen: false,
        applicationsOpen: false,
        systemMenuOpen: false,
        notificationCenterOpen: false,
        notifications: [],
        activeToasts: [],
        toastGroupHovered: false,
        toastTicker: null,
        audioContext: null,
        audioUnlocked: false,
        selectedShortcut: null,
        isFullscreen: false,

        get unreadNotificationsCount() {
            return this.notifications.filter(n => !n.read).length;
        },

        get isAudioActive() {
            const soundSetting = this.settings?.notifications?.sound !== false;
            return soundSetting && this.audioUnlocked;
        },

        contextMenu: {
            open: false,
            type: 'desktop',
            appId: null,
            x: 0,
            y: 0,
        },


        /*
        |--------------------------------------------------------------------------
        | Lifecycle
        |--------------------------------------------------------------------------
        */

        init() {
            if (this.initialized) return;
            this.initialized = true;
            /*
            |--------------------------------------------------------------------------
            | 1. Bootstrap window registry FIRST
            |--------------------------------------------------------------------------
            |
            | Semua window harus sudah mempunyai reactive state
            | sebelum x-show / x-bind di DOM dievaluasi.
            |
            */

            this.bootstrapWindows();


            /*
            |--------------------------------------------------------------------------
            | 2. Restore previous browser session
            |--------------------------------------------------------------------------
            */

            this.restoreWindowSession();

            try {
                const cachedPinned = localStorage.getItem('minios:dock:pinned_apps');
                if (cachedPinned) {
                    const parsed = JSON.parse(cachedPinned);
                    if (Array.isArray(parsed) && parsed.length > 0) {
                        if (!this.settings) this.settings = {};
                        if (!this.settings.dock) this.settings.dock = {};
                        if (!this.settings.dock.pinned_apps || !Array.isArray(this.settings.dock.pinned_apps)) {
                            this.settings.dock.pinned_apps = parsed;
                        }
                    }
                }
            } catch (e) {}


            /*
            |--------------------------------------------------------------------------
            | 3. Start desktop services
            |--------------------------------------------------------------------------
            */

            this.initClock();
            this.initSettingsListener();
            this.initNotificationListener();
            this.initAudioSystem();

            this.initPointerEvents();
            this.initKeyboardShortcuts();


            /*
            |--------------------------------------------------------------------------
            | 4. URL wins as active window
            |--------------------------------------------------------------------------
            */

            this.initRouter();

            this.pagehideHandler = () => this.flushWindowSession();
            this.visibilityChangeHandler = () => {
                if (document.visibilityState === 'hidden') {
                    this.flushWindowSession();
                }
            };
            window.addEventListener('pagehide', this.pagehideHandler);
            document.addEventListener('visibilitychange', this.visibilityChangeHandler);

            this.globalContextMenuHandler = (event) => {
                event.preventDefault();
            };
            document.addEventListener('contextmenu', this.globalContextMenuHandler);

            // Child x-ref bindings and workspace classes are ready after Alpine renders.
            this.$nextTick(() => {
                if (this.initialized) this.handleWorkspaceResize();
            });
        },


        destroy() {
            if (!this.initialized) return;
            this.flushWindowSession();
            this.initialized = false;
            window.removeEventListener('pagehide', this.pagehideHandler);
            document.removeEventListener('visibilitychange', this.visibilityChangeHandler);
            if (this.globalContextMenuHandler) {
                document.removeEventListener('contextmenu', this.globalContextMenuHandler);
                this.globalContextMenuHandler = null;
            }
            if (this.keydownHandler) {
                window.removeEventListener('keydown', this.keydownHandler, true);
            }
            if (this.clockTimer) {
                clearInterval(this.clockTimer);
            }


            if (this.popstateHandler) {
                window.removeEventListener(
                    'popstate',
                    this.popstateHandler
                );
            }


            if (this.pointerMoveHandler) {
                window.removeEventListener(
                    'pointermove',
                    this.pointerMoveHandler
                );
            }


            if (this.pointerUpHandler) {
                window.removeEventListener(
                    'pointerup',
                    this.pointerUpHandler
                );
            }


            if (this.viewportResizeHandler) {
                window.removeEventListener(
                    'resize',
                    this.viewportResizeHandler
                );
            }

            if (this.persistTimer) {
                clearTimeout(
                    this.persistTimer
                );
            }
            this.clockTimer = null;
            this.persistTimer = null;
        },


        /*
        |--------------------------------------------------------------------------
        | Helpers
        |--------------------------------------------------------------------------
        */

        clamp(value, min, max) {
            if (max < min) {
                return max;
            }

            return Math.min(
                Math.max(value, min),
                max
            );
        },


        getWorkspaceRect() {
            if (this.$refs?.workspace) {
                const rect =
                    this.$refs.workspace.getBoundingClientRect();

                if (rect.width > 0 && rect.height > 0) {
                    return { width: rect.width, height: rect.height };
                }
            }


            return {
                width: Math.max(
                    1,
                    window.innerWidth - 64
                ),

                height: Math.max(
                    1,
                    window.innerHeight - 28
                ),
            };
        },


        /*
        |--------------------------------------------------------------------------
        | Clock
        |--------------------------------------------------------------------------
        */

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

        get isDarkMode() {
            const theme = this.settings?.appearance?.theme || 'system';
            return theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
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
                Livewire.dispatch('toggle-dark-mode');
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


        /*
        |--------------------------------------------------------------------------
        | Router
        |--------------------------------------------------------------------------
        */

        initRouter() {
            this.syncRoute();


            this.popstateHandler = () => {
                this.syncRoute();
            };


            window.addEventListener(
                'popstate',
                this.popstateHandler
            );
        },


        normalizePath(path) {
            if (!path) {
                return '/';
            }


            let normalized = path;


            if (!normalized.startsWith('/')) {
                normalized = `/${normalized}`;
            }


            if (
                normalized.length > 1 &&
                normalized.endsWith('/')
            ) {
                normalized =
                    normalized.slice(0, -1);
            }


            return normalized;
        },


        resolveApplication(path) {
            const normalizedPath =
                this.normalizePath(path);


            const matches = [];


            Object.entries(this.applications)
                .forEach(([id, application]) => {

                    const routes =
                        application.routes ?? [];


                    routes.forEach((route) => {

                        const normalizedRoute =
                            this.normalizePath(route);


                        const exactMatch =
                            normalizedPath === normalizedRoute;


                        const childMatch =
                            normalizedPath.startsWith(
                                `${normalizedRoute}/`
                            );


                        if (
                            exactMatch ||
                            childMatch
                        ) {
                            matches.push({
                                id,
                                application,
                                route: normalizedRoute,
                            });
                        }

                    });

                });


            /*
             * Longest route wins.
             *
             * Example:
             *
             * /sales
             * /sales/admin
             */
            matches.sort((a, b) => {
                return (
                    b.route.length -
                    a.route.length
                );
            });


            return matches[0] ?? null;
        },


        syncRoute() {
            const pathname =
                this.normalizePath(
                    window.location.pathname
                );


            this.currentPath =
                pathname;


            this.currentUrl =
                pathname +
                window.location.search +
                window.location.hash;


            const resolved =
                this.resolveApplication(
                    pathname
                );


            /*
             * Desktop root / unknown route.
             */
            if (!resolved) {
                this.activeApplication = null;
                this.activeWindow = null;

                return;
            }


            let subPath =
                pathname.slice(
                    resolved.route.length
                );


            if (!subPath) {
                subPath = '/';
            }


            this.activeApplication = {
                id: resolved.id,

                name:
                    resolved.application.name,

                icon:
                    resolved.application.icon,

                entry:
                    resolved.application.entry,

                baseRoute:
                    resolved.route,

                path:
                    pathname,

                subPath,

                config:
                    resolved.application,
            };


            /*
            |--------------------------------------------------------------------------
            | Open Window From URL
            |--------------------------------------------------------------------------
            */

            this.openWindow(
                resolved.id,
                {
                    url:
                        this.currentUrl,

                    focus:
                        false,
                }
            );


            /*
            |--------------------------------------------------------------------------
            | URL decides active window
            |--------------------------------------------------------------------------
            */

            const windowState =
                this.getWindow(
                    resolved.id
                );


            if (windowState) {

                windowState.open =
                    true;

                windowState.minimized =
                    false;

                windowState.zIndex =
                    this.nextWindowZIndex();


                this.activeWindow =
                    resolved.id;
            }
        },


        navigate(url, options = {}) {
            const {
                replace = false,
            } = options;


            const target =
                new URL(
                    url,
                    window.location.origin
                );


            const pathname =
                this.normalizePath(
                    target.pathname
                );


            const browserUrl =
                pathname +
                target.search +
                target.hash;


            if (
                browserUrl ===
                this.currentUrl
            ) {
                this.syncRoute();
                this.closeAll();

                return;
            }


            if (replace) {
                window.history.replaceState(
                    {},
                    '',
                    browserUrl
                );
            } else {
                window.history.pushState(
                    {},
                    '',
                    browserUrl
                );
            }


            this.syncRoute();

            this.closeAll();
        },

        /*
        |--------------------------------------------------------------------------
        | Bootstrap Windows
        |--------------------------------------------------------------------------
        */

        bootstrapWindows() {
            const workspace =
                this.getWorkspaceRect();


            const windows = {};

            let index = 0;


            Object.entries(this.applications)
                .forEach(([id, application]) => {

                    const settings =
                        application.window ?? {};


                    const desiredWidth =
                        settings.width ?? 900;

                    const desiredHeight =
                        settings.height ?? 600;


                    const width =
                        Math.min(
                            desiredWidth,
                            Math.max(
                                300,
                                workspace.width - 40
                            )
                        );


                    const height =
                        Math.min(
                            desiredHeight,
                            Math.max(
                                220,
                                workspace.height - 40
                            )
                        );


                    const cascade =
                        (index % 8) * 28;


                    const centeredX =
                        Math.max(
                            0,
                            (workspace.width - width) / 2
                        );


                    const centeredY =
                        Math.max(
                            0,
                            (workspace.height - height) / 2
                        );


                    const isCentered = settings.center === true || id === 'about';

                    const x = isCentered
                        ? centeredX
                        : this.clamp(
                            centeredX + cascade,
                            0,
                            Math.max(
                                0,
                                workspace.width - width
                            )
                        );


                    const y = isCentered
                        ? centeredY
                        : this.clamp(
                            centeredY + cascade,
                            0,
                            Math.max(
                                0,
                                workspace.height - height
                            )
                        );


                    windows[id] = {
                        id,

                        title:
                            application.name,

                        icon:
                            application.icon,

                        /*
                        * Semua window registered,
                        * tetapi belum running.
                        */
                        open: false,

                        minimized: false,
                        maximized: false,

                        x,
                        y,

                        width,
                        height,

                        minWidth:
                            settings.min_width ?? 420,

                        minHeight:
                            settings.min_height ?? 280,

                        resizable:
                            settings.resizable !== false,

                        maximizable:
                            settings.maximizable !== false,

                        zIndex:
                            ++this.zIndexCounter,

                        url:
                            application.entry ??
                            application.routes?.[0] ??
                            '/',

                        restore: null,
                    };


                    index++;
                });


            /*
            * Satu assignment.
            *
            * Alpine langsung mendapatkan complete
            * reactive object.
            */
            this.windows = windows;
        },


        /*
        |--------------------------------------------------------------------------
        | Window Creation
        |--------------------------------------------------------------------------
        */

        ensureWindow(id) {
            const windowState =
                this.windows[id];


            if (!windowState) {
                console.warn(
                    `Desktop window [${id}] is not registered.`
                );

                return null;
            }


            return windowState;
        },

        getWindow(id) {
            return Object.hasOwn(this.windows, id) ? this.windows[id] : null;
        },


        /*
        |--------------------------------------------------------------------------
        | Open
        |--------------------------------------------------------------------------
        */

        openWindow(
            id,
            {
                url = null,
                focus = true,
            } = {}
        ) {
            const windowState =
                this.ensureWindow(id);


            if (!windowState) {
                return;
            }


            if (id === 'about' || this.applications[id]?.window?.center) {
                this.centerWindow(id);
            }

            /*
            * Penting:
            *
            * Membuka aplikasi SELALU berarti
            * window running + visible.
            */
            windowState.open = true;
            windowState.minimized = false;


            if (url) {
                windowState.url = url;
            }


            if (focus) {

                windowState.zIndex =
                    this.nextWindowZIndex();


                this.activeWindow =
                    id;
            }


            this.scheduleWindowSessionSave();
        },


        centerWindow(id) {
            const windowState = this.getWindow(id);
            if (!windowState) return;

            const workspace = this.getWorkspaceRect();
            windowState.x = Math.max(0, (workspace.width - windowState.width) / 2);
            windowState.y = Math.max(0, (workspace.height - windowState.height) / 2);
        },

        /*
        |--------------------------------------------------------------------------
        | Open Application from Dock / Launcher
        |--------------------------------------------------------------------------
        */

        openApplication(id, options = {}) {
            const application =
                this.applications[id];


            if (!application) {
                console.warn(
                    `Desktop application [${id}] is not registered.`
                );

                return;
            }

            const fileDependentApps = ['preview', 'editor', 'textedit', 'player'];
            const isFileApp = fileDependentApps.includes(id);

            // Buka langsung dari Launchpad tanpa file
            if (isFileApp && !options?.path && (this.applicationsOpen || options?.fromLauncher)) {
                this.applicationsOpen = false;
                this.closeAll();

                const appName = application.name || (id === 'editor' ? 'Editor' : id === 'player' ? 'Player' : 'Preview');
                this.handleOsNotify({
                    title: '',
                    message: 'Aplikasi berjalan, gunakan via Files.',
                    text: 'Aplikasi berjalan, gunakan via Files.',
                    variant: 'info',
                    app: appName,
                });

                return;
            }

            if (id === 'about' || application.window?.center) {
                this.centerWindow(id);
            }


            const windowState =
                this.getWindow(id);


            /*
            |--------------------------------------------------------------------------
            | Application already running
            |--------------------------------------------------------------------------
            */

            if (
                windowState &&
                windowState.open
            ) {

                windowState.minimized =
                    false;


                this.focusWindow(
                    id,
                    {
                        syncUrl: true,
                    }
                );


                this.closeAll();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | First launch
            |--------------------------------------------------------------------------
            */

            const entry =
                application.entry ??
                application.routes?.[0];


            if (!entry) {
                console.warn(
                    `Desktop application [${id}] has no entry route.`
                );

                return;
            }


            /*
            * Langsung set window state juga.
            *
            * Jadi UI tidak bergantung pada
            * router side-effect.
            */
            this.openWindow(
                id,
                {
                    url: entry,
                    focus: false,
                }
            );


            this.navigate(entry);


            this.closeAll();
        },

        /*
        |--------------------------------------------------------------------------
        | Focus
        |--------------------------------------------------------------------------
        */

        focusWindow(
            id,
            {
                syncUrl = true,
            } = {}
        ) {
            const windowState =
                this.getWindow(id);


            if (
                !windowState ||
                !windowState.open
            ) {
                return;
            }


            /*
            * Focusing minimized window juga
            * sekaligus restore.
            */
            windowState.minimized = false;


            windowState.zIndex =
                this.nextWindowZIndex();


            this.activeWindow =
                id;


            /*
            * URL mengikuti focused window.
            */
            if (
                syncUrl &&
                windowState.url &&
                this.currentUrl !==
                    windowState.url
            ) {

                const target =
                    new URL(
                        windowState.url,
                        window.location.origin
                    );


                const browserUrl =
                    this.normalizePath(
                        target.pathname
                    ) +
                    target.search +
                    target.hash;


                /*
                * Focus change tidak perlu
                * menambah browser history.
                */
                window.history.replaceState(
                    {},
                    '',
                    browserUrl
                );


                this.currentPath =
                    this.normalizePath(
                        target.pathname
                    );


                this.currentUrl =
                    browserUrl;


                const resolved =
                    this.resolveApplication(
                        this.currentPath
                    );


                if (resolved) {

                    let subPath =
                        this.currentPath.slice(
                            resolved.route.length
                        );


                    if (!subPath) {
                        subPath = '/';
                    }


                    this.activeApplication = {
                        id:
                            resolved.id,

                        name:
                            resolved.application.name,

                        icon:
                            resolved.application.icon,

                        entry:
                            resolved.application.entry,

                        baseRoute:
                            resolved.route,

                        path:
                            this.currentPath,

                        subPath,

                        config:
                            resolved.application,
                    };
                }
            }


            this.scheduleWindowSessionSave();
        },

        /*
        |--------------------------------------------------------------------------
        | Find highest visible window
        |--------------------------------------------------------------------------
        */

        getTopVisibleWindow(excludeId = null) {
            const candidates =
                Object.values(this.windows)
                    .filter(window => {

                        return (
                            window.id !== excludeId &&
                            window.open &&
                            !window.minimized
                        );

                    });


            candidates.sort(
                (a, b) =>
                    b.zIndex - a.zIndex
            );


            return candidates[0] ?? null;
        },


        /*
        |--------------------------------------------------------------------------
        | Close
        |--------------------------------------------------------------------------
        */

        closeWindow(id) {
            const windowState =
                this.getWindow(id);


            if (!windowState) {
                return;
            }


            windowState.open = false;
            windowState.minimized = false;


            if (
                this.activeWindow === id
            ) {
                this.activeWindow = null;


                const next =
                    this.getTopVisibleWindow(id);


                if (next) {
                    this.focusWindow(
                        next.id,
                        {
                            syncUrl: true,
                        }
                    );
                } else {
                    this.navigate(
                        '/',
                        {
                            replace: true,
                        }
                    );
                }
            }

            this.scheduleWindowSessionSave();
        },


        /*
        |--------------------------------------------------------------------------
        | Global Keyboard Shortcuts (Cmd+W / Ctrl+W to close active window)
        |--------------------------------------------------------------------------
        */

        initKeyboardShortcuts() {
            this.keydownHandler = (event) => {
                // 1. Applications Drawer / Launcher Shortcuts:
                // Cmd+Space, Ctrl+Space, Cmd+K, Ctrl+K, or Win+S (Meta+S)
                const isSpace = event.code === 'Space' || event.key === ' ';
                const isK = event.key === 'k' || event.key === 'K';
                const isS = event.key === 's' || event.key === 'S';

                const isLauncherCmd = (event.metaKey || event.ctrlKey)
                    && !event.altKey
                    && !event.shiftKey
                    && (isSpace || isK);

                const isWinS = event.metaKey && !event.ctrlKey && !event.altKey && !event.shiftKey && isS;

                if (isLauncherCmd || isWinS) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.toggleApplications();
                    return;
                }

                // 2. Cmd+W / Ctrl+W to close active window
                const isCmdOrCtrlW = (event.metaKey || event.ctrlKey)
                    && !event.altKey
                    && !event.shiftKey
                    && (event.key === 'w' || event.key === 'W');

                if (!isCmdOrCtrlW) {
                    return;
                }

                const hasOpenWindows = Object.values(this.windows).some(w => w.open);
                const currentNormalizedPath = this.normalizePath(window.location.pathname);
                const isAppPath = currentNormalizedPath !== '/';

                // Intercept Cmd+W / Ctrl+W only when an app window is open, an app URL is accessed,
                // or fullscreen launcher/system menu is open.
                if (hasOpenWindows || isAppPath || this.applicationsOpen || this.systemMenuOpen) {
                    event.preventDefault();
                    event.stopPropagation();

                    if (this.applicationsOpen) {
                        this.applicationsOpen = false;
                        return;
                    }

                    if (this.systemMenuOpen) {
                        this.systemMenuOpen = false;
                        return;
                    }

                    this.closeActiveOrTopWindow();
                }
                // When on desktop with "/" and all windows closed:
                // We do NOT call preventDefault(), allowing browser to execute native Cmd+W / Ctrl+W to close tab.
            };

            window.addEventListener('keydown', this.keydownHandler, true);
        },

        closeActiveOrTopWindow() {
            // 1. If an active window is focused and open, close it
            if (this.activeWindow && this.windows[this.activeWindow]?.open) {
                this.closeWindow(this.activeWindow);
                return;
            }

            // 2. Otherwise close the highest visible window
            const topWindow = this.getTopVisibleWindow();
            if (topWindow) {
                this.activeWindow = topWindow.id;
                this.closeWindow(topWindow.id);
                return;
            }

            // 3. Otherwise close any open window (e.g. minimized)
            const anyOpen = Object.values(this.windows).find(w => w.open);
            if (anyOpen) {
                this.activeWindow = anyOpen.id;
                this.closeWindow(anyOpen.id);
                return;
            }

            // 4. If no open window exists in state, but URL is not '/', navigate back to '/'
            if (this.normalizePath(window.location.pathname) !== '/') {
                this.navigate('/', { replace: true });
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Minimize
        |--------------------------------------------------------------------------
        */

        async minimizeWindow(id) {
            const windowState =
                this.getWindow(id);


            if (
                !windowState ||
                windowState.minimized
            ) {
                return;
            }


            /*
            * Kalau fullscreen, munculkan dock
            * terlebih dahulu.
            *
            * Workspace masih fullscreen sehingga
            * window tidak bergeser selama animasi.
            */
            if (windowState.maximized) {
                this.dockRevealOverride = true;
            }


            const animation =
                await this.animateWindowToDock(id);


            /*
            * Baru benar-benar sembunyikan window
            * setelah animasi selesai.
            */
            windowState.minimized = true;


            if (
                this.activeWindow === id
            ) {
                this.activeWindow = null;


                const next =
                    this.getTopVisibleWindow(id);


                if (next) {

                    this.focusWindow(
                        next.id,
                        {
                            syncUrl: true,
                        }
                    );

                } else {

                    this.navigate(
                        '/',
                        {
                            replace: true,
                        }
                    );
                }
            }


            this.dockRevealOverride = false;


            this.scheduleWindowSessionSave();


            /*
            * Lepaskan animation fill setelah
            * x-show menyembunyikan window.
            */
            if (animation) {
                requestAnimationFrame(() => {
                    animation.cancel();
                });
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Maximize / Restore
        |--------------------------------------------------------------------------
        */

        toggleMaximizeWindow(id) {
            const windowState =
                this.getWindow(id);


            if (!windowState || windowState.maximizable === false) {
                return;
            }


            this.focusWindow(
                id,
                {
                    syncUrl: false,
                }
            );


            /*
            |--------------------------------------------------------------------------
            | Maximize
            |--------------------------------------------------------------------------
            */

            if (!windowState.maximized) {

                windowState.restore = {
                    x:
                        windowState.x,

                    y:
                        windowState.y,

                    width:
                        windowState.width,

                    height:
                        windowState.height,
                };


                windowState.maximized = true;


                this.scheduleWindowSessionSave();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Restore
            |--------------------------------------------------------------------------
            */

            windowState.maximized = false;


            if (windowState.restore) {

                windowState.x =
                    windowState.restore.x;

                windowState.y =
                    windowState.restore.y;

                windowState.width =
                    windowState.restore.width;

                windowState.height =
                    windowState.restore.height;
            }


            windowState.restore = null;
            this.fitWindowGeometry(windowState, this.getWorkspaceRect());
            this.scheduleWindowSessionSave();
        },

        /*
        |--------------------------------------------------------------------------
        | Window State Helpers
        |--------------------------------------------------------------------------
        */

        isWindowRunning(id) {
            return (
                this.getWindow(id)?.open === true
            );
        },


        isWindowMinimized(id) {
            const windowState =
                this.windows[id];


            if (!windowState) {
                return false;
            }


            return (
                windowState.open === true &&
                windowState.minimized === true
            );
        },

        isWindowVisible(id) {
            const windowState =
                this.windows[id];


            if (!windowState) {
                return false;
            }


            return (
                windowState.open === true &&
                windowState.minimized === false
            );
        },


        isWindowFocused(id) {
            return (
                this.activeWindow === id &&
                this.isWindowVisible(id)
            );
        },


        isWindowMaximizable(id) {
            return this.getWindow(id)?.maximizable !== false;
        },


        isWindowResizable(id) {
            return this.getWindow(id)?.resizable !== false;
        },


        windowStyle(id) {
            const windowState =
                this.getWindow(id);


            if (!windowState) {
                return '';
            }


            if (windowState.maximized) {

                return `
                    left: 0px;
                    top: 0px;

                    width: 100%;
                    height: 100%;

                    z-index:
                        ${windowState.zIndex};
                `;
            }


            return `
                left:
                    ${windowState.x}px;

                top:
                    ${windowState.y}px;

                width:
                    ${windowState.width}px;

                height:
                    ${windowState.height}px;

                z-index:
                    ${windowState.zIndex};
            `;
        },


        /*
        |--------------------------------------------------------------------------
        | Pointer Events
        |--------------------------------------------------------------------------
        */

        initPointerEvents() {
            this.pointerMoveHandler =
                event => {
                    this.handlePointerMove(event);
                };


            this.pointerUpHandler =
                event => {
                    this.handlePointerUp(event);
                };


            this.viewportResizeHandler =
                () => {
                    this.handleWorkspaceResize();
                };


            window.addEventListener(
                'pointermove',
                this.pointerMoveHandler
            );


            window.addEventListener(
                'pointerup',
                this.pointerUpHandler
            );


            window.addEventListener(
                'resize',
                this.viewportResizeHandler
            );
        },


        /*
        |--------------------------------------------------------------------------
        | Drag
        |--------------------------------------------------------------------------
        */

        startDrag(event, id) {
            if (event.button !== 0) {
                return;
            }


            const windowState =
                this.getWindow(id);


            if (
                !windowState ||
                windowState.maximized
            ) {
                return;
            }


            this.focusWindow(
                id,
                {
                    syncUrl: true,
                }
            );


            this.dragState = {
                id,

                startPointerX:
                    event.clientX,

                startPointerY:
                    event.clientY,

                startX:
                    windowState.x,

                startY:
                    windowState.y,
            };


            document.body.style.userSelect =
                'none';

            document.body.style.cursor =
                'grabbing';


            event.preventDefault();
        },


        /*
        |--------------------------------------------------------------------------
        | Resize
        |--------------------------------------------------------------------------
        */

        startResize(
            event,
            id,
            direction
        ) {
            if (event.button !== 0) {
                return;
            }


            const windowState =
                this.getWindow(id);


            if (
                !windowState ||
                windowState.maximized ||
                windowState.resizable === false
            ) {
                return;
            }


            this.focusWindow(
                id,
                {
                    syncUrl: true,
                }
            );


            this.resizeState = {
                id,
                direction,

                startPointerX:
                    event.clientX,

                startPointerY:
                    event.clientY,

                startX:
                    windowState.x,

                startY:
                    windowState.y,

                startWidth:
                    windowState.width,

                startHeight:
                    windowState.height,
            };


            document.body.style.userSelect =
                'none';

            event.preventDefault();
        },


        /*
        |--------------------------------------------------------------------------
        | Pointer Move
        |--------------------------------------------------------------------------
        */

        handlePointerMove(event) {
            if (this.dockDrag?.active) {
                this.handleDockDragMove(event);

                return;
            }

            if (this.dragState) {
                this.handleDrag(event);

                return;
            }


            if (this.resizeState) {
                this.handleResize(event);
            }
        },


        handleDrag(event) {
            const state =
                this.dragState;


            const windowState =
                this.getWindow(state.id);


            if (!windowState) {
                return;
            }


            const workspace =
                this.getWorkspaceRect();


            const deltaX =
                event.clientX -
                state.startPointerX;


            const deltaY =
                event.clientY -
                state.startPointerY;


            const nextX =
                state.startX +
                deltaX;


            const nextY =
                state.startY +
                deltaY;


            windowState.x =
                this.clamp(
                    nextX,
                    0,
                    Math.max(
                        0,
                        workspace.width -
                        windowState.width
                    )
                );


            /*
             * Kita biarkan header selalu
             * berada di dalam workspace.
             */
            windowState.y =
                this.clamp(
                    nextY,
                    0,
                    Math.max(
                        0,
                        workspace.height - 40
                    )
                );
        },


        /*
        |--------------------------------------------------------------------------
        | Resize Logic
        |--------------------------------------------------------------------------
        */

        handleResize(event) {
            const state =
                this.resizeState;


            const windowState =
                this.getWindow(state.id);


            if (!windowState) {
                return;
            }


            const workspace =
                this.getWorkspaceRect();


            const dx =
                event.clientX -
                state.startPointerX;


            const dy =
                event.clientY -
                state.startPointerY;


            const direction =
                state.direction;


            const minimumWidth =
                Math.min(
                    windowState.minWidth,
                    workspace.width
                );


            const minimumHeight =
                Math.min(
                    windowState.minHeight,
                    workspace.height
                );


            /*
             * EAST
             */
            if (
                direction.includes('e')
            ) {
                windowState.width =
                    this.clamp(
                        state.startWidth + dx,

                        minimumWidth,

                        workspace.width -
                        state.startX
                    );
            }


            /*
             * SOUTH
             */
            if (
                direction.includes('s')
            ) {
                windowState.height =
                    this.clamp(
                        state.startHeight + dy,

                        minimumHeight,

                        workspace.height -
                        state.startY
                    );
            }


            /*
             * WEST
             */
            if (
                direction.includes('w')
            ) {
                const right =
                    state.startX +
                    state.startWidth;


                const nextX =
                    this.clamp(
                        state.startX + dx,

                        0,

                        right -
                        minimumWidth
                    );


                windowState.x =
                    nextX;


                windowState.width =
                    right -
                    nextX;
            }


            /*
             * NORTH
             */
            if (
                direction.includes('n')
            ) {
                const bottom =
                    state.startY +
                    state.startHeight;


                const nextY =
                    this.clamp(
                        state.startY + dy,

                        0,

                        bottom -
                        minimumHeight
                    );


                windowState.y =
                    nextY;


                windowState.height =
                    bottom -
                    nextY;
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Pointer Up
        |--------------------------------------------------------------------------
        */

        handlePointerUp(event) {
            if (this.dockDrag) {
                this.handleDockDragEnd(event);
            }

            const wasInteracting =
                !!this.dragState ||
                !!this.resizeState;


            this.dragState = null;
            this.resizeState = null;


            document.body.style.userSelect =
                '';

            document.body.style.cursor =
                '';


            if (wasInteracting) {
                this.scheduleWindowSessionSave();
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Browser Resize
        |--------------------------------------------------------------------------
        */

        handleWorkspaceResize() {
            const workspace =
                this.getWorkspaceRect();


            Object.values(this.windows)
                .forEach(windowState => {

                    if (
                        !windowState.open ||
                        windowState.maximized
                    ) {
                        return;
                    }


                    windowState.width =
                        Math.min(
                            windowState.width,
                            workspace.width
                        );


                    windowState.height =
                        Math.min(
                            windowState.height,
                            workspace.height
                        );


                    windowState.x =
                        this.clamp(
                            windowState.x,
                            0,
                            Math.max(
                                0,
                                workspace.width -
                                windowState.width
                            )
                        );


                    windowState.y =
                        this.clamp(
                            windowState.y,
                            0,
                            Math.max(
                                0,
                                workspace.height - 40
                            )
                        );
                });
            this.scheduleWindowSessionSave();
        },


        /*
        |--------------------------------------------------------------------------
        | Activities
        |--------------------------------------------------------------------------
        */


        toggleAboutModal() {
            const willOpen = !this.aboutOpen;
            this.closeAll();
            this.aboutOpen = willOpen;
        },

        /*
        |--------------------------------------------------------------------------
        | Applications
        |--------------------------------------------------------------------------
        */

        toggleApplications() {
            const willOpen =
                !this.applicationsOpen;

            this.closeAll();

            this.applicationsOpen =
                willOpen;
        },


        /*
        |--------------------------------------------------------------------------
        | System Menu
        |--------------------------------------------------------------------------
        */

        toggleSystemMenu() {
            const willOpen =
                !this.systemMenuOpen;

            this.closeAll();

            this.systemMenuOpen =
                willOpen;
        },


        /*
        |--------------------------------------------------------------------------
        | Context Menu
        |--------------------------------------------------------------------------
        */

        openContextMenu(event) {
            this.closeAll();

            const menuWidth = 230;
            const menuHeight = 260;

            let x = event.clientX;
            let y = event.clientY;

            if (x + menuWidth > window.innerWidth) {
                x = window.innerWidth - menuWidth - 8;
            }

            if (y + menuHeight > window.innerHeight) {
                y = window.innerHeight - menuHeight - 8;
            }

            this.contextMenu = {
                open: true,
                type: 'desktop',
                appId: null,
                x: Math.max(8, x),
                y: Math.max(36, y),
            };
        },

        openDockContextMenu(event, appId) {
            this.closeAll();

            const menuWidth = 210;
            const menuHeight = this.isWindowRunning(appId) ? 195 : 105;

            let x = event.clientX;
            let y = event.clientY;

            const dockPos = this.settings?.dock?.position ?? 'bottom';

            if (dockPos === 'bottom') {
                y = event.clientY - menuHeight - 12;
                x = event.clientX - (menuWidth / 2);
            } else if (dockPos === 'left') {
                x = event.clientX + 14;
                y = event.clientY - (menuHeight / 2);
            } else if (dockPos === 'right') {
                x = event.clientX - menuWidth - 14;
                y = event.clientY - (menuHeight / 2);
            }

            if (x + menuWidth > window.innerWidth) {
                x = window.innerWidth - menuWidth - 8;
            }
            if (y + menuHeight > window.innerHeight) {
                y = window.innerHeight - menuHeight - 8;
            }

            this.contextMenu = {
                open: true,
                type: 'dock',
                appId: appId,
                x: Math.max(8, x),
                y: Math.max(36, y),
            };
        },

        openLauncherContextMenu(event, appId) {
            this.closeContextMenu();
            this.systemMenuOpen = false;

            const menuWidth = 200;
            const menuHeight = 110;

            let x = event.clientX;
            let y = event.clientY;

            if (x + menuWidth > window.innerWidth) {
                x = window.innerWidth - menuWidth - 8;
            }
            if (y + menuHeight > window.innerHeight) {
                y = window.innerHeight - menuHeight - 8;
            }

            this.contextMenu = {
                open: true,
                type: 'launcher',
                appId: appId,
                x: Math.max(8, x),
                y: Math.max(36, y),
            };
        },

        openTrashContextMenu(event) {
            this.closeAll();

            const menuWidth = 220;
            const menuHeight = 150;

            let x = event.clientX;
            let y = event.clientY;

            if (x + menuWidth > window.innerWidth) {
                x = window.innerWidth - menuWidth - 8;
            }
            if (y + menuHeight > window.innerHeight) {
                y = window.innerHeight - menuHeight - 8;
            }

            this.contextMenu = {
                open: true,
                type: 'trash',
                appId: null,
                x: Math.max(8, x),
                y: Math.max(36, y),
            };
        },

        closeContextMenu() {
            this.contextMenu.open = false;
            this.contextMenu.appId = null;
        },

        /*
        |--------------------------------------------------------------------------
        | Dock App Pinning & Drag-to-Reorder
        |--------------------------------------------------------------------------
        */

        getPinnedAppIds() {
            if (this.settings?.dock?.pinned_apps && Array.isArray(this.settings.dock.pinned_apps) && this.settings.dock.pinned_apps.length > 0) {
                return [...this.settings.dock.pinned_apps];
            }
            return Object.keys(this.applications).filter(
                id => Boolean(this.applications[id]?.pinned)
            );
        },

        getDockAppIds() {
            const pinned = this.getPinnedAppIds();
            const running = Object.keys(this.windows || {}).filter(
                id => this.isWindowRunning(id) && !pinned.includes(id) && Boolean(this.applications[id])
            );
            return [...pinned, ...running];
        },

        getDockAppOrder(id) {
            const list = this.getDockAppIds();
            const idx = list.indexOf(id);
            return idx === -1 ? 999 : idx;
        },

        isAppPinned(id) {
            return this.getPinnedAppIds().includes(id);
        },

        isAppInDock(id) {
            return this.isAppPinned(id) || this.isWindowRunning(id);
        },

        isDockDragging(id = null) {
            if (this.dockDrag?.active) {
                return id ? this.dockDrag.appId === id : true;
            }
            if (this.dockDrop) {
                return id ? this.dockDrop.appId === id : true;
            }
            return false;
        },

        isDockDragEnabled() {
            const val = this.settings?.dock?.enable_drag;
            if (val === false || val === 'false' || val === 0 || val === '0') {
                return false;
            }
            return true;
        },

        isDockClickSuppressed(id) {
            return this.dockDragSuppressedId === id;
        },

        measureDockItems() {
            const dockNav = document.querySelector('[data-desktop-dock] nav');
            if (!dockNav) return [];

            const wrappers = Array.from(dockNav.querySelectorAll('[data-dock-wrapper]'));
            const visible = wrappers.filter(el => {
                const id = el.getAttribute('data-dock-wrapper');
                return id && this.isAppInDock(id);
            });

            visible.sort((a, b) => {
                const idA = a.getAttribute('data-dock-wrapper');
                const idB = b.getAttribute('data-dock-wrapper');
                return this.getDockAppOrder(idA) - this.getDockAppOrder(idB);
            });

            return visible.map(el => {
                const id = el.getAttribute('data-dock-wrapper');
                const rect = el.getBoundingClientRect();
                return {
                    id,
                    rect,
                    centerX: rect.left + rect.width / 2,
                    centerY: rect.top + rect.height / 2,
                    width: rect.width,
                    height: rect.height,
                };
            });
        },

        startDockDrag(event, appId) {
            if (!this.isDockDragEnabled()) return;
            if (event.button !== 0 && event.pointerType === 'mouse') return;

            this.closeContextMenu();
            this.dockDrop = null;

            const dockPos = this.settings?.dock?.position ?? 'bottom';
            const isHorizontal = dockPos === 'bottom';
            const visibleItems = this.measureDockItems();
            const initialIndex = visibleItems.findIndex(item => item.id === appId);

            if (initialIndex === -1) return;

            let slotSize = 48;
            if (visibleItems.length > 1) {
                const first = visibleItems[0];
                const last = visibleItems[visibleItems.length - 1];
                slotSize = isHorizontal
                    ? Math.abs(last.centerX - first.centerX) / (visibleItems.length - 1)
                    : Math.abs(last.centerY - first.centerY) / (visibleItems.length - 1);
            } else if (visibleItems.length === 1) {
                slotSize = isHorizontal ? visibleItems[0].width : visibleItems[0].height;
            }
            if (!slotSize || slotSize <= 0) slotSize = 48;

            this.dockDrag = {
                active: false,
                hasMoved: false,
                appId: appId,
                startX: event.clientX,
                startY: event.clientY,
                currentX: event.clientX,
                currentY: event.clientY,
                deltaX: 0,
                deltaY: 0,
                initialIndex: initialIndex,
                targetIndex: initialIndex,
                slotSize: slotSize,
                isHorizontal: isHorizontal,
                visibleItems: visibleItems,
                pointerId: event.pointerId,
                targetElement: event.currentTarget,
            };

            try {
                if (event.currentTarget && typeof event.currentTarget.setPointerCapture === 'function') {
                    event.currentTarget.setPointerCapture(event.pointerId);
                }
            } catch (e) {}
        },

        handleDockDragMove(event) {
            if (!this.dockDrag || !this.isDockDragEnabled()) return;

            const dx = event.clientX - this.dockDrag.startX;
            const dy = event.clientY - this.dockDrag.startY;

            if (!this.dockDrag.hasMoved) {
                const threshold = 4;
                if (Math.hypot(dx, dy) >= threshold) {
                    this.dockDrag.hasMoved = true;
                    this.dockDrag.active = true;
                    this.dockDragSuppressedId = this.dockDrag.appId;
                    document.body.style.userSelect = 'none';
                    document.body.style.cursor = 'grabbing';
                } else {
                    return;
                }
            }

            if (!this.dockDrag.active) return;

            this.dockDrag.currentX = event.clientX;
            this.dockDrag.currentY = event.clientY;

            if (this.dockDrag.isHorizontal) {
                this.dockDrag.deltaX = dx;
                this.dockDrag.deltaY = Math.max(-14, Math.min(14, dy));
            } else {
                this.dockDrag.deltaX = Math.max(-14, Math.min(14, dx));
                this.dockDrag.deltaY = dy;
            }

            const items = this.dockDrag.visibleItems;
            if (items.length > 0) {
                let bestIndex = this.dockDrag.initialIndex;
                let minDistance = Infinity;
                const pointerCoord = this.dockDrag.isHorizontal ? event.clientX : event.clientY;

                items.forEach((item, index) => {
                    const itemCoord = this.dockDrag.isHorizontal ? item.centerX : item.centerY;
                    const dist = Math.abs(pointerCoord - itemCoord);
                    if (dist < minDistance) {
                        minDistance = dist;
                        bestIndex = index;
                    }
                });

                this.dockDrag.targetIndex = Math.max(0, Math.min(items.length - 1, bestIndex));
            }
        },

        handleDockDragEnd(event) {
            if (!this.dockDrag) return;

            const drag = this.dockDrag;

            try {
                const targetEl = drag.targetElement || event?.currentTarget;
                if (targetEl && typeof targetEl.releasePointerCapture === 'function' && drag.pointerId !== undefined) {
                    targetEl.releasePointerCapture(drag.pointerId);
                }
            } catch (e) {}

            document.body.style.userSelect = '';
            document.body.style.cursor = '';

            if (drag.hasMoved && drag.active) {
                this.dockDragSuppressedId = drag.appId;

                const hasReordered = drag.targetIndex !== drag.initialIndex && drag.visibleItems.length > 0;
                let dropOffsetX = 0;
                let dropOffsetY = 0;

                if (hasReordered) {
                    const currentDockAppIds = this.getDockAppIds();
                    const fromId = drag.appId;
                    const toItem = drag.visibleItems[drag.targetIndex];

                    if (toItem && toItem.id !== fromId) {
                        const fromIdx = currentDockAppIds.indexOf(fromId);
                        if (fromIdx !== -1) {
                            currentDockAppIds.splice(fromIdx, 1);
                            const toIdx = currentDockAppIds.indexOf(toItem.id);
                            if (toIdx !== -1) {
                                if (drag.targetIndex > drag.initialIndex) {
                                    currentDockAppIds.splice(toIdx + 1, 0, fromId);
                                } else {
                                    currentDockAppIds.splice(toIdx, 0, fromId);
                                }
                            } else {
                                currentDockAppIds.push(fromId);
                            }

                            if (!this.settings) this.settings = {};
                            if (!this.settings.dock) this.settings.dock = {};
                            this.settings.dock.pinned_apps = [...currentDockAppIds];

                            if (this.applications[fromId]) {
                                this.applications[fromId].pinned = true;
                            }

                            this.persistDockPinnedApps(this.settings.dock.pinned_apps);
                        }
                    }

                    const initialItem = drag.visibleItems[drag.initialIndex];
                    const targetItem = drag.visibleItems[drag.targetIndex];
                    const targetDistX = targetItem && initialItem ? (targetItem.centerX - initialItem.centerX) : 0;
                    const targetDistY = targetItem && initialItem ? (targetItem.centerY - initialItem.centerY) : 0;

                    dropOffsetX = drag.deltaX - targetDistX;
                    dropOffsetY = drag.deltaY - targetDistY;
                } else {
                    dropOffsetX = drag.deltaX;
                    dropOffsetY = drag.deltaY;
                }

                this.dockDrop = {
                    appId: drag.appId,
                    offsetX: dropOffsetX,
                    offsetY: dropOffsetY,
                    animating: false,
                };

                this.dockDrag = null;

                requestAnimationFrame(() => {
                    if (this.dockDrop) {
                        this.dockDrop.animating = true;
                    }
                });

                setTimeout(() => {
                    this.dockDrop = null;
                    this.dockDragSuppressedId = null;
                }, 240);
            } else {
                this.dockDrag = null;
                this.dockDrop = null;
                setTimeout(() => {
                    this.dockDragSuppressedId = null;
                }, 100);
            }
        },

        getDockItemStyle(id) {
            if (!this.isAppInDock(id)) {
                return 'display: none !important;';
            }

            const order = this.getDockAppOrder(id);
            let style = `order: ${order};`;

            // Active dragging
            if (this.dockDrag && this.dockDrag.active) {
                const drag = this.dockDrag;

                if (drag.appId === id) {
                    const tx = drag.deltaX;
                    const ty = drag.deltaY;
                    return `${style} transform: translate3d(${tx}px, ${ty}px, 0) scale(1.08); z-index: 60; opacity: 0.95; filter: drop-shadow(0 10px 18px rgba(0,0,0,0.3)); transition: none; pointer-events: none;`;
                }

                const items = drag.visibleItems;
                const curIdx = items.findIndex(item => item.id === id);

                if (curIdx === -1) {
                    return style;
                }

                let shift = 0;
                const initIdx = drag.initialIndex;
                const targetIdx = drag.targetIndex;
                const slot = drag.slotSize;

                if (targetIdx > initIdx) {
                    if (curIdx > initIdx && curIdx <= targetIdx) {
                        shift = -slot;
                    }
                } else if (targetIdx < initIdx) {
                    if (curIdx >= targetIdx && curIdx < initIdx) {
                        shift = slot;
                    }
                }

                if (shift !== 0) {
                    const tx = drag.isHorizontal ? shift : 0;
                    const ty = drag.isHorizontal ? 0 : shift;
                    return `${style} transform: translate3d(${tx}px, ${ty}px, 0); transition: transform 0.22s cubic-bezier(0.2, 0, 0, 1);`;
                }

                return `${style} transform: translate3d(0, 0, 0); transition: transform 0.22s cubic-bezier(0.2, 0, 0, 1);`;
            }

            // Smooth drop landing animation
            if (this.dockDrop) {
                if (this.dockDrop.appId === id) {
                    if (this.dockDrop.animating) {
                        return `${style} transform: translate3d(0, 0, 0) scale(1); z-index: 60; opacity: 1; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); transition: transform 0.22s cubic-bezier(0.2, 0, 0, 1), scale 0.22s cubic-bezier(0.2, 0, 0, 1), filter 0.22s ease-out; pointer-events: none;`;
                    }
                    const ox = this.dockDrop.offsetX;
                    const oy = this.dockDrop.offsetY;
                    return `${style} transform: translate3d(${ox}px, ${oy}px, 0) scale(1.08); z-index: 60; opacity: 0.98; transition: none; pointer-events: none;`;
                }

                // Neighbor items: ALREADY in their new order slot. Must NOT jump or animate!
                return `${style} transform: translate3d(0, 0, 0); transition: none !important;`;
            }

            return `${style} transform: translate3d(0, 0, 0); transition: none;`;
        },

        pinApp(id) {
            if (!this.settings) this.settings = {};
            if (!this.settings.dock) this.settings.dock = {};

            const list = this.getPinnedAppIds();
            if (!list.includes(id)) {
                list.push(id);
            }

            this.settings.dock.pinned_apps = list;
            if (this.applications[id]) {
                this.applications[id].pinned = true;
            }

            this.persistDockPinnedApps(list);
        },

        unpinApp(id) {
            if (!this.settings) this.settings = {};
            if (!this.settings.dock) this.settings.dock = {};

            const list = this.getPinnedAppIds().filter(appId => appId !== id);

            this.settings.dock.pinned_apps = list;
            if (this.applications[id]) {
                this.applications[id].pinned = false;
            }

            this.persistDockPinnedApps(list);
        },

        persistDockPinnedApps(list) {
            try {
                localStorage.setItem('minios:dock:pinned_apps', JSON.stringify(list));
            } catch (e) {}

            if (window.Livewire) {
                Livewire.dispatch('update-dock-setting', { key: 'pinned_apps', value: list });
            }
        },


        /*
        |--------------------------------------------------------------------------
        | Notifications & Sound Chime
        |--------------------------------------------------------------------------
        */

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

                // Deduplicate rapidly repeated notifications (e.g. if both window and Livewire.on catch the same event)
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

            // Clear entrance animation lock after 200ms so standard transition applies on hover/stack
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

        /*
        |--------------------------------------------------------------------------
        | Web Audio System & Autoplay Policy Management
        |--------------------------------------------------------------------------
        */

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
                // User wants to mute
                this.settings.notifications.sound = false;
                this.audioUnlocked = false;
                try {
                    await ctx.suspend();
                } catch (e) {}
            } else {
                // User wants to enable audio
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
        },

        playNotificationChime() {
            try {
                const isSoundEnabled = this.settings?.notifications?.sound !== false;
                if (!isSoundEnabled) return;

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
                    gain.gain.linearRampToValueAtTime(0.12, startTime + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.0001, startTime + duration);

                    osc.connect(gain);
                    gain.connect(ctx.destination);

                    osc.start(startTime);
                    osc.stop(startTime + duration);
                };

                // Gentle Windows 11 dual chime: D5 (587.33Hz) -> A5 (880Hz)
                playTone(587.33, now, 0.18);
                playTone(880.00, now + 0.09, 0.28);
            } catch (e) {
                // Ignore audio context errors gracefully
            }
        },

        toggleNotificationCenter() {
            const willOpen = !this.notificationCenterOpen;
            this.closeAll();
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


        /*
        |--------------------------------------------------------------------------
        | Overlay
        |--------------------------------------------------------------------------
        */

        closeAll() {
            this.activitiesOpen = false;
            this.applicationsOpen = false;
            this.systemMenuOpen = false;
            this.notificationCenterOpen = false;
            this.aboutOpen = false;
            this.selectedShortcut = null;

            this.closeContextMenu();
        },

        toggleFullscreen() {
            if (!document.fullscreenElement) {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen().catch(() => {});
                }
                this.isFullscreen = true;
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                    this.isFullscreen = false;
                }
            }
        },

        /*
        |--------------------------------------------------------------------------
        | Window Session Persistence
        |--------------------------------------------------------------------------
        */

        scheduleWindowSessionSave() {
            if (this.persistTimer) {
                clearTimeout(this.persistTimer);
            }

            this.persistTimer = setTimeout(() => {
                this.flushWindowSession();
            }, 100);
        },

        flushWindowSession() {
            if (this.persistTimer !== null) clearTimeout(this.persistTimer);
            this.persistTimer = null;
            this.saveWindowSession();
        },

        saveWindowSession() {
            const windows = {};


            Object.entries(this.windows)
                .forEach(([id, windowState]) => {

                    windows[id] = {
                        open:
                            windowState.open,

                        minimized:
                            windowState.minimized,

                        maximized:
                            windowState.maximized,

                        x:
                            windowState.x,

                        y:
                            windowState.y,

                        width:
                            windowState.width,

                        height:
                            windowState.height,

                        zIndex:
                            windowState.zIndex,

                        url:
                            windowState.url,

                        restore:
                            windowState.restore,
                    };

                });


            const session = {
                version: 2,
                windows,

                activeWindow:
                    this.activeWindow,

                zIndexCounter:
                    this.zIndexCounter,
            };


            try {
                localStorage.setItem(
                    this.windowSessionKey,
                    JSON.stringify(session)
                );
            } catch (error) {
                console.warn(
                    'Unable to persist desktop session.',
                    error
                );
            }
        },


        restoreWindowSession() {
            let session;
            try {
                const stored = localStorage.getItem(this.windowSessionKey)
                    ?? localStorage.getItem('web-desktop:window-session:v1');
                if (!stored) return;
                session = JSON.parse(stored);
            } catch (error) {
                console.warn('Unable to restore desktop session.', error);
                return;
            }

            const isRecord = value => value !== null
                && typeof value === 'object' && !Array.isArray(value);
            const isNumber = value => typeof value === 'number' && Number.isFinite(value);

            if (!isRecord(session) || !isRecord(session.windows)) return;

            const workspace = this.getWorkspaceRect();

            for (const [id, saved] of Object.entries(session.windows)) {
                // Ignore removed applications and malformed entries independently.
                if (!Object.hasOwn(this.windows, id) || !isRecord(saved)) continue;
                const state = this.windows[id];

                for (const key of ['x', 'y', 'width', 'height']) {
                    if (isNumber(saved[key])) state[key] = saved[key];
                }
                this.fitWindowGeometry(state, workspace);

                state.open = saved.open === true;
                state.minimized = state.open && saved.minimized === true;
                // A minimized window may still need to restore to maximized mode.
                state.maximized = state.maximizable !== false && saved.maximized === true;

                if (state.resizable === false) {
                    const appSettings = this.applications[id]?.window ?? {};
                    if (typeof appSettings.width === 'number') state.width = appSettings.width;
                    if (typeof appSettings.height === 'number') state.height = appSettings.height;
                }
                state.url = this.resolveWindowUrl(id, saved.url);
                state.zIndex = isNumber(saved.zIndex) ? saved.zIndex : state.zIndex;

                state.restore = null;
                if (isRecord(saved.restore)
                    && ['x', 'y', 'width', 'height'].every(key => isNumber(saved.restore[key]))
                    && saved.restore.width > 0 && saved.restore.height > 0) {
                    state.restore = {
                        x: saved.restore.x, y: saved.restore.y,
                        width: saved.restore.width, height: saved.restore.height,
                    };
                    this.fitWindowGeometry(state.restore, workspace, state);
                }
            }

            // Rebuild a finite counter from actual windows, preserving their order.
            // Do not trust an old counter or let windows cover the shell overlays.
            this.normalizeWindowStack();
            this.activeWindow = typeof session.activeWindow === 'string'
                && this.isWindowVisible(session.activeWindow)
                ? session.activeWindow : null;
        },

        resolveWindowUrl(id, savedUrl) {
            const application = this.applications[id];
            const entry = application.entry ?? application.routes?.[0] ?? '/';
            if (typeof savedUrl !== 'string' || !savedUrl.trim()) return entry;

            try {
                const target = new URL(savedUrl, window.location.origin);
                if (target.origin !== window.location.origin
                    || this.resolveApplication(target.pathname)?.id !== id) return entry;
                return this.normalizePath(target.pathname) + target.search + target.hash;
            } catch {
                return entry;
            }
        },

        fitWindowGeometry(geometry, workspace, settings = geometry) {
            geometry.width = this.clamp(geometry.width,
                Math.min(settings.minWidth ?? 300, workspace.width), workspace.width);
            geometry.height = this.clamp(geometry.height,
                Math.min(settings.minHeight ?? 220, workspace.height), workspace.height);
            geometry.x = this.clamp(geometry.x, 0, Math.max(0, workspace.width - geometry.width));
            geometry.y = this.clamp(geometry.y, 0, Math.max(0, workspace.height - 40));
        },

        normalizeWindowStack() {
            const ordered = Object.values(this.windows).sort((a, b) => a.zIndex - b.zIndex);
            this.zIndexCounter = 100;
            for (const state of ordered) state.zIndex = ++this.zIndexCounter;
        },

        nextWindowZIndex() {
            if (!Number.isFinite(this.zIndexCounter) || this.zIndexCounter >= 8000) {
                this.normalizeWindowStack();
            }
            return ++this.zIndexCounter;
        },

        /*
        |--------------------------------------------------------------------------
        | Desktop Layout
        |--------------------------------------------------------------------------
        */
        dockRevealOverride: false,
        dockHovered: false,

        shouldExpandWorkspace() {
            if (!this.activeWindow) {
                return false;
            }


            const windowState =
                this.getWindow(
                    this.activeWindow
                );


            return !!(
                windowState &&
                windowState.open &&
                !windowState.minimized &&
                windowState.maximized
            );
        },


        shouldHideDock() {
            if (this.dockHovered || this.applicationsOpen || this.activitiesOpen || this.dockDrag?.active || this.dockDrop) {
                return false;
            }

            if (this.settings?.dock?.autohide) {
                return true;
            }

            return (
                this.shouldExpandWorkspace() &&
                !this.dockRevealOverride
            );
        },

        getWorkspaceStyles() {
            const rawSize = this.settings?.dock?.size || 'medium';
            const sizeMap = { small: 44, medium: 56, large: 68 };
            const size = typeof rawSize === 'number' ? rawSize : (sizeMap[rawSize] || parseInt(rawSize, 10) || 56);

            const pos = this.settings?.dock?.position || 'bottom';
            const hidden = this.shouldHideDock();

            let left = '0px';
            let right = '0px';
            let bottom = '0px';
            let top = '28px';

            if (!hidden) {
                if (pos === 'left') {
                    left = `${size}px`;
                } else if (pos === 'right') {
                    right = `${size}px`;
                } else if (pos === 'bottom') {
                    bottom = `${size}px`;
                }
            }

            return `left: ${left}; right: ${right}; bottom: ${bottom}; top: ${top}; z-index: 0; isolation: isolate;`;
        },

        isWindowInteracting(id) {
            return (
                this.dragState?.id === id ||
                this.resizeState?.id === id
            );
        },

        /*
        |--------------------------------------------------------------------------
        | Window Animation
        |--------------------------------------------------------------------------
        */

        async animateWindowToDock(id) {
            const windowElement =
                document.querySelector(
                    `[data-window-id="${id}"]`
                );


            if (!windowElement) {
                return;
            }


            const dockItem =
                document.querySelector(
                    `[data-dock-app="${id}"]`
                );


            /*
            * Kalau aplikasi tidak pinned,
            * cukup fade + shrink.
            */
            if (!dockItem) {

                const animation =
                    windowElement.animate(
                        [
                            {
                                transform:
                                    'scale(1)',

                                opacity: 1,
                            },

                            {
                                transform:
                                    'scale(0.8)',

                                opacity: 0,
                            },
                        ],

                        {
                            duration: 220,

                            easing:
                                'cubic-bezier(0.4, 0, 1, 1)',

                            fill:
                                'forwards',
                        }
                    );


                try {
                    await animation.finished;
                } catch {
                    //
                }


                animation.cancel();

                return;
            }


            const windowRect =
                windowElement.getBoundingClientRect();


            const targetRect =
                dockItem.getBoundingClientRect();


            const dock =
                document.querySelector(
                    '[data-desktop-dock]'
                );


            const dockWidth =
                dock?.getBoundingClientRect().width
                ?? 64;


            /*
            * Y tetap valid meskipun dock sedang
            * transform keluar layar.
            *
            * X kita gunakan posisi FINAL dock:
            * setengah dari width Dock.
            */
            const targetCenterX =
                dockWidth / 2;


            const targetCenterY =
                targetRect.top +
                targetRect.height / 2;


            const windowCenterX =
                windowRect.left +
                windowRect.width / 2;


            const windowCenterY =
                windowRect.top +
                windowRect.height / 2;


            const translateX =
                targetCenterX -
                windowCenterX;


            const translateY =
                targetCenterY -
                windowCenterY;


            const scaleX =
                Math.max(
                    0.04,
                    targetRect.width /
                    windowRect.width
                );


            const scaleY =
                Math.max(
                    0.04,
                    targetRect.height /
                    windowRect.height
                );


            const animation =
                windowElement.animate(
                    [
                        {
                            transform:
                                'translate(0px, 0px) scale(1, 1)',

                            opacity: 1,

                            filter:
                                'blur(0px)',
                        },

                        {
                            transform:
                                `
                                    translate(
                                        ${translateX}px,
                                        ${translateY}px
                                    )
                                    scale(
                                        ${scaleX},
                                        ${scaleY}
                                    )
                                `,

                            opacity: 0.15,

                            filter:
                                'blur(1px)',
                        },
                    ],

                    {
                        duration: 300,

                        easing:
                            'cubic-bezier(0.4, 0, 0.2, 1)',

                        fill:
                            'forwards',
                    }
                );


            try {
                await animation.finished;
            } catch {
                //
            }


            return animation;
        },
    };
}
