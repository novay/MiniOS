<div
    id="taskbar"
    x-data="{
        getDockPx() {
            const val = settings?.dock?.size || 'medium';
            if (val === 'small') return 44;
            if (val === 'medium') return 56;
            if (val === 'large') return 68;
            return parseInt(val, 10) || 56;
        }
    }"
    @mouseenter="dockHovered = true"
    @mouseleave="dockHovered = false"
>
    {{-- Autohide Edge Trigger Zone when hidden --}}
    <div
        x-cloak
        x-show="settings?.dock?.autohide && !applicationsOpen"
        class="fixed z-[8999]"
        :class="{
            'bottom-0 left-0 right-0 h-2': (settings?.dock?.position ?? 'bottom') === 'bottom',
            'left-0 top-7 bottom-0 w-2': (settings?.dock?.position ?? 'bottom') === 'left',
            'right-0 top-7 bottom-0 w-2': (settings?.dock?.position ?? 'bottom') === 'right',
        }"
    ></div>

    <aside
        x-cloak
        data-desktop-dock
        :class="{
            '-translate-x-full': (settings?.dock?.position ?? 'bottom') === 'left' && shouldHideDock(),
            'translate-x-full': (settings?.dock?.position ?? 'bottom') === 'right' && shouldHideDock(),
            'translate-y-full': (settings?.dock?.position ?? 'bottom') === 'bottom' && shouldHideDock(),
            'bottom-0 left-0 right-0 flex-row px-4 items-center justify-center border-t border-white/5 dark:border-white/10 bg-white/25 dark:bg-[#181818]/35 backdrop-blur-2xl backdrop-saturate-150 shadow-lg': (settings?.dock?.position ?? 'bottom') === 'bottom',
            'bottom-0 left-0 top-7 flex-col py-2 items-center justify-start border-r border-white/5 dark:border-white/10 bg-white/25 dark:bg-[#181818]/35 backdrop-blur-2xl backdrop-saturate-150 shadow-lg': (settings?.dock?.position ?? 'bottom') === 'left',
            'bottom-0 right-0 top-7 flex-col py-2 items-center justify-start border-l border-white/5 dark:border-white/10 bg-white/25 dark:bg-[#181818]/35 backdrop-blur-2xl backdrop-saturate-150 shadow-lg': (settings?.dock?.position ?? 'bottom') === 'right',
            'opacity-0 pointer-events-none': applicationsOpen,
        }"
        :style="(settings?.dock?.position ?? 'bottom') === 'bottom'
            ? 'height: ' + getDockPx() + 'px'
            : 'width: ' + getDockPx() + 'px'"
        class="desktop-dock absolute z-[9000] flex transform-gpu transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] select-none"
    >
        <nav
            class="flex items-center justify-center gap-1 sm:gap-1.5"
            :class="{
                'flex-row h-full': (settings?.dock?.position ?? 'bottom') === 'bottom',
                'flex-col w-full': (settings?.dock?.position ?? 'bottom') !== 'bottom',
            }"
        >
            {{-- SHOW APPLICATIONS (Windows 11 Start Menu Button) --}}
            <div
                class="flex"
                :class="{
                    'order-first': (settings?.dock?.position ?? 'bottom') === 'bottom',
                    'order-last mt-auto': (settings?.dock?.position ?? 'bottom') !== 'bottom',
                }"
            >
                <x-minios.dock-item
                    label="Start Menu"
                    @click.stop="toggleApplications()"
                >
                    <x-minios.icon
                        name="windows"
                        class="text-[#0078d4]"
                    />
                </x-minios.dock-item>
            </div>

            {{-- DOCK APPLICATIONS (PINNED & RUNNING) --}}
            @foreach (config('desktop.applications') as $id => $application)
                <div
                    x-cloak
                    x-show="isAppInDock(@js($id))"
                    class="transition-all duration-200"
                >
                    <x-minios.dock-item
                        :label="$application['name']"
                        :app-id="$id"
                    >
                        <x-minios.icon
                            :name="$application['icon']"
                        />
                    </x-minios.dock-item>
                </div>
            @endforeach
        </nav>
    </aside>
</div>
