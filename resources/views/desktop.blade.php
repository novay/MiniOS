@php
    $currentWallpaper = os_setting()->get('appearance.wallpaper', 'wall-1');
    $wallpaperFile = str_ends_with($currentWallpaper, '.webp') ? $currentWallpaper : $currentWallpaper.'.webp';
@endphp

<div
    x-data="minios(@js(config('desktop.applications')), @js(os_setting()->all()))"

    @keydown.escape.window="closeAll()"

    @open-app.window="openApplication($event.detail?.id || $event.detail)"

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

        @contextmenu.prevent="
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
                @dblclick="navigate('/files/home')"
            >

                <x-minios.icon name="home" class="h-full w-full" />
            </x-minios.shortcut>


            <x-minios.shortcut
                label="{{ __('Trash') }}"
                selected="selectedShortcut === 'trash'"
                @click.stop="selectedShortcut = 'trash'"
                @dblclick="openWindow('files', { url: '/files/trash' })"
            >

                <x-minios.icon
                    name="trash"
                    class="h-full w-full"
                />

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
                            <livewire:dynamic-component :is="$livewireComp" :wire:key="'minios-app-'.$id" />
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
    {{-- TOAST NOTIFICATION HUB --}}
    {{-- ========================================================= --}}
    <x-minios.toast-hub />


    {{-- ========================================================= --}}
    {{-- CONTEXT MENU --}}
    {{-- ========================================================= --}}
    <x-minios.context-menu />
</div>