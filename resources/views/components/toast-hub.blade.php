@php
    $position = function_exists('os_setting') ? os_setting('notifications.position', 'bottom end') : 'bottom end';

    $positionClasses = match ($position) {
        'top end' => 'top-9 right-4 items-end',
        'top start' => 'top-9 left-4 items-start',
        'bottom start' => 'bottom-4 left-4 items-start',
        default => 'bottom-4 right-4 items-end',
    };
@endphp

<div
    class="fixed z-[100] flex flex-col gap-2.5 pointer-events-none select-none {{ $positionClasses }}"
    aria-live="polite"
>
    <template x-for="toast in activeToasts" :key="toast.id">
        <div
            @mouseenter="pauseToast(toast.id)"
            @mouseleave="resumeToast(toast.id)"
            x-transition:enter="transform transition cubic-bezier(0.16, 1, 0.3, 1) duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-x-4 scale-95"
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
                text-[13px]
                font-medium
                text-neutral-800
                dark:text-neutral-200
                shadow-2xl
                backdrop-blur-2xl
                transition-all
                hover:shadow-indigo-500/10
            "
        >
            {{-- Header: App info & dismiss button --}}
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-1.5 text-xs text-neutral-500 dark:text-neutral-400">
                    <img src="{{ asset('minios/images/logo.png') }}" alt="MiniOS" class="size-3.5 opacity-80" />
                    <span class="font-semibold text-neutral-700 dark:text-neutral-300" x-text="toast.app || 'MiniOS'"></span>
                    <span class="opacity-40">•</span>
                    <span class="text-[11px] font-mono" x-text="toast.time || '{{ __('Now') }}'"></span>
                </div>

                <button
                    type="button"
                    @click="dismissToast(toast.id)"
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
                        x-show="toast.title"
                        class="text-xs font-semibold text-neutral-900 dark:text-white leading-tight mb-1 truncate"
                        x-text="toast.title"
                    ></h5>
                    <p class="text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed break-words" x-text="toast.text"></p>
                </div>
            </div>
        </div>
    </template>
</div>
