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
                class="flex order-first"
            >
                <x-minios.dock-item
                    label="MiniOS"
                    @click.stop="toggleApplications()"
                >
                    <x-minios.icon
                        name="minios"
                    />
                </x-minios.dock-item>
            </div>

            {{-- WINDOWS 11 TASKBAR SEPARATOR --}}
            <div
                class="shrink-0 select-none rounded-full transition-all duration-150 order-first"
                :class="{
                    'mx-1 w-px bg-neutral-400/40 dark:bg-white/15 self-center': (settings?.dock?.position ?? 'bottom') === 'bottom',
                    'my-1 h-px bg-neutral-400/40 dark:bg-white/15 self-center': (settings?.dock?.position ?? 'bottom') !== 'bottom',
                    'h-5': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'small',
                    'h-6': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'medium',
                    'h-8': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'large',
                    'w-5': (settings?.dock?.position ?? 'bottom') !== 'bottom' && (settings?.dock?.size ?? 'medium') === 'small',
                    'w-6': (settings?.dock?.position ?? 'bottom') !== 'bottom' && (settings?.dock?.size ?? 'medium') === 'medium',
                    'w-8': (settings?.dock?.position ?? 'bottom') !== 'bottom' && (settings?.dock?.size ?? 'medium') === 'large',
                }"
                aria-hidden="true"
            ></div>

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
