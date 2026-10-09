<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div class="space-y-0.5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="#" class="text-xs">{{ $this->t('crumb_settings') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">{{ $this->t('nav_notifications') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <h1 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">
            {{ $this->t('notifications_title') }}
        </h1>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            {{ $this->t('notifications_desc') }}
        </p>
    </div>

    <button
        type="button"
        wire:click="resetCategory('notifications')"
        class="self-start sm:self-auto flex items-center gap-2 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
    >
        <flux:icon name="arrow-path" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
        <span>{{ $this->t('btn_reset') }}</span>
    </button>
</div>

<div class="mt-6 flex flex-col gap-5 max-w-2xl">

    {{-- POSISI TOAST NOTIFIKASI --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-4">
        <div>
            <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">
                {{ $this->t('notif_position_title') }}
            </h2>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                {{ $this->t('notif_position_desc') }}
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @php
                $positions = [
                    'bottom end' => [
                        'label' => $this->t('notif_pos_bottom_right'),
                        'desc' => $this->t('notif_pos_bottom_right_desc'),
                        'icon' => 'arrow-down-right',
                    ],
                    'top end' => [
                        'label' => $this->t('notif_pos_top_right'),
                        'desc' => $this->t('notif_pos_top_right_desc'),
                        'icon' => 'arrow-up-right',
                    ],
                    'bottom start' => [
                        'label' => $this->t('notif_pos_bottom_left'),
                        'desc' => $this->t('notif_pos_bottom_left_desc'),
                        'icon' => 'arrow-down-left',
                    ],
                    'top start' => [
                        'label' => $this->t('notif_pos_top_left'),
                        'desc' => $this->t('notif_pos_top_left_desc'),
                        'icon' => 'arrow-up-left',
                    ],
                ];
                $currentPos = $notifications['position'] ?? 'bottom end';
            @endphp

            @foreach ($positions as $val => $info)
                @php $isSelected = $currentPos === $val; @endphp
                <div
                    wire:click="$set('notifications.position', '{{ $val }}')"
                    class="cursor-pointer rounded-xl border p-3.5 transition-all flex items-start gap-3 {{ $isSelected ? 'border-sky-500 bg-sky-500/5 ring-1 ring-sky-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
                >
                    <div class="flex size-8 shrink-0 items-center justify-center rounded-lg {{ $isSelected ? 'bg-sky-500 text-white' : 'bg-neutral-100 dark:bg-white/10 text-neutral-500' }}">
                        <flux:icon :name="$info['icon']" class="size-4" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-semibold text-neutral-900 dark:text-white flex items-center justify-between">
                            <span>{{ $info['label'] }}</span>
                            @if ($isSelected)
                                <span class="size-1.5 rounded-full bg-sky-500"></span>
                            @endif
                        </div>
                        <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5 leading-normal">
                            {{ $info['desc'] }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- SUARA NOTIFIKASI --}}
    <div class="flex items-start justify-between rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">
                {{ $this->t('notif_sound_title') }}
            </div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                {{ $this->t('notif_sound_desc') }}
            </div>
        </div>
        <flux:switch wire:model.live="notifications.sound" />
    </div>

    {{-- UJI NOTIFIKASI --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">
                {{ $this->t('notif_test_header') }}
            </div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">
                {{ $this->t('notif_test_header_desc') }}
            </div>
        </div>

        <button
            type="button"
            wire:click="testNotification"
            class="inline-flex items-center justify-center gap-2 rounded-md bg-sky-600 hover:bg-sky-500 text-white px-4 py-2 text-xs font-medium shadow-2xs transition-all active:scale-98 shrink-0"
        >
            <flux:icon name="bell" class="size-3.5" />
            <span>{{ $this->t('btn_test_notification') }}</span>
        </button>
    </div>

</div>
