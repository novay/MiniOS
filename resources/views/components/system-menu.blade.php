<div
    x-cloak
    x-show="systemMenuOpen"
    @click.outside="systemMenuOpen = false"
    @click.stop
    style="display: none;"
    x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-75"
    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
    x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
    class="
        absolute
        right-2
        top-8
        z-[70]
        w-60
        origin-top-right
        overflow-hidden
        rounded-md
        border
        border-black/10
        dark:border-white/10
        bg-white/90
        dark:bg-[#1e1e1e]/90
        p-1.5
        text-[13px]
        font-medium
        text-neutral-800
        dark:text-neutral-200
        shadow-2xl
        backdrop-blur-2xl
        select-none
    "
>
    {{-- Profile Info Header --}}
    {{-- <div class="flex items-center gap-2.5 rounded border border-black/5 dark:border-white/5 bg-black/[0.03] dark:bg-white/[0.04] p-2">
        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 font-bold text-xs text-white shadow-sm">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-[12px] font-semibold truncate text-neutral-800 dark:text-neutral-200 leading-tight">
                {{ auth()->user()->name ?? 'MiniOS User' }}
            </div>
            <div class="text-[10px] text-neutral-400 dark:text-neutral-500 truncate leading-tight">
                {{ auth()->user()->email ?? 'user@minios.local' }}
            </div>
        </div>
    </div>

    <div class="my-1 h-px bg-neutral-200/80 dark:bg-white/10"></div> --}}

    {{-- System App Shortcuts --}}
    <div class="flex flex-col">
        <button
            type="button"
            @click="systemMenuOpen = false; toggleFullscreen()"
            class="group flex w-full items-center justify-between rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
        >
            <div class="flex items-center gap-2.5">
                <flux:icon name="arrows-pointing-out" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
                <span x-text="isFullscreen ? '{{ __('Exit Fullscreen') }}' : '{{ __('Fullscreen Mode') }}'"></span>
            </div>
            <span class="text-[10px] font-mono opacity-60 group-hover:opacity-100 group-hover:text-white">F11</span>
        </button>

        <button
            type="button"
            @click="systemMenuOpen = false; openApplication('settings')"
            class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
        >
            <flux:icon name="cog-6-tooth" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
            <span>{{ __('System Settings') }}</span>
        </button>

        <button
            type="button"
            @click="systemMenuOpen = false; navigate(desktopPath('/files/home'))"
            class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
        >
            <flux:icon name="folder" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
            <span>{{ __('My Files') }}</span>
        </button>

        <button
            type="button"
            @click="systemMenuOpen = false; openApplication('terminal')"
            class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
        >
            <flux:icon name="command-line" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
            <span>{{ __('Terminal') }}</span>
        </button>
    </div>

    <div class="my-1 h-px bg-neutral-200/80 dark:bg-white/10"></div>

    {{-- Session Actions --}}
    <div class="flex flex-col">
        <button
            type="button"
            @click="systemMenuOpen = false; closeAll(); window.location.href = '{{ route('lock') }}'"
            class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left transition-colors hover:bg-indigo-600 hover:text-white dark:hover:bg-indigo-500"
        >
            <flux:icon name="lock-closed" class="size-4 text-neutral-500 dark:text-neutral-400 group-hover:text-white" />
            <span>{{ __('Lock Screen') }}</span>
        </button>

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button
                type="submit"
                class="group flex w-full items-center gap-2.5 rounded px-2.5 py-1.5 text-left text-rose-600 dark:text-rose-400 transition-colors hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white"
            >
                <flux:icon name="arrow-right-start-on-rectangle" class="size-4 text-rose-500 dark:text-rose-400 group-hover:text-white" />
                <span>{{ __('Log out...') }}</span>
            </button>
        </form>
    </div>
</div>
