<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div class="space-y-0.5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="#" class="text-xs">Pengaturan</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">Layanan &amp; Infrastruktur</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">Mail Delivery</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <h1 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">
            Mail Delivery
        </h1>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            Pengaturan driver pengiriman surel sistem untuk pesan aplikasi.
        </p>
    </div>

    <button
        type="button"
        wire:click="resetCategory('mail')"
        class="self-start sm:self-auto flex items-center gap-2 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
    >
        <flux:icon name="arrow-path" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
        <span>Reset</span>
    </button>
</div>

<div class="mt-6 flex flex-col gap-6 max-w-3xl">
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-4">
        <div class="flex items-start gap-3">
            
            <div>
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Layanan Surel (Mail Delivery)</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                    Pengaturan pengiriman surel sistem.
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
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">Log Sistem (Dev)</span>
                    <span class="size-2 rounded-full {{ $isLog ? 'bg-sky-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    Mencatat surel ke <code class="font-mono text-[10px]">storage/logs</code> tanpa mengirim email sungguhan.
                </p>
            </div>

            {{-- SMTP Server Driver --}}
            @php $isSmtp = ($services['mail_driver'] ?? 'log') === 'smtp'; @endphp
            <div
                wire:click="$set('services.mail_driver', 'smtp')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isSmtp ? 'border-sky-500 bg-sky-500/5 ring-1 ring-sky-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">Server SMTP</span>
                    <span class="size-2 rounded-full {{ $isSmtp ? 'bg-sky-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    Pengiriman nyata via server SMTP (Gmail, Mailtrap, SES, dll).
                </p>
            </div>

            {{-- Sendmail Driver --}}
            @php $isSendmail = ($services['mail_driver'] ?? 'log') === 'sendmail'; @endphp
            <div
                wire:click="$set('services.mail_driver', 'sendmail')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isSendmail ? 'border-sky-500 bg-sky-500/5 ring-1 ring-sky-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">Sendmail</span>
                    <span class="size-2 rounded-full {{ $isSendmail ? 'bg-sky-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    Utilitas sendmail binary lokal server host.
                </p>
            </div>

            {{-- Resend Driver --}}
            @php $isResend = ($services['mail_driver'] ?? 'log') === 'resend'; @endphp
            <div
                wire:click="$set('services.mail_driver', 'resend')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isResend ? 'border-sky-500 bg-sky-500/5 ring-1 ring-sky-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">Resend API</span>
                    <span class="size-2 rounded-full {{ $isResend ? 'bg-sky-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    Email API modern, cepat &amp; developer-friendly dari Resend.com.
                </p>
            </div>
        </div>

        {{-- Parameter Konfigurasi SMTP (Muncul jika SMTP dipilih) --}}
        @if (($services['mail_driver'] ?? 'log') === 'smtp')
            <div class="mt-4 rounded-xl border border-neutral-200/80 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 p-4 space-y-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Konfigurasi Server SMTP</span>
                    <span class="text-[10px] text-neutral-500 font-mono">Port Standar: 587 (TLS) / 465 (SSL)</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="sm:col-span-2 space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">SMTP Host</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.smtp_host"
                            placeholder="smtp.mailtrap.io atau smtp.gmail.com"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Port</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.smtp_port"
                            placeholder="587"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500 font-mono"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Enkripsi</label>
                        <flux:select wire:model.live="services.smtp_encryption">
                            <option value="tls">TLS (Disarankan)</option>
                            <option value="ssl">SSL</option>
                            <option value="none">Tanpa Enkripsi</option>
                        </flux:select>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Username</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.smtp_username"
                            placeholder="username-smtp"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Password</label>
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
                    <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Konfigurasi Resend Mailer</span>
                    <span class="text-[10px] text-neutral-500 font-mono">resend.com/api-keys</span>
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Resend API Key</label>
                    <input
                        type="password"
                        wire:model.live.debounce.300ms="services.resend_api_key"
                        placeholder="re_123456789_abcdefghijklmnopqrstuvwxyz"
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500 font-mono"
                    />
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1">
                        Dapatkan kunci API resmi Anda di dasbor <span class="font-mono text-[10px] text-sky-600 dark:text-sky-400">Resend &gt; API Keys</span>.
                    </p>
                </div>
            </div>
        @endif

        {{-- Identitas Pengirim Global (Berlaku untuk semua driver selain log) --}}
        <div class="mt-4 rounded-xl border border-neutral-200/80 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 p-4 space-y-3.5">
            <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Identitas Pengirim Global (Global Sender)</span>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1">
                    <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Alamat Pengirim Default (From Email)</label>
                    <input
                        type="email"
                        wire:model.live.debounce.300ms="services.mail_from_address"
                        placeholder="notifikasi@minios.local"
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-sky-500"
                    />
                </div>

                <div class="space-y-1">
                    <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Nama Pengirim (From Name)</label>
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
                <span>Kirim Surel Uji Coba</span>
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
