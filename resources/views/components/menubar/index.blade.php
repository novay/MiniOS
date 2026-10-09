<div
    x-data="{
        activeMenu: null,
        get isAnyOpen() {
            return this.activeMenu !== null;
        },
        openMenu(id) {
            this.activeMenu = id;
        },
        closeMenu() {
            this.activeMenu = null;
        },
        toggleMenu(id) {
            this.activeMenu = (this.activeMenu === id) ? null : id;
        },
        hoverMenu(id) {
            if (this.isAnyOpen) {
                this.activeMenu = id;
            }
        }
    }"
    @click.outside="closeMenu()"
    @keydown.escape.window="closeMenu()"
    class="relative flex items-center gap-0.5 select-none"
    {{ $attributes }}
>
    {{-- Transparent click catcher for iframes and background when menu is open --}}
    <div
        x-cloak
        x-show="isAnyOpen"
        @click="closeMenu()"
        class="fixed inset-0 z-40 bg-transparent"
        style="display: none;"
    ></div>

    <div class="relative z-50 flex items-center gap-0.5">
        {{ $slot }}
    </div>
</div>
