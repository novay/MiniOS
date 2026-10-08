<div
    x-cloak
    x-show="systemMenuOpen"
    @click.outside="systemMenuOpen = false"
    style="display: none;"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
    x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
    class="
        absolute
        right-2
        top-8
        z-[70]
        w-64
        origin-top-right
        overflow-hidden
        rounded-2xl
        border
        border-black/10
        dark:border-white/15
        bg-white/80
        dark:bg-neutral-900/80
        text-neutral-900
        dark:text-white
        shadow-2xl
        backdrop-blur-2xl
        p-1.5
        space-y-1
        select-none
    "
>
    {{-- Profile Info Header --}}
    <div class="flex items-center gap-2.5 p-2 rounded-xl bg-black/5 dark:bg-white/5 border border-black/10 dark:border-white/10">
        <div class="flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 font-bold text-xs text-white shadow-sm">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="text-[12px] font-semibold truncate text-neutral-900 dark:text-white leading-tight">
                {{ auth()->user()->name ?? 'MiniOS User' }}
            </div>
            <div class="text-[10px] text-neutral-500 dark:text-white/50 truncate leading-tight">
                {{ auth()->user()->email ?? 'user@minios.local' }}
            </div>
        </div>
    </div>

    <div class="h-px bg-black/10 dark:bg-white/10 my-1"></div>

    {{-- System App Shortcuts --}}
    <div class="space-y-0.5">
        <button
            type="button"
            @click="systemMenuOpen = false; toggleFullscreen()"
            class="flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left text-[12px] font-medium text-neutral-800 dark:text-white/90 transition-all hover:bg-indigo-600 hover:text-white group"
        >
            <div class="flex items-center gap-2">
                <flux:icon name="arrows-pointing-out" class="size-3.5 text-neutral-500 dark:text-white/70 group-hover:text-white" />
                <span x-text="isFullscreen ? 'Keluar Layar Penuh' : 'Mode Layar Penuh'"></span>
            </div>
            <span class="text-[10px] opacity-60">F11</span>
        </button>

        <button
            type="button"
            @click="systemMenuOpen = false; openApplication('settings')"
            class="flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left text-[12px] font-medium text-neutral-800 dark:text-white/90 transition-all hover:bg-indigo-600 hover:text-white group"
        >
            <div class="flex items-center gap-2">
                <flux:icon name="cog-6-tooth" class="size-3.5 text-neutral-500 dark:text-white/70 group-hover:text-white" />
                <span>Pengaturan Sistem</span>
            </div>
        </button>

        <button
            type="button"
            @click="systemMenuOpen = false; navigate('/files/home')"
            class="flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left text-[12px] font-medium text-neutral-800 dark:text-white/90 transition-all hover:bg-indigo-600 hover:text-white group"
        >
            <div class="flex items-center gap-2">
                <flux:icon name="folder" class="size-3.5 text-neutral-500 dark:text-white/70 group-hover:text-white" />
                <span>Berkas Saya</span>
            </div>
        </button>

        <button
            type="button"
            @click="systemMenuOpen = false; openApplication('terminal')"
            class="flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left text-[12px] font-medium text-neutral-800 dark:text-white/90 transition-all hover:bg-indigo-600 hover:text-white group"
        >
            <div class="flex items-center gap-2">
                <flux:icon name="command-line" class="size-3.5 text-neutral-500 dark:text-white/70 group-hover:text-white" />
                <span>Terminal</span>
            </div>
        </button>
    </div>

    <div class="h-px bg-black/10 dark:bg-white/10 my-1"></div>

    {{-- Session Actions --}}
    <div class="space-y-0.5">
        <button
            type="button"
            @click="systemMenuOpen = false; closeAll(); window.location.href = '{{ route('lock') }}'"
            class="flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left text-[12px] font-medium text-neutral-800 dark:text-white/90 transition-all hover:bg-indigo-600 hover:text-white group"
        >
            <div class="flex items-center gap-2">
                <flux:icon name="lock-closed" class="size-3.5 text-neutral-500 dark:text-white/70 group-hover:text-white" />
                <span>Kunci Layar</span>
            </div>
        </button>

        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button
                type="submit"
                class="flex w-full items-center justify-between px-2.5 py-1.5 rounded-lg text-left text-[12px] font-medium text-rose-600 dark:text-rose-300 transition-all hover:bg-rose-600 hover:text-white group"
            >
                <div class="flex items-center gap-2">
                    <flux:icon name="arrow-right-start-on-rectangle" class="size-3.5 text-rose-500 dark:text-rose-400 group-hover:text-white" />
                    <span>Keluar...</span>
                </div>
            </button>
        </form>
    </div>
</div>
