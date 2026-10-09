@php
    $currentWallpaper = os_setting()->get('appearance.wallpaper', 'wall-1');
    $wallpaperFile = str_ends_with($currentWallpaper, '.webp') ? $currentWallpaper : $currentWallpaper.'.webp';
@endphp

<div
    x-data="{
        lockTime: '',
        lockDate: '',
        updateLockClock() {
            const now = new Date();
            const loc = '{{ app()->getLocale() === "en" ? "en-US" : "id-ID" }}';
            this.lockTime = new Intl.DateTimeFormat(loc, { hour: '2-digit', minute: '2-digit', hour12: false }).format(now);
            this.lockDate = new Intl.DateTimeFormat(loc, { weekday: 'long', day: 'numeric', month: 'long' }).format(now);
        }
    }"
    x-init="
        updateLockClock();
        setInterval(() => updateLockClock(), 1000);
        $nextTick(() => $refs.lockPasswordInput?.focus());
    "
    style="background-image: url('{{ asset('minios/wallpapers/'.$wallpaperFile) }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
    class="fixed inset-0 z-[99999] flex flex-col items-center justify-between overflow-hidden p-8 text-white select-none"
>
    {{-- Glassmorphism Blur Overlay --}}
    <div class="absolute inset-0 bg-neutral-950/60 backdrop-blur-2xl"></div>

    {{-- Main Content Layer --}}
    <div class="relative z-10 flex h-full w-full flex-col items-center justify-between">
        {{-- Top Section: Date & Time --}}
        <div class="mt-12 flex flex-col items-center text-center">
            <div class="text-6xl font-medium tracking-tight sm:text-8xl" x-text="lockTime"></div>
            <div class="mt-2 text-base font-medium text-white/70 sm:text-lg" x-text="lockDate"></div>
        </div>

        {{-- Center Section: User Card & Password Form --}}
        <div class="my-auto flex w-full max-w-sm flex-col items-center text-center">
            {{-- User Avatar --}}
            <div class="relative flex size-24 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 shadow-2xl ring-4 ring-white/10">
                <span class="text-3xl font-bold uppercase tracking-wider text-white">
                    {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                </span>
            </div>

            {{-- User Name --}}
            <h2 class="mt-4 text-xl font-bold tracking-tight text-white sm:text-2xl">
                {{ auth()->user()->name ?? 'MiniOS User' }}
            </h2>
            <p class="text-xs text-white/60">
                {{ auth()->user()->email ?? 'user@minios.local' }}
            </p>

            {{-- Unlock Form --}}
            <form wire:submit="unlock" class="mt-6 w-full space-y-3">
                <div class="relative">
                    <input
                        x-ref="lockPasswordInput"
                        wire:model="password"
                        type="password"
                        placeholder="{{ __('Enter password...') }}"
                        class="w-full rounded-xl border border-white/20 bg-white/10 px-4 py-3 text-sm text-white placeholder-white/40 shadow-inner backdrop-blur-md outline-none transition-all focus:border-indigo-400 focus:bg-white/15 focus:ring-2 focus:ring-indigo-400/40"
                        autofocus
                    />

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="absolute right-2 top-1/2 flex size-8 -translate-y-1/2 items-center justify-center rounded-lg bg-indigo-600 text-white transition-all hover:bg-indigo-500 active:scale-95 disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="unlock">
                            <flux:icon name="arrow-right" class="size-4" />
                        </span>
                        <span wire:loading wire:target="unlock">
                            <flux:icon name="arrow-path" class="size-4 animate-spin" />
                        </span>
                    </button>
                </div>

                @error('password')
                    <div class="text-xs text-rose-400 font-medium">
                        {{ $message }}
                    </div>
                @enderror
            </form>
        </div>

        {{-- Bottom Section: Actions --}}
        {{-- Bottom Section: Centered Circle Logout Button --}}
        <div class="mb-6 flex flex-col items-center justify-center">
            <button
                type="button"
                wire:click="logout"
                wire:loading.attr="disabled"
                class="group flex flex-col items-center justify-center gap-2 focus:outline-none"
            >
                <div class="flex size-12 items-center justify-center rounded-full border border-white/20 bg-white/10 text-rose-400 shadow-lg backdrop-blur-md transition-all duration-200 group-hover:scale-110 group-hover:border-rose-400/50 group-hover:bg-rose-500/20 group-active:scale-95">
                    <flux:icon name="power" class="size-6 text-rose-400 transition-colors group-hover:text-rose-300" />
                </div>
                <span class="text-xs font-medium tracking-wider text-white/70 transition-colors group-hover:text-white">
                    {{ __('Logout') }}
                </span>
            </button>
        </div>
    </div>
</div>
