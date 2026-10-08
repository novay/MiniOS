<div
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
            'bottom-0 left-0 right-0 flex-row px-3 items-center justify-center': (settings?.dock?.position ?? 'bottom') === 'bottom',
            'bottom-0 left-0 top-7 flex-col py-1.5 items-center justify-start': (settings?.dock?.position ?? 'bottom') === 'left',
            'bottom-0 right-0 top-7 flex-col py-1.5 items-center justify-start': (settings?.dock?.position ?? 'bottom') === 'right',
            'opacity-0 pointer-events-none': applicationsOpen,
        }"
        :style="(settings?.dock?.position ?? 'bottom') === 'bottom'
            ? 'height: ' + getDockPx() + 'px'
            : 'width: ' + getDockPx() + 'px'"
        class="desktop-dock absolute z-[9000] flex transform-gpu transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)]"
    >
        {{-- SHOW APPLICATIONS (Start button on bottom dock, or bottom button on side dock) --}}
        <div
            class="flex"
            :class="{
                'order-first border-r border-white/10 pr-1 mr-1': (settings?.dock?.position ?? 'bottom') === 'bottom',
                'order-last mt-auto border-t border-white/10 pt-1': (settings?.dock?.position ?? 'bottom') !== 'bottom',
            }"
        >
            <x-minios.dock-item
                label="Show Applications"
                @click.stop="toggleApplications()"
            >
                <x-minios.icon
                    name="apps"
                    class="size-7 text-white"
                />
            </x-minios.dock-item>
        </div>

        {{-- DOCK APPLICATIONS (PINNED & RUNNING) --}}
        <div
            class="flex gap-1"
            :class="{
                'flex-row items-center': (settings?.dock?.position ?? 'bottom') === 'bottom',
                'flex-col items-center': (settings?.dock?.position ?? 'bottom') !== 'bottom',
            }"
        >
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
                        {{-- Running Indicator --}}
                        <span
                            x-cloak
                            x-show="(settings?.dock?.show_indicators ?? true) && isWindowRunning(@js($id))"
                            class="absolute transition-all"
                            :class="{
                                '-bottom-1 left-1/2 -translate-x-1/2 h-1 w-4 rounded-t-full': (settings?.dock?.position ?? 'bottom') === 'bottom',
                                '-left-1 top-1/2 -translate-y-1/2 w-1 h-4 rounded-r-full': (settings?.dock?.position ?? 'bottom') === 'left',
                                '-right-1 top-1/2 -translate-y-1/2 w-1 h-4 rounded-l-full': (settings?.dock?.position ?? 'bottom') === 'right',
                            }"
                            :style="'background-color: var(--accent-color, #6366f1)'"
                        ></span>

                        <x-minios.icon
                            :name="$application['icon']"
                            class="size-10"
                        />
                    </x-minios.dock-item>
                </div>
            @endforeach
        </div>
    </aside>
</div>
