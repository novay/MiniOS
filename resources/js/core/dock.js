/**
 * MiniOS Desktop - Dock & Context Menu Manager
 * Handles dock drag-and-drop reordering, pinning, autohide, layout geometry,
 * minimize animation to dock, and context menus (desktop, dock, launcher, trash).
 */

export function createDockManager() {
    return {
        dockDrag: null,
        dockDrop: null,
        dockDragSuppressedId: null,
        dockRevealOverride: false,
        dockHovered: false,

        contextMenu: {
            open: false,
            type: 'desktop',
            appId: null,
            x: 0,
            y: 0,
        },

        /*
        |--------------------------------------------------------------------------
        | Context Menus
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
        | Desktop Layout & Dock Visibility
        |--------------------------------------------------------------------------
        */

        shouldExpandWorkspace() {
            if (!this.activeWindow) {
                return false;
            }

            const windowState = this.getWindow(this.activeWindow);

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

        /*
        |--------------------------------------------------------------------------
        | Window Animation to Dock
        |--------------------------------------------------------------------------
        */

        async animateWindowToDock(id) {
            const windowElement = document.querySelector(`[data-window-id="${id}"]`);
            if (!windowElement) return;

            const dockItem = document.querySelector(`[data-dock-app="${id}"]`);

            /*
            * Kalau aplikasi tidak pinned, cukup fade + shrink.
            */
            if (!dockItem) {
                const animation = windowElement.animate(
                    [
                        { transform: 'scale(1)', opacity: 1 },
                        { transform: 'scale(0.8)', opacity: 0 },
                    ],
                    {
                        duration: 220,
                        easing: 'cubic-bezier(0.4, 0, 1, 1)',
                        fill: 'forwards',
                    }
                );

                try {
                    await animation.finished;
                } catch {}

                animation.cancel();
                return;
            }

            const windowRect = windowElement.getBoundingClientRect();
            const targetRect = dockItem.getBoundingClientRect();
            const dock = document.querySelector('[data-desktop-dock]');
            const dockWidth = dock?.getBoundingClientRect().width ?? 64;

            const targetCenterX = dockWidth / 2;
            const targetCenterY = targetRect.top + targetRect.height / 2;
            const windowCenterX = windowRect.left + windowRect.width / 2;
            const windowCenterY = windowRect.top + windowRect.height / 2;

            const translateX = targetCenterX - windowCenterX;
            const translateY = targetCenterY - windowCenterY;

            const scaleX = Math.max(0.04, targetRect.width / windowRect.width);
            const scaleY = Math.max(0.04, targetRect.height / windowRect.height);

            const animation = windowElement.animate(
                [
                    {
                        transform: 'translate(0px, 0px) scale(1, 1)',
                        opacity: 1,
                        filter: 'blur(0px)',
                    },
                    {
                        transform: `translate(${translateX}px, ${translateY}px) scale(${scaleX}, ${scaleY})`,
                        opacity: 0.15,
                        filter: 'blur(1px)',
                    },
                ],
                {
                    duration: 300,
                    easing: 'cubic-bezier(0.4, 0, 0.2, 1)',
                    fill: 'forwards',
                }
            );

            try {
                await animation.finished;
            } catch {}

            return animation;
        },
    };
}
