<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div class="space-y-0.5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="#" class="text-xs">{{ $this->t('crumb_settings') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">{{ $this->t('locale_title') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <h1 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">
            {{ $this->t('locale_title') }}
        </h1>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            {{ $this->t('locale_desc') }}
        </p>
    </div>

    <button
        type="button"
        wire:click="resetCategory('locale_time')"
        class="self-start sm:self-auto flex items-center gap-2 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
    >
        <flux:icon name="arrow-path" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
        <span>{{ $this->t('btn_reset') }}</span>
    </button>
</div>

<div class="mt-6 flex flex-col gap-4 max-w-2xl">
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-2">
        <flux:select 
            wire:model.live="locale_time.locale" 
            :label="$this->t('lbl_interface_language')"
        >
            <option value="id">Bahasa Indonesia</option>
            <option value="en">English (US)</option>
        </flux:select>
    </div>

    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-2">
        <flux:select 
            wire:model.live="locale_time.timezone" 
            :label="$this->t('lbl_timezone')"
        >
            <option value="Asia/Jakarta">Asia/Jakarta (WIB - UTC+7)</option>
            <option value="Asia/Makassar">Asia/Makassar (WITA - UTC+8)</option>
            <option value="Asia/Jayapura">Asia/Jayapura (WIT - UTC+9)</option>
        </flux:select>
    </div>
</div>
