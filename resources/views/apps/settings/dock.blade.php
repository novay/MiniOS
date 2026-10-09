<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div class="space-y-0.5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="#" class="text-xs">{{ $this->t('crumb_settings') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">{{ $this->t('dock_title') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <h1 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">
            {{ $this->t('dock_title') }}
        </h1>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            {{ $this->t('dock_desc') }}
        </p>
    </div>

    <button
        type="button"
        wire:click="resetCategory('dock')"
        class="self-start sm:self-auto flex items-center gap-2 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
    >
        <flux:icon name="arrow-path" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
        <span>{{ $this->t('btn_reset') }}</span>
    </button>
</div>

<div class="mt-6 flex flex-col gap-4 max-w-2xl">
    {{-- Ukuran Dock Card --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-1">
        <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('dock_size_title') }}</div>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">{{ $this->t('dock_size_desc') }}</p>
        <flux:select wire:model.live="dock.size">
            <option value="small">{{ $this->t('dock_size_small') }}</option>
            <option value="medium">{{ $this->t('dock_size_medium') }}</option>
            <option value="large">{{ $this->t('dock_size_large') }}</option>
        </flux:select>
    </div>

    {{-- Posisi Layar Card --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-1">
        <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('dock_position_title') }}</div>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mb-2">{{ $this->t('dock_position_desc') }}</p>
        <flux:select wire:model.live="dock.position">
            <option value="bottom">{{ $this->t('dock_pos_bottom') }}</option>
            <option value="left">{{ $this->t('dock_pos_left') }}</option>
            <option value="right">{{ $this->t('dock_pos_right') }}</option>
        </flux:select>
    </div>

    {{-- Autohide Dock Card --}}
    <div class="flex items-start justify-between rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('dock_autohide_title') }}</div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('dock_autohide_desc') }}</div>
        </div>
        <flux:switch wire:model.live="dock.autohide" />
    </div>

    {{-- Indikator Aktif Card --}}
    <div class="flex items-start justify-between rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs">
        <div class="space-y-1">
            <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('dock_indicators_title') }}</div>
            <div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('dock_indicators_desc') }}</div>
        </div>
        <flux:switch wire:model.live="dock.show_indicators" />
    </div>
</div>
