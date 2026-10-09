<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div class="space-y-0.5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="#" class="text-xs">{{ $this->t('crumb_settings') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">{{ $this->t('window_title') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <h1 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">
            {{ $this->t('window_title') }}
        </h1>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            {{ $this->t('window_desc') }}
        </p>
    </div>

    <button
        type="button"
        wire:click="resetCategory('window_manager')"
        class="self-start sm:self-auto flex items-center gap-2 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
    >
        <flux:icon name="arrow-path" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
        <span>{{ $this->t('btn_reset') }}</span>
    </button>
</div>

<div class="mt-6 flex flex-col gap-4 max-w-2xl">

    {{-- Pulihkan Sesi Sebelumnya Card --}}
    <div class="flex items-start justify-between rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('restore_session_title') }}</div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('restore_session_desc') }}</div>
        </div>
        <flux:switch wire:model.live="window_manager.restore_session" />
    </div>

    {{-- Ingat Posisi & Ukuran Window Card --}}
    <div class="flex items-start justify-between rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('remember_position_title') }}</div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('remember_position_desc') }}</div>
        </div>
        <flux:switch wire:model.live="window_manager.remember_position" />
    </div>
</div>
