@props([
    'appId' => null,
    'width' => 260,
    'minWidth' => 160,
    'maxWidth' => 420,
    'collapsed' => false,
    'statusbar' => true,
])

<div
    x-data="{
        appId: @js($appId),
        sidebarCollapsed: @js($collapsed),
        sidebarWidth: Number(@js($width)),
        lastExpandedWidth: Number(@js($width)),
        minWidth: Number(@js($minWidth)),
        maxWidth: Number(@js($maxWidth)),
        statusbarVisible: @js($statusbar),
        isResizing: false,
        isRefreshing: false,
        menuSearch: '',

        getAppId() {
            return this.appId || this.$el?.closest('[data-window-id]')?.getAttribute('data-window-id') || null;
        },

        refresh() {
            if (this.isRefreshing) return;
            this.isRefreshing = true;

            // Reset client-side menu search filter
            this.menuSearch = '';

            const startTime = Date.now();
            const minLoadingTime = 550;

            const finish = () => {
                const elapsed = Date.now() - startTime;
                const remaining = Math.max(0, minLoadingTime - elapsed);
                setTimeout(() => {
                    this.isRefreshing = false;
                }, remaining);
            };

            const wire = this.$wire;
            if (wire) {
                if (typeof wire.refresh === 'function') {
                    try {
                        const res = wire.refresh();
                        if (res && typeof res.then === 'function') {
                            res.then(() => finish()).catch(() => finish());
                        } else {
                            finish();
                        }
                    } catch (err) {
                        finish();
                    }
                    return;
                }

                if (typeof wire.$refresh === 'function') {
                    try {
                        const res = wire.$refresh();
                        if (res && typeof res.then === 'function') {
                            res.then(() => finish()).catch(() => finish());
                        } else {
                            finish();
                        }
                    } catch (err) {
                        finish();
                    }
                } else {
                    finish();
                }
            } else {
                finish();
            }
        },

        toggleSidebar() {
            if (this.sidebarCollapsed) {
                this.expandSidebar();
            } else {
                this.collapseSidebar();
            }
        },

        collapseSidebar() {
            const currentW = Number(this.sidebarWidth);
            const minW = Number(this.minWidth);
            this.lastExpandedWidth = currentW >= minW ? currentW : Number(@js($width));
            this.sidebarCollapsed = true;
            this.menuSearch = '';
        },

        expandSidebar() {
            this.sidebarCollapsed = false;
            const lastW = Number(this.lastExpandedWidth);
            const minW = Number(this.minWidth);
            this.sidebarWidth = lastW >= minW ? lastW : Number(@js($width));
        },

        toggleStatusbar() {
            this.statusbarVisible = !this.statusbarVisible;
        },

        startSidebarResize(e) {
            if (e.button !== 0) return;

            if (this.sidebarCollapsed) {
                this.sidebarCollapsed = false;
            }
            this.isResizing = true;
            document.body.classList.add('cursor-col-resize', 'select-none');

            const startX = e.clientX;
            const startWidth = Number(this.sidebarWidth) || 260;
            const minW = Number(this.minWidth) || 160;
            const maxW = Number(this.maxWidth) || 420;

            const onMouseMove = (ev) => {
                if (!this.isResizing) return;
                ev.preventDefault();
                const deltaX = ev.clientX - startX;
                const newWidth = Math.min(maxW, Math.max(minW, Math.round(startWidth + deltaX)));
                this.sidebarWidth = newWidth;
                this.lastExpandedWidth = newWidth;
            };

            const onMouseUp = () => {
                this.isResizing = false;
                document.body.classList.remove('cursor-col-resize', 'select-none');
                window.removeEventListener('mousemove', onMouseMove);
                window.removeEventListener('mouseup', onMouseUp);
                window.removeEventListener('pointermove', onMouseMove);
                window.removeEventListener('pointerup', onMouseUp);
            };

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
            window.addEventListener('pointermove', onMouseMove);
            window.addEventListener('pointerup', onMouseUp);
        },

        startResize(e) {
            this.startSidebarResize(e);
        },

        stopResize(onMouseMove, onMouseUp) {
            this.isResizing = false;
            document.body.classList.remove('cursor-col-resize', 'select-none');
            window.removeEventListener('mousemove', onMouseMove);
            window.removeEventListener('mouseup', onMouseUp);
            window.removeEventListener('pointermove', onMouseMove);
            window.removeEventListener('pointerup', onMouseUp);
        },

        handleKeyboardShortcut(e) {
            const currentAppId = this.getAppId();

            // Jika appId didefinisikan, periksa apakah jendela ini sedang aktif
            if (currentAppId) {
                if (typeof this.isWindowFocused === 'function' && !this.isWindowFocused(currentAppId)) {
                    return;
                }
                if (typeof this.activeWindow !== 'undefined' && this.activeWindow && this.activeWindow !== currentAppId) {
                    return;
                }
            }

            const isModifier = e.metaKey || e.ctrlKey || e.altKey;
            if (!isModifier) return;

            const key = e.key ? e.key.toLowerCase() : '';
            const tag = (e.target.tagName || '').toLowerCase();
            const isEditing = tag === 'input' || tag === 'textarea' || e.target.isContentEditable;

            // ⌘+W / Ctrl+W / ⌥+W: Tutup Jendela Aktif
            const isW = key === 'w' || e.code === 'KeyW';
            if (isW && !e.shiftKey) {
                e.preventDefault();
                e.stopPropagation();
                if (typeof this.closeWindow === 'function' && currentAppId) {
                    this.closeWindow(currentAppId);
                } else {
                    this.$dispatch('close-window', { id: currentAppId });
                }
                return;
            }
            // ⌘+B: Toggle Sidebar
            else if (key === 'b') {
                e.preventDefault();
                e.stopPropagation();
                this.toggleSidebar();
            }
            // ⌘+P: Disable / Enable Status Bar
            else if (key === 'p') {
                e.preventDefault();
                e.stopPropagation();
                this.toggleStatusbar();
            }
            // ⌘+Shift+R / Ctrl+Shift+R: Refresh full halaman browser
            else if (key === 'r' && e.shiftKey) {
                window.location.reload();
            }
            // ⌘+R: Refresh / Muat Ulang Livewire dengan efek blur & loading icon
            else if (key === 'r' && !e.shiftKey) {
                e.preventDefault();
                e.stopPropagation();
                this.refresh();
            }
            // ⌘+A: About / Bantuan (hanya jika tidak sedang mengetik di input/textarea)
            else if (key === 'a' && !isEditing) {
                e.preventDefault();
                e.stopPropagation();
                this.$dispatch('open-about');
            }
        }
    }"
    @refresh-window.window="
        if (!$event.detail?.id || $event.detail?.id === getAppId()) {
            refresh();
        }
    "
    @keydown.window="handleKeyboardShortcut($event)"
    :class="{ 'select-none cursor-col-resize': isResizing }"
    {{ $attributes->merge([
        'class' => 'relative grid h-full w-full min-h-0 min-w-0 overflow-hidden bg-[#f3f3f3] dark:bg-[#202020] text-neutral-800 dark:text-neutral-100 font-sans select-none grid-cols-[auto_minmax(0,1fr)] grid-rows-[auto_minmax(0,1fr)_auto] [grid-template-areas:\'menu_menu\'_\'sidebar_content\'_\'sidebar_statusbar\']'
    ]) }}
>
    {{ $slot }}

    {{-- Refresh Loading Overlay Gimmick --}}
    <div
        x-cloak
        x-show="isRefreshing"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="pointer-events-auto absolute inset-0 z-50 flex flex-col items-center justify-center bg-white/40 dark:bg-black/50 backdrop-blur-md select-none"
    >
        <div
            x-show="isRefreshing"
            x-transition:enter="transition cubic-bezier(0.16, 1, 0.3, 1) duration-250"
            x-transition:enter-start="opacity-0 scale-90 translate-y-1"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-1"
            class="flex flex-col items-center gap-3 px-6 py-5 rounded-2xl bg-white/85 dark:bg-[#1e1e1e]/90 border border-neutral-200/80 dark:border-white/10 shadow-2xl backdrop-blur-xl"
        >
            <div class="relative flex items-center justify-center">
                <flux:icon name="arrow-path" class="size-7 text-[var(--accent-color,#3b82f6)] animate-spin" />
            </div>
            <div class="flex flex-col items-center gap-0.5 text-center">
                <span class="text-xs font-semibold tracking-tight text-neutral-800 dark:text-neutral-100">
                    {{ __('Memuat Ulang...') }}
                </span>
                <span class="text-[10px] text-neutral-500 dark:text-neutral-400">
                    {{ __('Menyinkronkan data jendela') }}
                </span>
            </div>
        </div>
    </div>
</div>
