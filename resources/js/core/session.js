/**
 * MiniOS Desktop - Window Session & Layout Persistence Manager
 * Handles saving/restoring window coordinates, size, z-index stack, and open/minimized/maximized states across browser reloads.
 */

export function createSessionManager() {
    return {
        windowSessionKey: 'web-desktop:window-session:v2',
        persistTimer: null,
        pagehideHandler: null,
        visibilityChangeHandler: null,

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

            Object.entries(this.windows).forEach(([id, windowState]) => {
                windows[id] = {
                    open: windowState.open,
                    minimized: windowState.minimized,
                    maximized: windowState.maximized,
                    x: windowState.x,
                    y: windowState.y,
                    width: windowState.width,
                    height: windowState.height,
                    zIndex: windowState.zIndex,
                    url: windowState.url,
                    restore: windowState.restore,
                };
            });

            const session = {
                version: 2,
                windows,
                activeWindow: this.activeWindow,
                zIndexCounter: this.zIndexCounter,
            };

            try {
                localStorage.setItem(
                    this.windowSessionKey,
                    JSON.stringify(session)
                );
            } catch (error) {
                console.warn('Unable to persist desktop session.', error);
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
                        x: saved.restore.x,
                        y: saved.restore.y,
                        width: saved.restore.width,
                        height: saved.restore.height,
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
            geometry.width = this.clamp(
                geometry.width,
                Math.min(settings.minWidth ?? 300, workspace.width),
                workspace.width
            );
            geometry.height = this.clamp(
                geometry.height,
                Math.min(settings.minHeight ?? 220, workspace.height),
                workspace.height
            );
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
    };
}
