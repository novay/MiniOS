/**
 * MiniOS Desktop - Client Router
 * Handles browser URL synchronization, popstate history, application route matching,
 * and client-side navigation.
 */

export function createRouter() {
    return {
        activeApplication: null,
        currentPath: '/',
        currentUrl: '/',
        popstateHandler: null,

        /*
        |--------------------------------------------------------------------------
        | Router Initialization
        |--------------------------------------------------------------------------
        */

        initRouter() {
            this.syncRoute();

            this.popstateHandler = () => {
                this.syncRoute();
            };

            window.addEventListener('popstate', this.popstateHandler);
        },

        normalizePath(path) {
            if (!path) {
                return '/';
            }

            let normalized = path;

            if (!normalized.startsWith('/')) {
                normalized = `/${normalized}`;
            }

            if (normalized.length > 1 && normalized.endsWith('/')) {
                normalized = normalized.slice(0, -1);
            }

            return normalized;
        },

        resolveApplication(path) {
            const normalizedPath = this.normalizePath(path);
            const matches = [];

            Object.entries(this.applications).forEach(([id, application]) => {
                const routes = application.routes ?? [];

                routes.forEach((route) => {
                    const normalizedRoute = this.normalizePath(route);
                    const exactMatch = normalizedPath === normalizedRoute;
                    const childMatch = normalizedPath.startsWith(`${normalizedRoute}/`);

                    if (exactMatch || childMatch) {
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
             * Example: /sales vs /sales/admin
             */
            matches.sort((a, b) => b.route.length - a.route.length);

            return matches[0] ?? null;
        },

        syncRoute() {
            const pathname = this.normalizePath(window.location.pathname);

            this.currentPath = pathname;
            this.currentUrl = pathname + window.location.search + window.location.hash;

            const resolved = this.resolveApplication(pathname);

            /*
             * Desktop root / unknown route.
             */
            if (!resolved) {
                this.activeApplication = null;
                this.activeWindow = null;
                return;
            }

            let subPath = pathname.slice(resolved.route.length);
            if (!subPath) {
                subPath = '/';
            }

            this.activeApplication = {
                id: resolved.id,
                name: resolved.application.name,
                icon: resolved.application.icon,
                entry: resolved.application.entry,
                baseRoute: resolved.route,
                path: pathname,
                subPath,
                config: resolved.application,
            };

            /*
            |--------------------------------------------------------------------------
            | Open Window From URL
            |--------------------------------------------------------------------------
            */

            this.openWindow(resolved.id, {
                url: this.currentUrl,
                focus: false,
            });

            /*
            |--------------------------------------------------------------------------
            | URL decides active window
            |--------------------------------------------------------------------------
            */

            const windowState = this.getWindow(resolved.id);

            if (windowState) {
                windowState.open = true;
                windowState.minimized = false;
                windowState.zIndex = this.nextWindowZIndex();
                this.activeWindow = resolved.id;
            }
        },

        navigate(url, options = {}) {
            const { replace = false } = options;

            const target = new URL(url, window.location.origin);
            const pathname = this.normalizePath(target.pathname);
            const browserUrl = pathname + target.search + target.hash;

            if (browserUrl === this.currentUrl) {
                this.syncRoute();
                this.closeAll();
                return;
            }

            if (replace) {
                window.history.replaceState({}, '', browserUrl);
            } else {
                window.history.pushState({}, '', browserUrl);
            }

            this.syncRoute();
            this.closeAll();
        },
    };
}
