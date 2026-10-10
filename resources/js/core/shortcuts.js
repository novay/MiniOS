/**
 * MiniOS Desktop - Keyboard Shortcuts Manager
 * Handles global keybindings (launcher, volume controls, window close).
 */

export function createShortcuts() {
    return {
        keydownHandler: null,

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
                const isCmdOrCtrl = event.metaKey || event.ctrlKey;

                // Global Volume Shortcuts: Cmd+F12 (Up), Cmd+F11 (Down)
                const isF12 = event.key === 'F12' || event.code === 'F12';
                const isF11 = event.key === 'F11' || event.code === 'F11';

                if (isCmdOrCtrl && isF12) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.increaseGlobalVolume(0.05);
                    return;
                }

                if (isCmdOrCtrl && isF11) {
                    event.preventDefault();
                    event.stopPropagation();
                    this.decreaseGlobalVolume(0.05);
                    return;
                }

                const isLauncherCmd = isCmdOrCtrl
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

                // 2. Cmd+W / Ctrl+W / Alt+W (Option+W) to close active window
                const isCloseWindowShortcut = (event.metaKey || event.ctrlKey || event.altKey)
                    && !event.shiftKey
                    && (event.key === 'w' || event.key === 'W' || event.code === 'KeyW');

                if (!isCloseWindowShortcut) {
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

            // 4. If no open window exists in state, but URL is not desktop root, navigate back to desktop root
            const rootPath = this.desktopPath ? this.desktopPath() : '/';
            if (this.normalizePath(window.location.pathname) !== this.normalizePath(rootPath)) {
                this.navigate(rootPath, { replace: true });
            }
        },
    };
}
