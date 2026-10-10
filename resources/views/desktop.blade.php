@php
    $currentWallpaper = os_setting()->get('appearance.wallpaper', 'wall-1');
    $wallpaperFile = str_ends_with($currentWallpaper, '.webp') ? $currentWallpaper : $currentWallpaper.'.webp';
@endphp

<div
    x-data="minios(@js(config('desktop.applications')), @js(os_setting()->all()), @js(os_path()))"

    @contextmenu.prevent="openContextMenu($event)"

    @keydown.escape.window="closeAll()"

    @open-app.window="
        const targetId = $event.detail?.id || $event.detail?.app || (typeof $event.detail === 'string' ? $event.detail : null);
        if (targetId) {
            openApplication(targetId, { path: $event.detail?.path });
            if ($event.detail?.path) {
                $dispatch('open-file', $event.detail);
            }
        }
    "

    @close-window.window="
        const closeId = $event.detail?.id || $event.detail?.app || (typeof $event.detail === 'string' ? $event.detail : null);
        if (closeId) {
            closeWindow(closeId);
        }
    "

    @update-window-url.window="
        const appId = $event.detail?.id || $event.detail?.app;
        const newUrl = $event.detail?.url;
        if (appId && newUrl) {
            const win = getWindow(appId);
            if (win) {
                win.url = newUrl;
            }
            if (activeWindow === appId) {
                window.history.pushState({}, '', newUrl);
                currentPath = normalizePath(newUrl);
                currentUrl = newUrl;
            }
        }
    "

    @popstate.window="
        $dispatch('desktop-route-changed', { path: window.location.pathname, url: window.location.href });
    "

    @trash-updated.window="
        const c = $event.detail?.count ?? $event.detail?.[0]?.count ?? (typeof $event.detail === 'number' ? $event.detail : null);
        if (c !== null && c !== undefined) {
            $wire.set('trashCount', Number(c), false);
        }
    "

    @click="closeContextMenu(); selectedShortcut = null"

    style="background-image: url('{{ asset('minios/wallpapers/'.$wallpaperFile) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"

    class="
        desktop-wallpaper

        relative

        h-dvh
        w-screen

        overflow-hidden

        text-white
    "
>

    {{-- ========================================================= --}}
    {{-- TOP BAR --}}
    {{-- ========================================================= --}}

    <x-minios.topbar />


    {{-- ========================================================= --}}
    {{-- DESKTOP WORKSPACE --}}
    {{-- ========================================================= --}}

    <main
        x-ref="workspace"

        @contextmenu.prevent.stop="
            openContextMenu($event)
        "

        x-bind:style="getWorkspaceStyles()"

        class="
            absolute
            z-0

            overflow-hidden

            transition-all
            duration-300

            ease-[cubic-bezier(0.22,1,0.36,1)]
        "
    >

        {{-- Desktop Icons --}}
        <div
            class="
                absolute
                right-5
                top-5

                flex
                flex-col
                items-center
                gap-2
            "
        >

            <x-minios.shortcut
                label="{{ __('Home') }}"
                selected="selectedShortcut === 'home'"
                @click.stop="selectedShortcut = 'home'"
                @dblclick="navigate(desktopPath('/files/home'))"
            >

                <x-minios.icon name="home" class="h-full w-full" />
            </x-minios.shortcut>


            <x-minios.shortcut
                label="{{ __('Trash') }}"
                selected="selectedShortcut === 'trash'"
                @click.stop="selectedShortcut = 'trash'"
                @dblclick="openWindow('files', { url: '/files/.trash' }); $dispatch('open-folder', { path: '.trash' })"
                @contextmenu.prevent.stop="openTrashContextMenu($event)"
            >
                <div class="relative flex items-center justify-center h-full w-full">
                    {{-- Icon Saat Kosong (Empty) --}}
                    <div x-show="!$wire.trashCount || $wire.trashCount === 0" class="flex items-center justify-center h-full w-full">
                        <x-minios.icon
                            name="trash-empty"
                            class="h-full w-full"
                        />
                    </div>

                    {{-- Icon Saat Terisi Sampah (Full) --}}
                    <div x-show="$wire.trashCount > 0" class="flex items-center justify-center h-full w-full" style="display: none;">
                        <x-minios.icon
                            name="trash-full"
                            class="h-full w-full"
                        />
                    </div>

                    <span
                        x-show="$wire.trashCount > 0"
                        class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-bold text-white shadow-md ring-2 ring-white/20"
                        x-text="$wire.trashCount"
                    ></span>
                </div>
            </x-minios.shortcut>

        </div>

        {{-- ========================================================= --}}
        {{-- WINDOW LAYER --}}
        {{-- ========================================================= --}}

        <div class="pointer-events-none absolute inset-0">

            @foreach (
                config('desktop.applications')
                as $id => $application
            )

                <x-minios.window
                    :id="$id"
                    :title="$application['name']"
                    :icon="$application['icon']"
                >

                    @if (! empty($application['missing_packages']))
                        <x-minios.missing-dependencies :app="$application" :missing="$application['missing_packages']" :id="$id" />
                    @elseif (! empty($application['component']))
                        @if ((class_exists($application['component']) && is_subclass_of($application['component'], \Livewire\Component::class)) || str_starts_with($application['component'], 'livewire:') || str_starts_with($application['component'], 'pages::'))
                            @php
                                $livewireComp = str_starts_with($application['component'], 'livewire:')
                                    ? substr($application['component'], 9)
                                    : $application['component'];
                            @endphp
                            @if ($id === 'katalog')
                                <livewire:dynamic-component :is="$livewireComp" :wire:key="'minios-app-'.$id" :path="$desktopPath" />
                            @else
                                <livewire:dynamic-component :is="$livewireComp" :wire:key="'minios-app-'.$id" />
                            @endif
                        @else
                            <x-dynamic-component :component="$application['component']" />
                        @endif
                    @else
                        <div
                            class="
                                flex
                                h-full
                                min-h-72

                                flex-col
                                items-center
                                justify-center

                                gap-5

                                bg-[#f6f5f4]

                                p-10

                                text-center
                            "
                        >

                            <x-minios.icon
                                :name="$application['icon']"
                                class="size-20"
                            />


                            <div>

                                <h2
                                    class="
                                        text-xl
                                        font-semibold
                                        text-neutral-800
                                    "
                                >
                                    {{ $application['name'] }}
                                </h2>


                                <p
                                    class="
                                        mt-1

                                        text-sm
                                        text-neutral-500
                                    "
                                >
                                    {{ __('Application Host placeholder.') }}
                                </p>

                            </div>


                            <div
                                class="
                                    rounded-lg

                                    border
                                    border-neutral-200

                                    bg-white

                                    px-4
                                    py-2

                                    font-mono
                                    text-xs
                                    text-neutral-500

                                    shadow-sm
                                "
                            >
                                <span
                                    x-text="
                                        getWindow(@js($id))?.url
                                        ?? @js($application['entry'])
                                    "
                                ></span>
                            </div>

                        </div>
                    @endif
                </x-minios.window>

            @endforeach

        </div>

        {{-- Watermark --}}
        <div
            class="
                pointer-events-none

                absolute
                bottom-5
                right-6

                text-right
                text-white/40
            "
        >

            <div
                class="
                    text-[11px]
                    uppercase
                    tracking-[0.18em]
                "
            >
                {{ __('Web Desktop') }}
            </div>

            <div class="mt-0.5 text-[10px]">
                {{ __('MiniOS Project') }}
            </div>

        </div>

    </main>


    {{-- ========================================================= --}}
    {{-- APPLICATIONS --}}
    {{-- ========================================================= --}}
    <x-minios.applications />


    {{-- ========================================================= --}}
    {{-- SOUNDCLOUD MINI PLAYER --}}
    {{-- ========================================================= --}}
    @include('minios::apps.soundcloud.mini')

    {{-- ========================================================= --}}
    {{-- DOCK --}}
    {{-- ========================================================= --}}
    <x-minios.dock />


    {{-- ========================================================= --}}
    {{-- SYSTEM MENU --}}
    {{-- ========================================================= --}}
    <x-minios.system-menu />


    {{-- ========================================================= --}}
    {{-- NOTIFICATION CENTER --}}
    {{-- ========================================================= --}}
    <x-minios.notification-center />


    {{-- ========================================================= --}}
    {{-- VOLUME HUD OVERLAY --}}
    {{-- ========================================================= --}}
    <div
        x-cloak
        x-show="volumeHudVisible"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-90 -translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        class="fixed top-12 left-1/2 -translate-x-1/2 z-[9999] flex items-center gap-3.5 px-4 py-2.5 rounded-2xl bg-neutral-900/85 dark:bg-neutral-800/90 backdrop-blur-2xl border border-white/20 text-white shadow-2xl pointer-events-none select-none min-w-[220px]"
    >
        <div class="shrink-0 flex items-center justify-center">
            <template x-if="globalMuted || globalVolume === 0">
                <flux:icon name="speaker-x-mark" class="size-5 text-rose-400" />
            </template>
            <template x-if="!globalMuted && globalVolume > 0">
                <flux:icon name="speaker-wave" class="size-5 text-white" />
            </template>
        </div>

        <div class="flex-1 flex flex-col gap-1">
            <div class="flex items-center justify-between text-[11px] font-medium text-white/80">
                <span>{{ __('Sound Volume') }}</span>
                <span class="font-mono text-[10px]" x-text="(globalMuted ? 0 : Math.round(globalVolume * 100)) + '%'"></span>
            </div>
            <div class="h-1.5 w-full bg-white/20 rounded-full overflow-hidden">
                <div
                    class="h-full bg-white rounded-full transition-all duration-100 ease-out"
                    :style="'width: ' + (globalMuted ? 0 : Math.round(globalVolume * 100)) + '%'"
                ></div>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    @persist('toast')
        <x-minios.toast.group>
            <x-minios.toast />
        </x-minios.toast.group>
    @endpersist



    {{-- ========================================================= --}}
    {{-- CONTEXT MENU --}}
    {{-- ========================================================= --}}
    <x-minios.context-menu />
</div>