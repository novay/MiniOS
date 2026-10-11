<div
    id="taskbar"
    @mouseenter="dockHovered = true"
    @mouseleave="dockHovered = false"
>
    {{-- Autohide Edge Trigger Zone --}}
    <div
        x-cloak
        x-show="settings?.dock?.autohide && !applicationsOpen"
        class="fixed z-[8999]"
        :class="{
            'bottom-0 left-0 right-0 h-2': (settings?.dock?.position ?? 'bottom') === 'bottom',
            'left-0 top-0 bottom-0 w-2': (settings?.dock?.position ?? 'bottom') === 'left',
            'right-0 top-0 bottom-0 w-2': (settings?.dock?.position ?? 'bottom') === 'right',
        }"
    ></div>

    <aside
        x-cloak
        data-desktop-dock
        :class="{
            '-translate-x-full': (settings?.dock?.position ?? 'bottom') === 'left' && shouldHideDock(),
            'translate-x-full': (settings?.dock?.position ?? 'bottom') === 'right' && shouldHideDock(),
            'translate-y-full': (settings?.dock?.position ?? 'bottom') === 'bottom' && shouldHideDock(),
            
            {{-- DOCK BOTTOM: Adaptive Glass (Light / Dark berdasarkan luminansi wallpaper & theme) --}}
            'bottom-2 left-3 right-3 flex-row px-4 items-center justify-between rounded-lg backdrop-blur-2xl backdrop-saturate-[180%] shadow-2xl transition-all duration-300': (settings?.dock?.position ?? 'bottom') === 'bottom',
            'bg-neutral-900/60 border border-white/20 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.25)] text-white shadow-black/35': (settings?.dock?.position ?? 'bottom') === 'bottom' && isDockTextBright,
            'bg-white/88 border border-white/70 border-t-white/90 shadow-[inset_0_1px_0_0_rgba(255,255,255,0.7)] text-neutral-900 shadow-black/10': (settings?.dock?.position ?? 'bottom') === 'bottom' && !isDockTextBright,
            
            {{-- DOCK LEFT --}}
            'bottom-0 left-0 top-0 flex-col py-2 items-center justify-between backdrop-blur-2xl backdrop-saturate-[180%] border-r shadow-lg transition-all duration-300': (settings?.dock?.position ?? 'bottom') === 'left',
            'bg-neutral-900/60 border-white/15 text-white': (settings?.dock?.position ?? 'bottom') === 'left' && isDockTextBright,
            'bg-white/88 border-white/50 text-neutral-900': (settings?.dock?.position ?? 'bottom') === 'left' && !isDockTextBright,
            
            {{-- DOCK RIGHT --}}
            'bottom-0 right-0 top-0 flex-col py-2 items-center justify-between backdrop-blur-2xl backdrop-saturate-[180%] border-l shadow-lg transition-all duration-300': (settings?.dock?.position ?? 'bottom') === 'right',
            'bg-neutral-900/60 border-white/15 text-white': (settings?.dock?.position ?? 'bottom') === 'right' && isDockTextBright,
            'bg-white/88 border-white/50 text-neutral-900': (settings?.dock?.position ?? 'bottom') === 'right' && !isDockTextBright,
            
            'opacity-0 pointer-events-none': applicationsOpen,
        }"
        :style="(settings?.dock?.position ?? 'bottom') === 'bottom'
            ? 'height: ' + getDockPx() + 'px'
            : 'width: ' + getDockPx() + 'px'"
        class="desktop-dock absolute z-[9000] flex transform-gpu transition-all duration-300 ease-[cubic-bezier(0.22,1,0.36,1)] select-none pointer-events-auto"
    >
        {{-- KIRI: Spacer Penyeimbang (Bottom Dock) --}}
        <div 
            x-show="(settings?.dock?.position ?? 'bottom') === 'bottom'"
            class="flex-1 hidden md:block"
        ></div>

        {{-- TENGAH: Dock Apps --}}
        <div class="flex items-center justify-center">
            <nav
                class="flex items-center justify-center gap-1 sm:gap-1.5"
                :class="{
                    'flex-row h-full': (settings?.dock?.position ?? 'bottom') === 'bottom',
                    'flex-col w-full': (settings?.dock?.position ?? 'bottom') !== 'bottom',
                }"
            >
                {{-- SHOW APPLICATIONS (Windows 11 Start Menu Button) --}}
                <div class="flex order-first">
                    <x-minios.dock-item
                        label="MiniOS"
                        @click.stop="toggleApplications()"
                    >
                        <x-minios.icon name="minios" />
                    </x-minios.dock-item>
                </div>

                {{-- WINDOWS 11 SEPARATOR --}}
                <div
                    class="shrink-0 select-none rounded-full transition-all duration-150 order-first"
                    :class="{
                        'mx-1 w-px self-center': (settings?.dock?.position ?? 'bottom') === 'bottom',
                        'my-1 h-px self-center': (settings?.dock?.position ?? 'bottom') !== 'bottom',
                        'bg-white/25': isDockTextBright,
                        'bg-black/15': !isDockTextBright,
                        'h-5': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'small',
                        'h-6': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'medium',
                        'h-8': (settings?.dock?.position ?? 'bottom') === 'bottom' && (settings?.dock?.size ?? 'medium') === 'large',
                        'w-5': (settings?.dock?.position ?? 'bottom') !== 'bottom' && (settings?.dock?.size ?? 'medium') === 'small',
                        'w-6': (settings?.dock?.position ?? 'bottom') !== 'bottom' && (settings?.dock?.size ?? 'medium') === 'medium',
                        'w-8': (settings?.dock?.position ?? 'bottom') !== 'bottom' && (settings?.dock?.size ?? 'medium') === 'large',
                    }"
                    aria-hidden="true"
                ></div>

                {{-- DOCK APPLICATIONS --}}
                @foreach (config('desktop.applications') as $id => $application)
                    <div
                        x-cloak
                        x-show="isAppInDock(@js($id))"
                        data-dock-wrapper="{{ $id }}"
                        :style="isAppInDock(@js($id)) ? getDockItemStyle(@js($id)) : 'display: none !important;'"
                    >
                        <x-minios.dock-item
                            :label="$application['name']"
                            :app-id="$id"
                        >
                            <x-minios.icon :name="$application['icon']" />
                        </x-minios.dock-item>
                    </div>
                @endforeach
            </nav>
        </div>

        {{-- KANAN: System Tray Icons + Jam & Tanggal (Bottom Dock) --}}
        <div 
            x-show="(settings?.dock?.position ?? 'bottom') === 'bottom'"
            class="flex-1 flex items-center justify-end gap-2"
            :class="isDockTextBright ? 'text-white' : 'text-neutral-900'"
        >
            {{-- System Tray Icons --}}
            <div class="flex items-center gap-1 sm:gap-1.5">
                {{-- Audio Control Dropdown --}}
                <div class="relative" @click.outside="audioDropdownOpen = false">
                    <button
                        type="button"
                        @click.stop="toggleAudioDropdown()"
                        class="p-1.5 rounded-lg transition-all"
                        :class="audioDropdownOpen 
                            ? (isDockTextBright ? 'bg-white/20 text-white' : 'bg-black/15 text-neutral-900') 
                            : (isDockTextBright ? 'text-white/90 hover:text-white hover:bg-white/15' : 'text-neutral-700 hover:text-neutral-950 hover:bg-black/10')"
                        :title="'{{ __('Sound Volume') }}'"
                    >
                        <span x-cloak x-show="globalMuted || globalVolume === 0" class="flex items-center justify-center opacity-70">
                            <flux:icon name="speaker-x-mark" class="size-4" />
                        </span>
                        <span x-cloak x-show="!globalMuted && globalVolume > 0" class="flex items-center justify-center">
                            <flux:icon name="speaker-wave" class="size-4" />
                        </span>
                    </button>

                    {{-- Volume Dropdown Popover (Centered) --}}
                    <div
                        x-cloak
                        x-show="audioDropdownOpen"
                        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-3 z-50 pointer-events-auto"
                        @click.stop
                    >
                        <div
                            x-show="audioDropdownOpen"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                            class="w-64 rounded-xl border border-black/10 dark:border-white/10 bg-white/95 dark:bg-[#1c1c1c]/95 p-3.5 shadow-2xl backdrop-blur-2xl select-none text-neutral-800 dark:text-neutral-100"
                        >
                            <div class="flex items-center justify-between mb-2.5">
                                <div class="flex items-center gap-1.5 text-xs font-semibold">
                                    <span>{{ __('Volume Master') }}</span>
                                </div>
                                <span class="text-xs font-medium text-neutral-500 dark:text-neutral-400 font-mono" x-text="(globalMuted ? 0 : Math.round(globalVolume * 100)) + '%'"></span>
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
                                    class="w-full h-1.5 bg-neutral-200 dark:bg-neutral-700 rounded-lg appearance-none cursor-pointer accent-indigo-500 hover:accent-indigo-600"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notification Center Toggle --}}
                <button
                    type="button"
                    @click.stop="toggleNotificationCenter()"
                    class="relative p-1.5 rounded-lg transition-all"
                    :class="notificationCenterOpen 
                        ? (isDockTextBright ? 'bg-white/20 text-white' : 'bg-black/15 text-neutral-900') 
                        : (isDockTextBright ? 'text-white/90 hover:text-white hover:bg-white/15' : 'text-neutral-700 hover:text-neutral-950 hover:bg-black/10')"
                    :title="'{{ __('Notification Center') }}'"
                >
                    <flux:icon name="bell" class="size-4" />
                    <span
                        x-cloak
                        x-show="unreadNotificationsCount > 0"
                        class="absolute -top-0.5 -right-0.5 flex size-3.5 items-center justify-center rounded-full bg-rose-500 text-[9px] font-bold text-white shadow-sm"
                        x-text="unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount"
                    ></span>
                </button>

                {{-- System Menu / Profile Toggle --}}
                <button
                    type="button"
                    @click.stop="toggleSystemMenu()"
                    class="flex items-center gap-1.5 p-1 rounded-lg transition-all"
                    :class="systemMenuOpen 
                        ? (isDockTextBright ? 'bg-white/20' : 'bg-black/15') 
                        : (isDockTextBright ? 'hover:bg-white/15' : 'hover:bg-black/10')"
                    :title="'{{ __('System Menu') }} (' + '{{ auth()->user()->name ?? 'User' }}' + ')'"
                >
                    <div class="flex size-5 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-[10px] font-bold uppercase text-white shadow-xs">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                </button>
            </div>

            {{-- Separator Vertikal --}}
            <div class="h-4 w-px" :class="isDockTextBright ? 'bg-white/25' : 'bg-black/20'"></div>

            {{-- Jam & Tanggal (Klik Membuka About MiniOS) --}}
            <div
                @click.stop="openApplication('about')"
                class="flex flex-col justify-center text-right px-2.5 py-1 rounded-lg transition-all cursor-pointer group"
                :class="isDockTextBright ? 'hover:bg-white/15' : 'hover:bg-black/10'"
                :title="'{{ __('About MiniOS') }}'"
            >
                <span class="text-xs font-semibold leading-none tracking-tight" :class="isDockTextBright ? 'text-white drop-shadow-[0_1px_2px_rgba(0,0,0,0.5)]' : 'text-neutral-900'" x-text="clockTime"></span>
                <span class="text-[10px] font-medium leading-tight mt-0.5" :class="isDockTextBright ? 'text-white/80 group-hover:text-white drop-shadow-[0_1px_2px_rgba(0,0,0,0.4)]' : 'text-neutral-700 group-hover:text-neutral-950'" x-text="clockDate"></span>
            </div>
        </div>

        {{-- DOCK VERTIKAL TRAY (Left / Right) --}}
        <div
            x-show="(settings?.dock?.position ?? 'bottom') !== 'bottom'"
            class="mt-auto flex flex-col items-center gap-2 pt-2 border-t w-full"
            :class="isDockTextBright ? 'border-white/15 text-white' : 'border-black/10 text-neutral-900'"
        >
            {{-- Volume --}}
            <button
                type="button"
                @click.stop="toggleAudioDropdown()"
                class="p-1.5 rounded-lg transition-all"
                :class="audioDropdownOpen 
                    ? (isDockTextBright ? 'bg-white/20 text-white' : 'bg-black/15 text-neutral-900') 
                    : (isDockTextBright ? 'text-white/90 hover:text-white hover:bg-white/15' : 'text-neutral-700 hover:text-neutral-950 hover:bg-black/10')"
                :title="'{{ __('Sound Volume') }}'"
            >
                <span x-cloak x-show="globalMuted || globalVolume === 0" class="opacity-70">
                    <flux:icon name="speaker-x-mark" class="size-4" />
                </span>
                <span x-cloak x-show="!globalMuted && globalVolume > 0">
                    <flux:icon name="speaker-wave" class="size-4" />
                </span>
            </button>

            {{-- Notification Center --}}
            <button
                type="button"
                @click.stop="toggleNotificationCenter()"
                class="relative p-1.5 rounded-lg transition-all"
                :class="notificationCenterOpen 
                    ? (isDockTextBright ? 'bg-white/20 text-white' : 'bg-black/15 text-neutral-900') 
                    : (isDockTextBright ? 'text-white/90 hover:text-white hover:bg-white/15' : 'text-neutral-700 hover:text-neutral-950 hover:bg-black/10')"
                :title="'{{ __('Notification Center') }}'"
            >
                <flux:icon name="bell" class="size-4" />
                <span
                    x-cloak
                    x-show="unreadNotificationsCount > 0"
                    class="absolute -top-0.5 -right-0.5 flex size-3 items-center justify-center rounded-full bg-rose-500 text-[8px] font-bold text-white shadow-sm"
                    x-text="unreadNotificationsCount > 9 ? '9+' : unreadNotificationsCount"
                ></span>
            </button>

            {{-- User Profile / System Menu --}}
            <button
                type="button"
                @click.stop="toggleSystemMenu()"
                class="flex size-6 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-[10px] font-bold uppercase text-white shadow-xs"
                :title="'{{ __('System Menu') }}'"
            >
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </button>

            {{-- Clock (Klik Membuka About MiniOS) --}}
            <div
                @click.stop="openApplication('about')"
                class="flex flex-col items-center justify-center text-center px-1 py-1 rounded-lg transition-all cursor-pointer"
                :class="isDockTextBright ? 'hover:bg-white/15' : 'hover:bg-black/10'"
                :title="'{{ __('About MiniOS') }}'"
            >
                <span class="text-[11px] font-semibold leading-tight" :class="isDockTextBright ? 'text-white drop-shadow-[0_1px_2px_rgba(0,0,0,0.5)]' : 'text-neutral-900'" x-text="clockTime ? clockTime.split(' ')[0] : '12:12'"></span>
            </div>
        </div>
    </aside>
</div>