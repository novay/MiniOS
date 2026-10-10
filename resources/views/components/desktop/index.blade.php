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
        sidebarWidth: @js($width),
        lastExpandedWidth: @js($width),
        minWidth: @js($minWidth),
        maxWidth: @js($maxWidth),
        statusbarVisible: @js($statusbar),
        isResizing: false,

        toggleSidebar() {
            if (this.sidebarCollapsed) {
                this.expandSidebar();
            } else {
                this.collapseSidebar();
            }
        },

        collapseSidebar() {
            this.lastExpandedWidth = this.sidebarWidth >= this.minWidth ? this.sidebarWidth : @js($width);
            this.sidebarCollapsed = true;
        },

        expandSidebar() {
            this.sidebarCollapsed = false;
            this.sidebarWidth = this.lastExpandedWidth >= this.minWidth ? this.lastExpandedWidth : @js($width);
        },

        toggleStatusbar() {
            this.statusbarVisible = !this.statusbarVisible;
        },

        startResize(e) {
            if (this.sidebarCollapsed) {
                this.sidebarCollapsed = false;
            }
            this.isResizing = true;
            const startX = e.clientX;
            const startWidth = this.sidebarWidth;

            const onMouseMove = (ev) => {
                if (!this.isResizing) return;
                const deltaX = ev.clientX - startX;
                const newWidth = Math.min(this.maxWidth, Math.max(this.minWidth, startWidth + deltaX));
                this.sidebarWidth = newWidth;
                this.lastExpandedWidth = newWidth;
            };

            const onMouseUp = () => {
                this.stopResize(onMouseMove, onMouseUp);
            };

            window.addEventListener('mousemove', onMouseMove);
            window.addEventListener('mouseup', onMouseUp);
        },

        stopResize(onMouseMove, onMouseUp) {
            this.isResizing = false;
            window.removeEventListener('mousemove', onMouseMove);
            window.removeEventListener('mouseup', onMouseUp);
        },

        handleKeyboardShortcut(e) {
            // Jika appId didefinisikan, periksa apakah jendela ini sedang aktif
            if (this.appId && typeof activeWindow !== 'undefined' && activeWindow !== this.appId) {
                return;
            }

            const isCmdOrCtrl = e.metaKey || e.ctrlKey;
            if (!isCmdOrCtrl) return;

            const key = e.key ? e.key.toLowerCase() : '';
            const tag = (e.target.tagName || '').toLowerCase();
            const isEditing = tag === 'input' || tag === 'textarea' || e.target.isContentEditable;

            // ⌘+B: Toggle Sidebar
            if (key === 'b') {
                e.preventDefault();
                this.toggleSidebar();
            }
            // ⌘+P: Disable / Enable Status Bar
            else if (key === 'p') {
                e.preventDefault();
                this.toggleStatusbar();
            }
            // ⌘+R: Refresh / Muat Ulang Livewire tanpa reload browser
            else if (key === 'r') {
                e.preventDefault();
                if (typeof $wire !== 'undefined' && $wire.$refresh) {
                    $wire.$refresh();
                }
            }
            // ⌘+A: About / Bantuan (hanya jika tidak sedang mengetik di input/textarea)
            else if (key === 'a' && !isEditing) {
                e.preventDefault();
                this.$dispatch('open-about');
            }
        }
    }"
    @keydown.window="handleKeyboardShortcut($event)"
    :class="{ 'select-none cursor-col-resize': isResizing }"
    {{ $attributes->merge([
        'class' => 'relative grid h-full w-full min-h-0 min-w-0 overflow-hidden bg-[#f3f3f3] dark:bg-[#202020] text-neutral-800 dark:text-neutral-100 font-sans select-none grid-cols-[auto_minmax(0,1fr)] grid-rows-[auto_minmax(0,1fr)_auto] [grid-template-areas:\'menu_menu\'_\'sidebar_content\'_\'sidebar_statusbar\']'
    ]) }}
>
    {{ $slot }}
</div>
