@props([
    'id',
    'title',
    'icon',
])

<div
    wire:key="desktop-window-{{ $id }}"
    x-cloak

    data-window-id="{{ $id }}"

    x-show="
        isWindowVisible(
            @js($id)
        )
    "

    @pointerdown="
        focusWindow(
            @js($id),
            {
                syncUrl: true
            }
        )
    "

    :style="
        windowStyle(
            @js($id)
        )
    "

    :class="[
        getWindow(@js($id))?.maximized
            ? 'rounded-none'
            : 'rounded-xl',

        isWindowInteracting(@js($id))
            ? 'desktop-window-no-motion'
            : 'desktop-window-motion',
    ]"

    class="
        pointer-events-auto
        absolute
        flex
        flex-col
        overflow-hidden
        {{-- border
        border-zinc-800/90
        dark:border-[#333333] --}}
        bg-zinc-50
        dark:bg-[#161616]
        text-zinc-900
        dark:text-zinc-100
        shadow-2xl
    "
>

    {{-- ========================================================= --}}
    {{-- TITLE BAR --}}
    {{-- ========================================================= --}}

    <header
        @pointerdown.stop="
            startDrag(
                $event,
                @js($id)
            )
        "

        @dblclick="
            if (isWindowMaximizable(@js($id))) {
                toggleMaximizeWindow(
                    @js($id)
                )
            }
        "

        :class="
            isWindowFocused(@js($id))
                ? 'bg-[#3b3539] text-white'
                : 'bg-[#2a2729] text-white/70'
        "

        class="
            flex
            h-9
            shrink-0
            touch-none
            items-center

            border-b
            border-black/30

            px-3
        "
    >

        {{-- Icon --}}
        <div
            class="
                flex
                items-center
                gap-2
            "
        >

            <x-minios.icon
                :name="$icon"
                class="size-5"
            />

        </div>


        {{-- Title --}}
        <div
            class="
                pointer-events-none

                absolute
                left-1/2

                max-w-[50%]

                -translate-x-1/2

                truncate

                text-[13px]
                font-semibold
            "
        >
            {{ $title }}
        </div>


        {{-- ========================================================= --}}
        {{-- WINDOW CONTROLS --}}
        {{-- ========================================================= --}}

        <div
            class="
                ml-auto

                flex
                h-full
                items-center

                gap-1
            "
        >

            {{-- Minimize --}}
            <button
                type="button"

                title="Minimize"

                @pointerdown.stop

                @click.stop="
                    minimizeWindow(
                        @js($id)
                    )
                "

                class="
                    flex
                    size-6
                    items-center
                    justify-center

                    rounded-full

                    text-white/90

                    transition-colors
                    duration-150

                    hover:bg-white/10
                "
            >
                <img src="https://vivek9patel.github.io/themes/Yaru/window/window-minimize-symbolic.svg" alt="window minimize" class="h-6 w-6 inline">
            </button>


            {{-- Maximize / Restore --}}
            <button
                type="button"

                :disabled="!isWindowMaximizable(@js($id))"

                :title="
                    !isWindowMaximizable(@js($id))
                        ? ''
                        : (getWindow(@js($id))?.maximized ? 'Restore' : 'Maximize')
                "

                @pointerdown.stop

                @click.stop="
                    if (isWindowMaximizable(@js($id))) {
                        toggleMaximizeWindow(
                            @js($id)
                        )
                    }
                "

                :class="
                    !isWindowMaximizable(@js($id))
                        ? 'opacity-30 cursor-not-allowed pointer-events-none'
                        : 'hover:bg-white/10'
                "

                class="
                    flex
                    size-6
                    items-center
                    justify-center

                    rounded-full

                    text-white/90

                    transition-colors
                    duration-150
                "
            >

                {{-- Maximize icon --}}
                <img 
                    x-show="
                        !getWindow(@js($id))?.maximized
                    "
                    src="https://vivek9patel.github.io/themes/Yaru/window/window-maximize-symbolic.svg" 
                    alt="window maximize" 
                    class="h-5 w-5 inline"
                >


                {{-- Restore icon --}}
                <img 
                    x-cloak

                    x-show="
                        getWindow(@js($id))?.maximized
                    "
                    src="https://vivek9patel.github.io/themes/Yaru/window/window-restore-symbolic.svg" 
                    alt="window restore" 
                    class="h-6 w-6 inline"
                >
            </button>


            {{-- Close --}}
            <button 
                type="button"

                title="Close"

                @pointerdown.stop

                @click.stop="
                    closeWindow(
                        @js($id)
                    )
                "

                class="
                    ml-1

                    flex
                    size-6
                    items-center
                    justify-center

                    rounded-full

                    bg-[#e95420]

                    text-white

                    shadow-sm

                    transition-all
                    duration-150

                    hover:bg-[#f46a36]
                    hover:scale-105

                    active:scale-95
                "
            >
                <img src="https://vivek9patel.github.io/themes/Yaru/window/window-close-symbolic.svg" 
                    alt="window close" 
                    class="h-4 w-4 inline">
            </button>

        </div>

    </header>


    {{-- ========================================================= --}}
    {{-- CONTENT --}}
    {{-- ========================================================= --}}

    <div
        class="
            min-h-0
            flex-1
            h-full

            overflow-auto
            bg-zinc-50
            dark:bg-[#161616]
        "
    >
        {{ $slot }}
    </div>


    {{-- ========================================================= --}}
    {{-- RESIZE HANDLES --}}
    {{-- ========================================================= --}}

    <template
        x-if="
            !getWindow(@js($id))?.maximized && isWindowResizable(@js($id))
        "
    >

        <div>

            {{-- North --}}
            <div
                @pointerdown.stop.prevent="
                    startResize(
                        $event,
                        @js($id),
                        'n'
                    )
                "

                class="
                    absolute
                    -top-1
                    left-3
                    right-3

                    z-20

                    h-2

                    cursor-n-resize
                "
            ></div>


            {{-- South --}}
            <div
                @pointerdown.stop.prevent="
                    startResize(
                        $event,
                        @js($id),
                        's'
                    )
                "

                class="
                    absolute
                    -bottom-1
                    left-3
                    right-3

                    z-20

                    h-2

                    cursor-s-resize
                "
            ></div>


            {{-- West --}}
            <div
                @pointerdown.stop.prevent="
                    startResize(
                        $event,
                        @js($id),
                        'w'
                    )
                "

                class="
                    absolute
                    -left-1
                    bottom-3
                    top-3

                    z-20

                    w-2

                    cursor-w-resize
                "
            ></div>


            {{-- East --}}
            <div
                @pointerdown.stop.prevent="
                    startResize(
                        $event,
                        @js($id),
                        'e'
                    )
                "

                class="
                    absolute
                    -right-1
                    bottom-3
                    top-3

                    z-20

                    w-2

                    cursor-e-resize
                "
            ></div>


            {{-- NW --}}
            <div
                @pointerdown.stop.prevent="
                    startResize(
                        $event,
                        @js($id),
                        'nw'
                    )
                "

                class="
                    absolute
                    -left-1
                    -top-1

                    z-30

                    size-4

                    cursor-nw-resize
                "
            ></div>


            {{-- NE --}}
            <div
                @pointerdown.stop.prevent="
                    startResize(
                        $event,
                        @js($id),
                        'ne'
                    )
                "

                class="
                    absolute
                    -right-1
                    -top-1

                    z-30

                    size-4

                    cursor-ne-resize
                "
            ></div>


            {{-- SW --}}
            <div
                @pointerdown.stop.prevent="
                    startResize(
                        $event,
                        @js($id),
                        'sw'
                    )
                "

                class="
                    absolute
                    -bottom-1
                    -left-1

                    z-30

                    size-4

                    cursor-sw-resize
                "
            ></div>


            {{-- SE --}}
            <div
                @pointerdown.stop.prevent="
                    startResize(
                        $event,
                        @js($id),
                        'se'
                    )
                "

                class="
                    absolute
                    -bottom-1
                    -right-1

                    z-30

                    size-4

                    cursor-se-resize
                "
            ></div>

        </div>

    </template>

</div>
