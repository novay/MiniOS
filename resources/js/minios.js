import { createDockManager } from './core/dock.js';
import { createNotificationManager } from './core/notifications.js';
import { createRouter } from './core/router.js';
import { createSessionManager } from './core/session.js';
import { createShortcuts } from './core/shortcuts.js';
import { createSystemUI } from './core/system-ui.js';
import { mergeSlices } from './core/utils.js';
import { createWindowManager } from './core/window-manager.js';

export default function minios(applications = {}, userSettings = {}, basePath = '/') {
    const instance = {

        /*
        |--------------------------------------------------------------------------
        | Applications, Settings & Base Path
        |--------------------------------------------------------------------------
        */

        applications,
        settings: userSettings,
        basePath: basePath || '/',
        initialized: false,

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
            */
            this.bootstrapWindows();

            /*
            |--------------------------------------------------------------------------
            | 2. Restore previous browser session & dock pins
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
                window.removeEventListener('popstate', this.popstateHandler);
            }
            if (this.pointerMoveHandler) {
                window.removeEventListener('pointermove', this.pointerMoveHandler);
            }
            if (this.pointerUpHandler) {
                window.removeEventListener('pointerup', this.pointerUpHandler);
            }
            if (this.viewportResizeHandler) {
                window.removeEventListener('resize', this.viewportResizeHandler);
            }
            if (this.persistTimer) {
                clearTimeout(this.persistTimer);
            }
            this.clockTimer = null;
            this.persistTimer = null;
        },

        /*
        |--------------------------------------------------------------------------
        | Root Helpers
        |--------------------------------------------------------------------------
        */

        clamp(value, min, max) {
            if (max < min) return max;
            return Math.min(Math.max(value, min), max);
        },

        getWorkspaceRect() {
            if (this.$refs?.workspace) {
                const rect = this.$refs.workspace.getBoundingClientRect();
                if (rect.width > 0 && rect.height > 0) {
                    return { width: rect.width, height: rect.height };
                }
            }

            return {
                width: Math.max(1, window.innerWidth - 64),
                height: Math.max(1, window.innerHeight - 28),
            };
        },
    };

    return mergeSlices(
        instance,
        createSystemUI(),
        createNotificationManager(),
        createShortcuts(),
        createDockManager(),
        createSessionManager(),
        createRouter(),
        createWindowManager()
    );
}
