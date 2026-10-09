<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div class="space-y-0.5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="#" class="text-xs">{{ $this->t('crumb_settings') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">{{ $this->t('account_title') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <h1 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">
            {{ $this->t('account_title') }}
        </h1>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            {{ $this->t('account_desc') }}
        </p>
    </div>
</div>

<div class="mt-6 flex flex-col gap-5 max-w-2xl">
    
    {{-- Windows 11 Profile Hero Card --}}
    <div class="flex items-center gap-4 rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-5 shadow-2xs">
        <div class="flex size-12 items-center justify-center rounded-full text-xl font-bold text-white shadow-xs shrink-0" style="background-color: var(--accent-color, {{ $accent['hex'] }});">
            {{ auth()->user()?->initials() ?? 'US' }}
        </div>
        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <h4 class="text-sm font-bold text-neutral-900 dark:text-white truncate">{{ auth()->user()?->name ?? $this->t('default_user_name') }}</h4>
                <span class="rounded-md bg-neutral-100 dark:bg-white/10 px-2 py-0.5 text-[10px] font-medium text-neutral-600 dark:text-neutral-400">Administrator</span>
            </div>
            <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5 truncate">{{ auth()->user()?->email ?? 'user@minios.local' }}</p>
        </div>
    </div>

    {{-- Edit Profile Card --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-5 shadow-2xs space-y-4">
        <div class="space-y-1">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('account_profile_title') }}</h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('account_profile_desc') }}</p>
        </div>

        @if ($profileStatus)
            <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-3 py-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                <flux:icon name="check-circle" class="size-4 shrink-0 text-emerald-500" />
                <span>{{ $profileStatus }}</span>
            </div>
        @endif

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $this->t('lbl_full_name') }}</label>
                <input
                    type="text"
                    wire:model="profile_name"
                    class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-2.5 py-1.5 text-sm text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    placeholder="{{ $this->t('placeholder_full_name') }}"
                />
                @error('profile_name')
                    <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $this->t('lbl_email_address') }}</label>
                <input
                    type="email"
                    wire:model="profile_email"
                    class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-2.5 py-1.5 text-sm text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    placeholder="nama@contoh.com"
                />
                @error('profile_email')
                    <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="pt-1 flex items-center justify-end">
            <button
                type="button"
                wire:click="updateProfile"
                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                class="flex items-center gap-2 rounded-md px-4 py-1.5 text-sm font-medium text-white shadow-2xs transition-all hover:brightness-110 active:scale-98"
            >
                <flux:icon name="check" class="size-3.5 stroke-[2.5]" />
                <span>{{ $this->t('btn_save_profile') }}</span>
            </button>
        </div>
    </div>

    {{-- Ubah Kata Sandi Card --}}
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-5 shadow-2xs space-y-4">
        <div class="space-y-1">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('password_title') }}</h3>
            <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('password_desc') }}</p>
        </div>

        @if ($passwordStatus)
            <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-3 py-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                <flux:icon name="check-circle" class="size-4 shrink-0 text-emerald-500" />
                <span>{{ $passwordStatus }}</span>
            </div>
        @endif

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $this->t('lbl_current_password') }}</label>
                <input
                    type="password"
                    wire:model="current_password"
                    class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-2.5 py-1.5 text-sm text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    placeholder="••••••••"
                />
                @error('current_password')
                    <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $this->t('lbl_new_password') }}</label>
                <input
                    type="password"
                    wire:model="password"
                    class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-2.5 py-1.5 text-sm text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    placeholder="{{ $this->t('placeholder_new_password') }}"
                />
                @error('password')
                    <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $this->t('lbl_confirm_password') }}</label>
                <input
                    type="password"
                    wire:model="password_confirmation"
                    class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-2.5 py-1.5 text-sm text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    placeholder="{{ $this->t('placeholder_confirm_password') }}"
                />
                @error('password_confirmation')
                    <p class="mt-1 text-[11px] text-rose-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="pt-1 flex items-center justify-end">
            <button
                type="button"
                wire:click="updatePassword"
                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                class="flex items-center gap-2 rounded-md px-4 py-1.5 text-sm font-medium text-white shadow-2xs transition-all hover:brightness-110 active:scale-98"
            >
                <flux:icon name="key" class="size-3.5" />
                <span>{{ $this->t('btn_update_password') }}</span>
            </button>
        </div>
    </div>

    {{-- Security Advanced Links Card (Optional Safe Link) --}}
    @if (\Illuminate\Support\Facades\Route::has('profile.edit') || \Illuminate\Support\Facades\Route::has('security.edit'))
        <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-5 shadow-2xs space-y-3">
            <div class="flex items-center gap-3">
                <div class="flex size-10 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-500 dark:text-indigo-400">
                    <flux:icon name="shield-check" class="size-5" />
                </div>
                <div class="space-y-1">
                    <div class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('security_advanced_title') }}</div>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('security_advanced_desc') }}</p>
                </div>
            </div>
            <div class="pt-1">
                <a
                    href="{{ \Illuminate\Support\Facades\Route::has('security.edit') ? route('security.edit') : route('profile.edit') }}"
                    target="_blank"
                    class="inline-flex items-center gap-2 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
                >
                    <span>{{ $this->t('btn_open_security_portal') }}</span>
                    <flux:icon name="arrow-top-right-on-square" class="size-3.5" />
                </a>
            </div>
        </div>
    @endif
</div>
