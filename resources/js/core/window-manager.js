/**
 * MiniOS Desktop - Window Manager & Interactions
 * Handles window lifecycle (bootstrap, open, close, focus, minimize, maximize),
 * pointer dragging, resizing, boundary clamping, and responsive workspace layout.
 */

export function createWindowManager() {
    return {
        windows: {},
        activeWindow: null,
        dragState: null,
        resizeState: null,
        pointerMoveHandler: null,
        pointerUpHandler: null,
        viewportResizeHandler: null,

        /*
        |--------------------------------------------------------------------------
        | Bootstrap Windows
        |--------------------------------------------------------------------------
        */

        bootstrapWindows() {
            const workspace = this.getWorkspaceRect();
            const windows = {};
            let index = 0;

            Object.entries(this.applications).forEach(([id, application]) => {
                const settings = application.window ?? {};

                const desiredWidth = settings.width ?? 900;
                const desiredHeight = settings.height ?? 600;

                const width = Math.min(
                    desiredWidth,
                    Math.max(300, workspace.width - 40)
                );

                const height = Math.min(
                    desiredHeight,
                    Math.max(220, workspace.height - 40)
                );

                const cascade = (index % 8) * 28;

                const centeredX = Math.max(0, (workspace.width - width) / 2);
                const centeredY = Math.max(0, (workspace.height - height) / 2);

                const isCentered = settings.center === true || id === 'about';

                const x = isCentered
                    ? centeredX
                    : this.clamp(
                        centeredX + cascade,
                        0,
                        Math.max(0, workspace.width - width)
                    );

                const y = isCentered
                    ? centeredY
                    : this.clamp(
                        centeredY + cascade,
                        0,
                        Math.max(0, workspace.height - height)
                    );

                windows[id] = {
                    id,
                    title: application.name,
                    icon: application.icon,
                    open: false,
                    minimized: false,
                    maximized: false,
                    x,
                    y,
                    width,
                    height,
                    minWidth: settings.min_width ?? 420,
                    minHeight: settings.min_height ?? 280,
                    resizable: settings.resizable !== false,
                    maximizable: settings.maximizable !== false,
                    zIndex: ++this.zIndexCounter,
                    url: application.entry ?? application.routes?.[0] ?? '/',
                    restore: null,
                };

                index++;
            });

            this.windows = windows;
        },

        /*
        |--------------------------------------------------------------------------
        | Window Creation & Retrieval
        |--------------------------------------------------------------------------
        */

        ensureWindow(id) {
            const windowState = this.windows[id];

            if (!windowState) {
                console.warn(`Desktop window [${id}] is not registered.`);
                return null;
            }

            return windowState;
        },

        getWindow(id) {
            return Object.hasOwn(this.windows, id) ? this.windows[id] : null;
        },

        /*
        |--------------------------------------------------------------------------
        | Open & Center
        |--------------------------------------------------------------------------
        */

        openWindow(id, { url = null, focus = true } = {}) {
            const windowState = this.ensureWindow(id);
            if (!windowState) return;

            if (id === 'about' || this.applications[id]?.window?.center) {
                this.centerWindow(id);
            }

            windowState.open = true;
            windowState.minimized = false;

            if (url) {
                windowState.url = url;
            }

            if (focus) {
                windowState.zIndex = this.nextWindowZIndex();
                this.activeWindow = id;
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
            const application = this.applications[id];

            if (!application) {
                console.warn(`Desktop application [${id}] is not registered.`);
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

            const windowState = this.getWindow(id);

            if (windowState && windowState.open) {
                windowState.minimized = false;
                this.focusWindow(id, { syncUrl: true });
                this.closeAll();
                return;
            }

            const entry = application.entry ?? application.routes?.[0];
            if (!entry) {
                console.warn(`Desktop application [${id}] has no entry route.`);
                return;
            }

            this.openWindow(id, { url: entry, focus: false });
            this.navigate(entry);
            this.closeAll();
        },

        /*
        |--------------------------------------------------------------------------
        | Focus & Close
        |--------------------------------------------------------------------------
        */

        focusWindow(id, { syncUrl = true } = {}) {
            const windowState = this.getWindow(id);
            if (!windowState || !windowState.open) return;

            windowState.minimized = false;
            windowState.zIndex = this.nextWindowZIndex();
            this.activeWindow = id;

            if (syncUrl && windowState.url && this.currentUrl !== windowState.url) {
                const target = new URL(windowState.url, window.location.origin);
                const browserUrl = this.normalizePath(target.pathname) + target.search + target.hash;

                window.history.replaceState({}, '', browserUrl);
                this.currentPath = this.normalizePath(target.pathname);
                this.currentUrl = browserUrl;

                const resolved = this.resolveApplication(this.currentPath);
                if (resolved) {
                    let subPath = this.currentPath.slice(resolved.route.length);
                    if (!subPath) subPath = '/';

                    this.activeApplication = {
                        id: resolved.id,
                        name: resolved.application.name,
                        icon: resolved.application.icon,
                        entry: resolved.application.entry,
                        baseRoute: resolved.route,
                        path: this.currentPath,
                        subPath,
                        config: resolved.application,
                    };
                }
            }

            this.scheduleWindowSessionSave();
        },

        getTopVisibleWindow(excludeId = null) {
            const candidates = Object.values(this.windows).filter(
                window => window.id !== excludeId && window.open && !window.minimized
            );

            candidates.sort((a, b) => b.zIndex - a.zIndex);
            return candidates[0] ?? null;
        },

        closeWindow(id) {
            const windowState = this.getWindow(id);
            if (!windowState) return;

            windowState.open = false;
            windowState.minimized = false;

            if (this.activeWindow === id) {
                this.activeWindow = null;
                const next = this.getTopVisibleWindow(id);

                if (next) {
                    this.focusWindow(next.id, { syncUrl: true });
                } else {
                    this.navigate('/', { replace: true });
                }
            }

            this.scheduleWindowSessionSave();
        },

        /*
        |--------------------------------------------------------------------------
        | Minimize & Maximize
        |--------------------------------------------------------------------------
        */

        async minimizeWindow(id) {
            const windowState = this.getWindow(id);
            if (!windowState || windowState.minimized) return;

            if (windowState.maximized) {
                this.dockRevealOverride = true;
            }

            const animation = await this.animateWindowToDock(id);
            windowState.minimized = true;

            if (this.activeWindow === id) {
                this.activeWindow = null;
                const next = this.getTopVisibleWindow(id);

                if (next) {
                    this.focusWindow(next.id, { syncUrl: true });
                } else {
                    this.navigate('/', { replace: true });
                }
            }

            this.dockRevealOverride = false;
            this.scheduleWindowSessionSave();

            if (animation) {
                requestAnimationFrame(() => {
                    animation.cancel();
                });
            }
        },

        toggleMaximizeWindow(id) {
            const windowState = this.getWindow(id);
            if (!windowState || windowState.maximizable === false) return;

            this.focusWindow(id, { syncUrl: false });

            if (!windowState.maximized) {
                windowState.restore = {
                    x: windowState.x,
                    y: windowState.y,
                    width: windowState.width,
                    height: windowState.height,
                };
                windowState.maximized = true;
                this.scheduleWindowSessionSave();
                return;
            }

            windowState.maximized = false;

            if (windowState.restore) {
                windowState.x = windowState.restore.x;
                windowState.y = windowState.restore.y;
                windowState.width = windowState.restore.width;
                windowState.height = windowState.restore.height;
            }

            windowState.restore = null;
            this.fitWindowGeometry(windowState, this.getWorkspaceRect());
            this.scheduleWindowSessionSave();
        },

        /*
        |--------------------------------------------------------------------------
        | Window State Helpers & Styles
        |--------------------------------------------------------------------------
        */

        isWindowRunning(id) {
            return this.getWindow(id)?.open === true;
        },

        isWindowMinimized(id) {
            const windowState = this.windows[id];
            return Boolean(windowState && windowState.open === true && windowState.minimized === true);
        },

        isWindowVisible(id) {
            const windowState = this.windows[id];
            return Boolean(windowState && windowState.open === true && windowState.minimized === false);
        },

        isWindowFocused(id) {
            return this.activeWindow === id && this.isWindowVisible(id);
        },

        isWindowMaximizable(id) {
            return this.getWindow(id)?.maximizable !== false;
        },

        isWindowResizable(id) {
            return this.getWindow(id)?.resizable !== false;
        },

        isWindowInteracting(id) {
            return (
                this.dragState?.id === id ||
                this.resizeState?.id === id
            );
        },

        windowStyle(id) {
            const windowState = this.getWindow(id);
            if (!windowState) return '';

            if (windowState.maximized) {
                return `left: 0px; top: 0px; width: 100%; height: 100%; z-index: ${windowState.zIndex};`;
            }

            return `left: ${windowState.x}px; top: ${windowState.y}px; width: ${windowState.width}px; height: ${windowState.height}px; z-index: ${windowState.zIndex};`;
        },

        /*
        |--------------------------------------------------------------------------
        | Pointer Events (Drag & Resize)
        |--------------------------------------------------------------------------
        */

        initPointerEvents() {
            this.pointerMoveHandler = (event) => {
                this.handlePointerMove(event);
            };

            this.pointerUpHandler = (event) => {
                this.handlePointerUp(event);
            };

            this.viewportResizeHandler = () => {
                this.handleWorkspaceResize();
            };

            window.addEventListener('pointermove', this.pointerMoveHandler);
            window.addEventListener('pointerup', this.pointerUpHandler);
            window.addEventListener('resize', this.viewportResizeHandler);
        },

        startDrag(event, id) {
            if (event.button !== 0) return;

            const windowState = this.getWindow(id);
            if (!windowState || windowState.maximized) return;

            this.focusWindow(id, { syncUrl: true });

            this.dragState = {
                id,
                startPointerX: event.clientX,
                startPointerY: event.clientY,
                startX: windowState.x,
                startY: windowState.y,
            };

            document.body.style.userSelect = 'none';
            document.body.style.cursor = 'grabbing';
            event.preventDefault();
        },

        startResize(event, id, direction) {
            if (event.button !== 0) return;

            const windowState = this.getWindow(id);
            if (!windowState || windowState.maximized || windowState.resizable === false) return;

            this.focusWindow(id, { syncUrl: true });

            this.resizeState = {
                id,
                direction,
                startPointerX: event.clientX,
                startPointerY: event.clientY,
                startX: windowState.x,
                startY: windowState.y,
                startWidth: windowState.width,
                startHeight: windowState.height,
            };

            document.body.style.userSelect = 'none';
            event.preventDefault();
        },

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
            const state = this.dragState;
            const windowState = this.getWindow(state.id);
            if (!windowState) return;

            const workspace = this.getWorkspaceRect();
            const deltaX = event.clientX - state.startPointerX;
            const deltaY = event.clientY - state.startPointerY;

            const nextX = state.startX + deltaX;
            const nextY = state.startY + deltaY;

            windowState.x = this.clamp(
                nextX,
                0,
                Math.max(0, workspace.width - windowState.width)
            );

            // Header stays within workspace boundaries
            windowState.y = this.clamp(
                nextY,
                0,
                Math.max(0, workspace.height - 40)
            );
        },

        handleResize(event) {
            const state = this.resizeState;
            const windowState = this.getWindow(state.id);
            if (!windowState) return;

            const workspace = this.getWorkspaceRect();
            const dx = event.clientX - state.startPointerX;
            const dy = event.clientY - state.startPointerY;
            const direction = state.direction;

            const minimumWidth = Math.min(windowState.minWidth, workspace.width);
            const minimumHeight = Math.min(windowState.minHeight, workspace.height);

            // East
            if (direction.includes('e')) {
                windowState.width = this.clamp(
                    state.startWidth + dx,
                    minimumWidth,
                    workspace.width - state.startX
                );
            }

            // South
            if (direction.includes('s')) {
                windowState.height = this.clamp(
                    state.startHeight + dy,
                    minimumHeight,
                    workspace.height - state.startY
                );
            }

            // West
            if (direction.includes('w')) {
                const right = state.startX + state.startWidth;
                const nextX = this.clamp(
                    state.startX + dx,
                    0,
                    right - minimumWidth
                );
                windowState.x = nextX;
                windowState.width = right - nextX;
            }

            // North
            if (direction.includes('n')) {
                const bottom = state.startY + state.startHeight;
                const nextY = this.clamp(
                    state.startY + dy,
                    0,
                    bottom - minimumHeight
                );
                windowState.y = nextY;
                windowState.height = bottom - nextY;
            }
        },

        handlePointerUp(event) {
            if (this.dockDrag) {
                this.handleDockDragEnd(event);
            }

            const wasInteracting = Boolean(this.dragState || this.resizeState);

            this.dragState = null;
            this.resizeState = null;

            document.body.style.userSelect = '';
            document.body.style.cursor = '';

            if (wasInteracting) {
                this.scheduleWindowSessionSave();
            }
        },

        handleWorkspaceResize() {
            const workspace = this.getWorkspaceRect();

            Object.values(this.windows).forEach((windowState) => {
                if (!windowState.open || windowState.maximized) {
                    return;
                }

                windowState.width = Math.min(windowState.width, workspace.width);
                windowState.height = Math.min(windowState.height, workspace.height);

                windowState.x = this.clamp(
                    windowState.x,
                    0,
                    Math.max(0, workspace.width - windowState.width)
                );

                windowState.y = this.clamp(
                    windowState.y,
                    0,
                    Math.max(0, workspace.height - 40)
                );
            });

            this.scheduleWindowSessionSave();
        },
    };
}
