<div
    x-cloak
    x-show="contextMenu.open"
    @click.outside="closeContextMenu()"
    @click.stop
    style="display: none;"
    :style="`
        left: ${contextMenu.x}px;
        top: ${contextMenu.y}px;
        display: ${contextMenu.open ? 'block' : 'none'};
    `"
    class="fixed z-[9999] w-[210px] origin-top-left overflow-hidden rounded-md border border-black/10 dark:border-white/10 bg-white/90 dark:bg-[#1e1e1e]/90 p-1.5 text-[13px] font-medium text-neutral-800 dark:text-neutral-200 shadow-2xl backdrop-blur-2xl select-none"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-95"
    x-transition:enter-end="opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 scale-100"
    x-transition:leave-end="opacity-0 scale-95"
>
    {{-- ========================================================= --}}
    {{-- DOCK CONTEXT MENU --}}
    {{-- ========================================================= --}}
    <template x-if="contextMenu.type === 'dock'">
        <div class="flex flex-col">
            {{-- App Title Header --}}
            <div class="flex items-center justify-between px-2 py-1 text-[11px] font-semibold text-neutral-400 dark:text-neutral-500">
                <span class="truncate" x-text="applications[contextMenu.appId]?.name || contextMenu.appId"></span>
                <span
                    x-show="isWindowRunning(contextMenu.appId)"
                    class="size-1.5 rounded-full bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.8)]"
                    title="Active"
                ></span>
            </div>

            {{-- 1. JIKA APP OPEN / RUNNING / ACTIVE --}}
            <template x-if="isWindowRunning(contextMenu.appId)">
                <div>
                    {{-- Remove from Dock (atau Pin to Dock jika belum di-pin) --}}
                    <button
                        type="button"
                        x-show="isAppPinned(contextMenu.appId)"
                        @click="unpinApp(contextMenu.appId); closeContextMenu()"
                        class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
                    >
                        <flux:icon name="bookmark-slash" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                        <span>{{ __('Remove from Dock') }}</span>
                    </button>

                    <button
                        type="button"
                        x-show="!isAppPinned(contextMenu.appId)"
                        @click="pinApp(contextMenu.appId); closeContextMenu()"
                        class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
                    >
                        <flux:icon name="bookmark" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                        <span>{{ __('Pin to Dock') }}</span>
                    </button>

                    <div class="my-1 h-px bg-neutral-200/80 dark:bg-white/10"></div>

                    {{-- Show --}}
                    <button
                        type="button"
                        @click="openApplication(contextMenu.appId); closeContextMenu()"
                        class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
                    >
                        <flux:icon name="eye" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                        <span>{{ __('Show') }}</span>
                    </button>

                    {{-- Hide --}}
                    <button
                        type="button"
                        @click="minimizeWindow(contextMenu.appId); closeContextMenu()"
                        class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
                    >
                        <flux:icon name="eye-slash" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                        <span>{{ __('Hide') }}</span>
                    </button>

                    {{-- Quit --}}
                    <button
                        type="button"
                        @click="closeWindow(contextMenu.appId); closeContextMenu()"
                        class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left text-rose-600 dark:text-rose-400 transition-colors hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white"
                    >
                        <flux:icon name="power" class="size-4 text-rose-500 dark:text-rose-400 group-hover:text-white" />
                        <span>{{ __('Quit') }}</span>
                    </button>
                </div>
            </template>

            {{-- 2. JIKA APP TIDAK SEDANG RUNNING (HANYA PINNED) --}}
            <template x-if="!isWindowRunning(contextMenu.appId)">
                <div>
                    {{-- Open --}}
                    <button
                        type="button"
                        @click="openApplication(contextMenu.appId); closeContextMenu()"
                        class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
                    >
                        <flux:icon name="arrow-top-right-on-square" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                        <span>{{ __('Open') }}</span>
                    </button>

                    <div class="my-1 h-px bg-neutral-200/80 dark:bg-white/10"></div>

                    {{-- Remove from Dock --}}
                    <button
                        type="button"
                        @click="unpinApp(contextMenu.appId); closeContextMenu()"
                        class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left text-rose-600 dark:text-rose-400 transition-colors hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white"
                    >
                        <flux:icon name="bookmark-slash" class="size-4 text-rose-500 dark:text-rose-400 group-hover:text-white" />
                        <span>{{ __('Remove from Dock') }}</span>
                    </button>
                </div>
            </template>
        </div>
    </template>

    {{-- ========================================================= --}}
    {{-- LAUNCHER CONTEXT MENU --}}
    {{-- ========================================================= --}}
    <template x-if="contextMenu.type === 'launcher'">
        <div class="flex flex-col">
            {{-- App Title Header --}}
            <div class="flex items-center justify-between px-2.5 py-1 text-[11px] font-semibold text-neutral-400 dark:text-neutral-500 border-b border-black/5 dark:border-white/5 mb-1">
                <span class="truncate" x-text="applications[contextMenu.appId]?.name || contextMenu.appId"></span>
                <span
                    x-show="isWindowRunning(contextMenu.appId)"
                    class="size-1.5 rounded-full bg-emerald-500 shadow-[0_0_6px_rgba(16,185,129,0.8)]"
                    title="Active"
                ></span>
            </div>

            {{-- Pin to Dock / Remove from Dock --}}
            <button
                type="button"
                x-show="!isAppPinned(contextMenu.appId)"
                @click="pinApp(contextMenu.appId); closeContextMenu()"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
            >
                <flux:icon name="bookmark" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span>{{ __('Pin to Dock') }}</span>
            </button>

            <button
                type="button"
                x-show="isAppPinned(contextMenu.appId)"
                @click="unpinApp(contextMenu.appId); closeContextMenu()"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
            >
                <flux:icon name="bookmark-slash" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span>{{ __('Remove from Dock') }}</span>
            </button>

            <div class="my-1 h-px bg-neutral-200/80 dark:bg-white/10"></div>

            {{-- Open --}}
            <button
                type="button"
                @click="openApplication(contextMenu.appId); applicationsOpen = false; closeContextMenu()"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
            >
                <flux:icon name="arrow-top-right-on-square" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span>{{ __('Open') }}</span>
            </button>
        </div>
    </template>

    {{-- ========================================================= --}}
    {{-- DESKTOP CONTEXT MENU (DEFAULT) --}}
    {{-- ========================================================= --}}
    <template x-if="!contextMenu.type || contextMenu.type === 'desktop'">
        <div class="flex flex-col">
            
            {{-- Ubah Wallpaper --}}
            <button
                type="button"
                @click="closeContextMenu(); openApplication('settings')"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
            >
                <flux:icon name="photo" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span>{{ __('Change Wallpaper...') }}</span>
            </button>

            {{-- Pengaturan Sistem --}}
            <button
                type="button"
                @click="closeContextMenu(); openApplication('settings')"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
            >
                <flux:icon name="cog-6-tooth" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span>{{ __('System Settings...') }}</span>
            </button>

            <div class="my-1 h-px bg-neutral-200/80 dark:bg-white/10"></div>

            {{-- Muat Ulang / Refresh --}}
            <button
                type="button"
                @click="closeContextMenu(); window.location.reload()"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
            >
                <flux:icon name="arrow-path" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span>{{ __('Refresh') }}</span>
            </button>

            <div class="my-1 h-px bg-neutral-200/80 dark:bg-white/10"></div>

            {{-- Terminal --}}
            <button
                type="button"
                @click="closeContextMenu(); openApplication('terminal')"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
            >
                <flux:icon name="command-line" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span>{{ __('Open Terminal') }}</span>
            </button>

            {{-- Manajer Berkas --}}
            <button
                type="button"
                @click="closeContextMenu(); openApplication('files')"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
            >
                <flux:icon name="folder" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span>{{ __('File Manager') }}</span>
            </button>

            <div class="my-1 h-px bg-neutral-200/80 dark:bg-white/10"></div>

            {{-- Tentang MiniOS --}}
            <button
                type="button"
                @click="closeContextMenu(); openApplication('about')"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
            >
                <flux:icon name="information-circle" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span>{{ __('About MiniOS') }}</span>
            </button>
        </div>
    </template>
</div>