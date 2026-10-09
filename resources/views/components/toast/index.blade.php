@php
    $position = function_exists('os_setting') ? os_setting('notifications.position', 'bottom end') : 'bottom end';
@endphp

<div class="relative flex flex-col items-end pointer-events-none w-80 sm:w-88 max-w-[calc(100vw-2rem)]">
    <template x-for="(toast, index) in activeToasts" :key="toast.id">
        <div
            :style="getToastStyle(toast)"
            @mouseenter="pauseToast(toast.id)"
            @mouseleave="resumeToast(toast.id)"
            :class="{
                'animate-toast-in-right': toast.entering && !(settings?.notifications?.position || '').includes('start'),
                'animate-toast-in-left': toast.entering && (settings?.notifications?.position || '').includes('start'),
            }"
            class="
                pointer-events-auto
                relative
                w-80
                sm:w-88
                max-w-[calc(100vw-2rem)]
                overflow-hidden
                rounded-xl
                border
                border-black/10
                dark:border-white/10
                bg-white/95
                dark:bg-[#1f1f22]/95
                p-3.5
                pb-4
                text-[13px]
                font-medium
                text-neutral-800
                dark:text-neutral-200
                shadow-[0_8px_30px_rgb(0,0,0,0.12)]
                dark:shadow-[0_8px_30px_rgb(0,0,0,0.4)]
                backdrop-blur-2xl
                transition-[transform,opacity,margin]
                duration-200
                ease-[cubic-bezier(0.16,1,0.3,1)]
                hover:shadow-indigo-500/10
            "
        >
            {{-- Header: App info & dismiss button --}}
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-1.5 text-xs text-neutral-500 dark:text-neutral-400">
                    {{-- App Icon --}}
                    <div class="size-4 shrink-0 flex items-center justify-center">
                        <template x-if="toast.icon_url">
                            <img :src="toast.icon_url" alt="" class="size-4 object-contain rounded" />
                        </template>

                        @foreach (config('desktop.applications') as $appId => $application)
                            <template x-if="!toast.icon_url && (toast.appId === '{{ $appId }}' || toast.icon === '{{ $application['icon'] }}' || (toast.app && toast.app.toLowerCase() === '{{ strtolower($application['name']) }}'))">
                                <x-minios.icon :name="$application['icon']" class="size-4 object-contain" />
                            </template>
                        @endforeach

                        <template x-if="!toast.icon_url && !toast.icon && !@js(array_keys(config('desktop.applications'))).includes(toast.appId)">
                            <img src="{{ asset('minios/images/logo.png') }}" alt="MiniOS" class="size-3.5 opacity-80" />
                        </template>
                    </div>

                    <span class="font-semibold text-neutral-700 dark:text-neutral-300" x-text="toast.app || 'MiniOS'"></span>
                    <span class="opacity-40">•</span>
                    <span class="text-[11px] font-mono" x-text="toast.time || '{{ __('Now') }}'"></span>
                </div>

                <button
                    type="button"
                    @click.stop="dismissToast(toast.id)"
                    class="rounded-md p-0.5 text-neutral-400 hover:text-neutral-700 dark:hover:text-neutral-200 hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
                    title="{{ __('Close') }}"
                >
                    <flux:icon name="x-mark" class="size-3.5" />
                </button>
            </div>

            {{-- Body: Variant indicator + Title + Message --}}
            <div class="flex items-start gap-2.5">
                {{-- Variant Icon Pill --}}
                <div class="shrink-0 mt-0.5">
                    <template x-if="toast.variant === 'success'">
                        <div class="flex size-5 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-600 dark:text-emerald-400">
                            <flux:icon name="check-circle" class="size-3.5" />
                        </div>
                    </template>
                    <template x-if="toast.variant === 'danger' || toast.variant === 'error'">
                        <div class="flex size-5 items-center justify-center rounded-full bg-rose-500/15 text-rose-600 dark:text-rose-400">
                            <flux:icon name="exclamation-circle" class="size-3.5" />
                        </div>
                    </template>
                    <template x-if="toast.variant === 'warning'">
                        <div class="flex size-5 items-center justify-center rounded-full bg-amber-500/15 text-amber-600 dark:text-amber-400">
                            <flux:icon name="exclamation-triangle" class="size-3.5" />
                        </div>
                    </template>
                    <template x-if="toast.variant === 'info' || (!['success', 'danger', 'error', 'warning'].includes(toast.variant))">
                        <div class="flex size-5 items-center justify-center rounded-full bg-blue-500/15 text-blue-600 dark:text-blue-400">
                            <flux:icon name="information-circle" class="size-3.5" />
                        </div>
                    </template>
                </div>

                {{-- Content text --}}
                <div class="flex-1 min-w-0">
                    <h5
                        x-show="toast.title && toast.title.trim().toLowerCase() !== (toast.app || '').trim().toLowerCase() && toast.title.trim().toLowerCase() !== 'minios'"
                        class="text-xs font-semibold text-neutral-900 dark:text-white leading-tight mb-1 truncate"
                        x-text="toast.title"
                    ></h5>
                    <p class="text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed break-words" x-text="toast.text"></p>
                </div>
            </div>

            {{-- Bottom Progress Loader --}}
            <div class="absolute bottom-0 left-0 right-0 h-[2.5px] bg-black/5 dark:bg-white/10 overflow-hidden rounded-b-xl">
                <div
                    class="h-full transition-all ease-linear"
                    :class="{
                        'bg-emerald-500': toast.variant === 'success',
                        'bg-rose-500': toast.variant === 'danger' || toast.variant === 'error',
                        'bg-amber-500': toast.variant === 'warning',
                        'bg-blue-500': toast.variant === 'info' || !['success', 'danger', 'error', 'warning'].includes(toast.variant)
                    }"
                    :style="`width: ${toast.progress ?? 100}%; transition-duration: ${toast.paused || toastGroupHovered ? '0ms' : '50ms'};`"
                ></div>
            </div>
        </div>
    </template>
</div>
