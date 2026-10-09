<div
    x-cloak
    x-show="notificationCenterOpen"
    @click.outside="notificationCenterOpen = false"
    @click.stop
    style="display: none;"
    x-transition:enter="transition ease-out duration-150"
    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
    x-transition:leave="transition ease-in duration-100"
    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
    x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
    class="
        absolute
        right-2
        top-8
        z-[70]
        w-88
        max-w-[calc(100vw-1rem)]
        origin-top-right
        overflow-hidden
        rounded-xl
        border
        border-black/10
        dark:border-white/10
        bg-white/90
        dark:bg-[#1c1c1e]/90
        text-[13px]
        font-medium
        text-neutral-800
        dark:text-neutral-200
        shadow-2xl
        backdrop-blur-2xl
        select-none
        flex
        flex-col
        max-h-[calc(100vh-3.5rem)]
    "
>
    {{-- Header --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02]">
        <div class="flex items-center gap-2">
            <flux:icon name="bell" class="size-4 text-neutral-600 dark:text-neutral-400" />
            <span class="text-sm font-semibold text-neutral-900 dark:text-white">{{ __('Notifications') }}</span>
            <span
                x-show="notifications.length > 0"
                class="rounded-full bg-neutral-200 dark:bg-neutral-800 px-2 py-0.5 text-[11px] font-medium text-neutral-700 dark:text-neutral-300"
                x-text="notifications.length"
            ></span>
        </div>

        <div class="flex items-center gap-1">
            <button
                type="button"
                x-show="notifications.length > 0"
                @click="clearAllNotifications()"
                class="rounded px-2 py-1 text-xs text-neutral-500 hover:text-neutral-900 dark:text-neutral-400 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
            >
                {{ __('Clear all') }}
            </button>
            <button
                type="button"
                @click="notificationCenterOpen = false"
                class="rounded p-1 text-neutral-400 hover:text-neutral-700 dark:text-neutral-500 dark:hover:text-neutral-200 hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
                title="{{ __('Close') }}"
            >
                <flux:icon name="x-mark" class="size-4" />
            </button>
        </div>
    </div>

    {{-- Notification List / Content --}}
    <div class="flex-1 overflow-y-auto p-3 space-y-2 min-h-[140px] max-h-[460px]">
        {{-- Empty State --}}
        <div
            x-show="notifications.length === 0"
            class="flex flex-col items-center justify-center py-12 text-center"
        >
            <div class="flex size-12 items-center justify-center rounded-full bg-black/[0.04] dark:bg-white/[0.05] text-neutral-400 dark:text-neutral-500 mb-2">
                <flux:icon name="bell-slash" class="size-6 opacity-70" />
            </div>
            <p class="text-xs font-semibold text-neutral-700 dark:text-neutral-300">{{ __('No new notifications') }}</p>
            <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">{{ __('You\'re all caught up!') }}</p>
        </div>

        {{-- Item List --}}
        <template x-for="notif in notifications" :key="notif.id">
            <div
                class="group relative flex flex-col gap-2 rounded-xl border border-black/5 dark:border-white/5 bg-white/75 dark:bg-white/[0.04] p-3 shadow-xs hover:bg-white/90 dark:hover:bg-white/[0.07] transition-all"
            >
                {{-- Item Header: App Icon, App Name, Time, Dismiss Button --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs text-neutral-500 dark:text-neutral-400">
                        {{-- App Icon --}}
                        <div class="size-3.5 shrink-0 flex items-center justify-center">
                            <template x-if="notif.icon_url">
                                <img :src="notif.icon_url" alt="" class="size-3.5 object-contain rounded" />
                            </template>

                            @foreach (config('desktop.applications') as $appId => $application)
                                <template x-if="!notif.icon_url && (notif.appId === '{{ $appId }}' || notif.icon === '{{ $application['icon'] }}' || (notif.app && notif.app.toLowerCase() === '{{ strtolower($application['name']) }}'))">
                                    <x-minios.icon :name="$application['icon']" class="size-3.5 object-contain" />
                                </template>
                            @endforeach

                            <template x-if="!notif.icon_url && !notif.icon && !@js(array_keys(config('desktop.applications'))).includes(notif.appId)">
                                <img src="{{ asset('minios/images/logo.png') }}" alt="MiniOS" class="size-3 opacity-80" />
                            </template>
                        </div>

                        <span class="font-semibold text-neutral-700 dark:text-neutral-300" x-text="notif.app || 'MiniOS'"></span>
                        <span class="opacity-40">•</span>
                        <span class="text-[10px] text-neutral-400 dark:text-neutral-500 font-mono" x-text="notif.time"></span>
                    </div>

                    <button
                        type="button"
                        @click.stop="removeNotification(notif.id)"
                        class="p-0.5 rounded text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-black/5 dark:hover:bg-white/10 opacity-60 group-hover:opacity-100 transition-all"
                        title="{{ __('Dismiss') }}"
                    >
                        <flux:icon name="x-mark" class="size-3.5" />
                    </button>
                </div>

                {{-- Item Body: Variant Icon + Content --}}
                <div class="flex items-start gap-2.5">
                    {{-- Variant Icon --}}
                    <div class="shrink-0 mt-0.5">
                        <template x-if="notif.variant === 'success'">
                            <div class="flex size-5 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                                <flux:icon name="check-circle" class="size-3.5" />
                            </div>
                        </template>
                        <template x-if="notif.variant === 'danger' || notif.variant === 'error'">
                            <div class="flex size-5 items-center justify-center rounded-full bg-rose-500/15 text-rose-600 dark:text-rose-400">
                                <flux:icon name="exclamation-circle" class="size-3.5" />
                            </div>
                        </template>
                        <template x-if="notif.variant === 'warning'">
                            <div class="flex size-5 items-center justify-center rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                <flux:icon name="exclamation-triangle" class="size-3.5" />
                            </div>
                        </template>
                        <template x-if="notif.variant === 'info' || (!['success', 'danger', 'error', 'warning'].includes(notif.variant))">
                            <div class="flex size-5 items-center justify-center rounded-full bg-blue-500/15 text-blue-600 dark:text-blue-400">
                                <flux:icon name="information-circle" class="size-3.5" />
                            </div>
                        </template>
                    </div>

                    {{-- Content Body --}}
                    <div class="flex-1 min-w-0">
                        <h4
                            x-show="notif.title && notif.title.trim().toLowerCase() !== (notif.app || '').trim().toLowerCase()"
                            class="text-xs font-semibold text-neutral-900 dark:text-white truncate mb-0.5"
                            x-text="notif.title"
                        ></h4>
                        <p class="text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed break-words" x-text="notif.text"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Footer Actions / Quick Settings --}}
    <div class="border-t border-black/5 dark:border-white/10 bg-black/[0.02] dark:bg-white/[0.02] p-2 flex items-center justify-between">
        <button
            type="button"
            @click="notificationCenterOpen = false; openApplication('settings')"
            class="flex items-center gap-1.5 rounded px-2.5 py-1 text-xs text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
        >
            <flux:icon name="cog-6-tooth" class="size-3.5" />
            <span>{{ __('Notification Settings') }}</span>
        </button>

        <button
            type="button"
            @click="toggleTheme()"
            class="flex items-center gap-1.5 rounded px-2.5 py-1 text-xs text-neutral-600 dark:text-neutral-400 hover:text-neutral-900 dark:hover:text-white hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
            title="{{ __('Toggle Theme') }}"
        >
            <span x-show="isDarkMode" class="flex items-center gap-1">
                <flux:icon name="sun" class="size-3.5 text-amber-400" />
                <span>{{ __('Light Mode') }}</span>
            </span>
            <span x-show="!isDarkMode" class="flex items-center gap-1">
                <flux:icon name="moon" class="size-3.5 text-neutral-700" />
                <span>{{ __('Dark Mode') }}</span>
            </span>
        </button>
    </div>
</div>
