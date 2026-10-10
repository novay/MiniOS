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

        {{-- Audio Control Dropdown --}}
        <div class="relative" @click.outside="audioDropdownOpen = false">
            <button
                type="button"
                @click.stop="toggleAudioDropdown()"
                class="flex size-6 items-center justify-center rounded-md text-neutral-700 dark:text-neutral-200 transition-colors hover:bg-black/10 dark:hover:bg-white/10"
                :class="audioDropdownOpen ? 'bg-black/10 dark:bg-white/15' : ''"
                :title="'{{ __('Sound Volume') }}'"
            >
                <span x-cloak x-show="globalMuted || globalVolume === 0" class="flex items-center justify-center">
                    <flux:icon name="speaker-x-mark" class="size-4 text-neutral-400 dark:text-neutral-500" />
                </span>
                <span x-cloak x-show="!globalMuted && globalVolume > 0" class="flex items-center justify-center">
                    <flux:icon name="speaker-wave" class="size-4 text-neutral-800 dark:text-neutral-200" />
                </span>
            </button>

            {{-- Volume Dropdown Popover --}}
            <div
                x-cloak
                x-show="audioDropdownOpen"
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                class="absolute right-0 top-full mt-2 w-64 rounded-lg border border-neutral-200/80 bg-white/95 p-3.5 shadow-xl backdrop-blur-xl dark:border-white/10 dark:bg-neutral-900/95 z-50 select-none text-neutral-800 dark:text-neutral-100"
                @click.stop
            >
                <div class="flex items-center justify-between mb-2.5">
                    <div class="flex items-center gap-1.5 text-xs font-semibold">
                        <span>{{ __('Volume Master') }}</span>
                    </div>
                    <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400" x-text="(globalMuted ? 0 : Math.round(globalVolume * 100)) + '%'"></span>
                </div>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        @click="toggleGlobalMute()"
                        class="flex size-7 shrink-0 items-center justify-center rounded-lg text-neutral-600 hover:bg-neutral-100 hover:text-neutral-900 dark:text-neutral-300 dark:hover:bg-neutral-800 transition"
                        :title="globalMuted ? '{{ __('Unmute') }}' : '{{ __('Mute') }}'"
                    >
                        <template x-if="globalMuted || globalVolume === 0">
                            <flux:icon name="speaker-x-mark" class="size-4 text-rose-500" />
                        </template>
                        <template x-if="!globalMuted && globalVolume > 0">
                            <flux:icon name="speaker-wave" class="size-4" />
                        </template>
                    </button>

                    <input
                        type="range"
                        min="0"
                        max="1"
                        step="0.01"
                        :value="globalMuted ? 0 : globalVolume"
                        @input="setGlobalVolume($event.target.value)"
                        class="w-full h-1.5 bg-neutral-200 dark:bg-neutral-700 rounded-lg appearance-none cursor-pointer accent-primary-500 hover:accent-primary-600"
                    />
                </div>
            </div>
        </div>

        {{-- Notification Center Toggle --}}
        <button
            type="button"
            @click.stop="toggleNotificationCenter()"
            class="relative flex size-6 items-center justify-center rounded-md text-neutral-700 dark:text-neutral-200 transition-colors hover:bg-black/10 dark:hover:bg-white/10"
            :class="notificationCenterOpen ? 'bg-black/10 dark:bg-white/15' : ''"
            :title="'{{ __('Notification Center') }}'"
        >
            <flux:icon name="bell" class="size-4 text-neutral-800 dark:text-neutral-200" />
            <span
                x-cloak
                x-show="unreadNotificationsCount > 0"
                class="absolute -top-0.5 -right-0.5 flex size-3.5 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white shadow-sm"
                x-text="unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount"
            ></span>
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
