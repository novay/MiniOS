<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div class="space-y-0.5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="#" class="text-xs">{{ $this->t('crumb_settings') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">{{ $this->t('section_services') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">{{ $this->t('nav_mail') }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <h1 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">
            {{ $this->t('mail_title') }}
        </h1>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            {{ $this->t('mail_desc') }}
        </p>
    </div>

    <button
        type="button"
        wire:click="resetCategory('mail')"
        class="self-start sm:self-auto flex items-center gap-2 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
    >
        <flux:icon name="arrow-path" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
        <span>{{ $this->t('btn_reset') }}</span>
    </button>
</div>

<div class="mt-6 flex flex-col gap-6 max-w-3xl">
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-4">
        <div class="flex items-start gap-3">
            
            <div>
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('mail_section_title') }}</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                    {{ $this->t('mail_section_desc') }}
                </p>
            </div>
        </div>

        {{-- Mail Driver Selection --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3 pt-1">
            {{-- Log Driver --}}
            @php $isLog = ($services['mail_driver'] ?? 'log') === 'log'; @endphp
            <div
                wire:click="$set('services.mail_driver', 'log')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isLog ? 'border-sky-500 bg-sky-500/5 ring-1 ring-sky-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">{{ $this->t('driver_mail_log_title') }}</span>
                    <span class="size-2 rounded-full {{ $isLog ? 'bg-sky-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    Direktori <code class="font-mono text-[10px]">storage/logs</code>. {{ $this->t('driver_mail_log_desc') }}
                </p>
            </div>

            {{-- SMTP Server Driver --}}
            @php $isSmtp = ($services['mail_driver'] ?? 'log') === 'smtp'; @endphp
            <div
                wire:click="$set('services.mail_driver', 'smtp')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isSmtp ? 'border-sky-500 bg-sky-500/5 ring-1 ring-sky-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">{{ $this->t('driver_mail_smtp_title') }}</span>
                    <span class="size-2 rounded-full {{ $isSmtp ? 'bg-sky-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    {{ $this->t('driver_mail_smtp_desc') }}
                </p>
            </div>

            {{-- Sendmail Driver --}}
            @php $isSendmail = ($services['mail_driver'] ?? 'log') === 'sendmail'; @endphp
            <div
                wire:click="$set('services.mail_driver', 'sendmail')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isSendmail ? 'border-sky-500 bg-sky-500/5 ring-1 ring-sky-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">{{ $this->t('driver_mail_sendmail_title') }}</span>
                    <span class="size-2 rounded-full {{ $isSendmail ? 'bg-sky-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    {{ $this->t('driver_mail_sendmail_desc') }}
                </p>
            </div>

            {{-- Resend Driver --}}
            @php $isResend = ($services['mail_driver'] ?? 'log') === 'resend'; @endphp
            <div
                wire:click="$set('services.mail_driver', 'resend')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isResend ? 'border-sky-500 bg-sky-500/5 ring-1 ring-sky-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">{{ $this->t('driver_mail_resend_title') }}</span>
                    <span class="size-2 rounded-full {{ $isResend ? 'bg-sky-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    {{ $this->t('driver_mail_resend_desc') }}
                </p>
            </div>
        </div>

        {{-- Parameter Konfigurasi SMTP (Muncul jika SMTP dipilih) --}}
        @if (($services['mail_driver'] ?? 'log') === 'smtp')
            <div class="mt-4 rounded-xl border border-neutral-200/80 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 p-4 space-y-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">{{ $this->t('smtp_config_title') }}</span>
                    <span class="text-[10px] text-neutral-500 font-mono">{{ $this->t('smtp_ports_hint') }}</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('lbl_smtp_host') }}</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.smtp_host"
                            placeholder="smtp.mailtrap.io atau smtp.gmail.com"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('lbl_port') }}</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.smtp_port"
                            placeholder="587"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500 font-mono"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('lbl_encryption') }}</label>
                        <flux:select wire:model.live="services.smtp_encryption">
                            <option value="tls">{{ $this->t('enc_tls') }}</option>
                            <option value="ssl">{{ $this->t('enc_ssl') }}</option>
                            <option value="none">{{ $this->t('enc_none') }}</option>
                        </flux:select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('lbl_username') }}</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.smtp_username"
                            placeholder="username-smtp"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('lbl_password') }}</label>
                        <input
                            type="password"
                            wire:model.live.debounce.300ms="services.smtp_password"
                            placeholder="••••••••••••"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>
                </div>
            </div>
        @endif

        {{-- Parameter Konfigurasi Resend (Muncul jika Resend dipilih) --}}
        @if (($services['mail_driver'] ?? 'log') === 'resend')
            <div class="mt-4 rounded-xl border border-neutral-200/80 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 p-4 space-y-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">{{ $this->t('resend_config_title') }}</span>
                    <span class="text-[10px] text-neutral-500 font-mono">resend.com/api-keys</span>
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('lbl_resend_api_key') }}</label>
                    <input
                        type="password"
                        wire:model.live.debounce.300ms="services.resend_api_key"
                        placeholder="re_123456789_abcdefghijklmnopqrstuvwxyz"
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500 font-mono"
                    />
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1">
                        {{ $this->t('resend_hint') }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Identitas Pengirim Global (Berlaku untuk semua driver selain log) --}}
        <div class="mt-4 rounded-xl border border-neutral-200/80 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 p-4 space-y-3.5">
            <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">{{ $this->t('global_sender_title') }}</span>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('lbl_from_email') }}</label>
                    <input
                        type="email"
                        wire:model.live.debounce.300ms="services.mail_from_address"
                        placeholder="notifikasi@minios.local"
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    />
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">{{ $this->t('lbl_from_name') }}</label>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="services.mail_from_name"
                        placeholder="MiniOS System"
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    />
                </div>
            </div>
        </div>

        {{-- Test Mail Delivery Action --}}
        <div class="pt-2 flex items-center justify-between flex-wrap gap-2">
            <button
                type="button"
                wire:click="testMailDelivery"
                wire:loading.attr="disabled"
                class="flex items-center gap-2 rounded-lg border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3.5 py-1.5 text-xs font-medium text-neutral-800 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-white/5 active:scale-98 transition-all disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="testMailDelivery">
                    <flux:icon name="paper-airplane" class="size-3.5 text-sky-600 dark:text-sky-400" />
                </span>
                <span wire:loading wire:target="testMailDelivery" class="inline-block animate-spin size-3.5 border-2 border-sky-600 border-t-transparent rounded-full"></span>
                <span>{{ $this->t('btn_test_mail') }}</span>
            </button>

            @if ($mailTestStatus)
                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1.5">
                    <flux:icon name="check-circle" class="size-4" />
                    <span>{{ $mailTestStatus }}</span>
                </span>
            @endif

            @if ($mailTestError)
                <span class="text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1.5">
                    <flux:icon name="exclamation-circle" class="size-4" />
                    <span>{{ $mailTestError }}</span>
                </span>
            @endif
        </div>
    </div>
</div>
