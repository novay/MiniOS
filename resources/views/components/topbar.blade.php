<header
    :class="{ 'opacity-0 pointer-events-none': applicationsOpen }"
    class="desktop-topbar absolute inset-x-0 top-0 z-50 grid h-7 grid-cols-3 items-center px-2 text-[13px] font-medium select-none transition-opacity duration-200"
>
    {{-- ========================================================= --}}
    {{-- LEFT --}}
    {{-- ========================================================= --}}
    <div class="flex items-center">
        <button
            type="button"
            @click.stop="openApplication('about')"
            class="flex items-center gap-1 rounded px-2 py-0.5 font-semibold transition-colors hover:bg-black/10 dark:hover:bg-white/10"
            :class="isWindowFocused('about') ? 'bg-black/10 dark:bg-white/15' : ''"
        >
            <img src="{{ asset('minios/images/logo.png') }}" alt="MiniOS" class="size-5" />
            <span>MiniOS</span>
        </button>
    </div>

    {{-- ========================================================= --}}
    {{-- CENTER / CLOCK --}}
    {{-- ========================================================= --}}
    <div class="flex justify-center">
        <button type="button" class="rounded px-3 py-0.5 font-semibold transition-colors hover:bg-black/10 dark:hover:bg-white/10">
            <span x-text="clock"></span>
        </button>
    </div>

    {{-- ========================================================= --}}
    {{-- SYSTEM TRAY --}}
    {{-- ========================================================= --}}
    <div class="flex items-center justify-end gap-1.5">
        {{-- Light / Dark Mode Toggle --}}
        <button
            type="button"
            @click.stop="toggleTheme()"
            class="flex size-6 items-center justify-center rounded-md text-neutral-700 dark:text-neutral-200 transition-colors hover:bg-black/10 dark:hover:bg-white/10"
            :title="isDarkMode ? '{{ __('Light Mode') }}' : '{{ __('Dark Mode') }}'"
        >
            <span x-cloak x-show="isDarkMode" class="flex items-center justify-center">
                <flux:icon name="sun"  class="size-4 text-amber-400" />
            </span>
            <span x-cloak x-show="!isDarkMode" class="flex items-center justify-center">
                <flux:icon name="moon" variant="solid" class="size-4 text-neutral-800 dark:text-neutral-300" />
            </span>
        </button>

        <button
            type="button"
            @click.stop="toggleSystemMenu()"
            class="flex h-6 items-center gap-2 rounded-md px-2 transition-colors hover:bg-black/10 dark:hover:bg-white/10"
            :class="systemMenuOpen ? 'bg-black/10 dark:bg-white/15' : ''"
        >
            <div class="flex size-4 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-[9px] font-bold uppercase text-white">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <span class="hidden md:flex text-xs font-medium text-neutral-800 dark:text-white/90">{{ auth()->user()->name ?? 'User' }}</span>

            <svg class="size-3 text-neutral-600 dark:text-white/70 transition-transform duration-200" :class="systemMenuOpen ? 'rotate-180' : ''" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.09 1.03l-4.25 4.5a.75.75 0 0 1-1.09 0l-4.25-4.5a.75.75 0 0 1 .02-1.05" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</header>
