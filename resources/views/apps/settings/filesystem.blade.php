<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
    <div class="space-y-0.5">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="#" class="text-xs">Pengaturan</flux:breadcrumbs.item>
            <flux:breadcrumbs.item class="text-xs">Filesystem</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <h1 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-white">
            Filesystem (Storage)
        </h1>
        <p class="mt-1 text-xs text-neutral-500 dark:text-neutral-400">
            Pengaturan disk penyimpanan berkas default.
        </p>
    </div>

    <button
        type="button"
        wire:click="resetCategory('filesystem')"
        class="self-start sm:self-auto flex items-center gap-2 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
    >
        <flux:icon name="arrow-path" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
        <span>Reset</span>
    </button>
</div>

<div class="mt-6 flex flex-col gap-6 max-w-3xl">
    <div class="rounded-xl bg-white dark:bg-[#2b2b2b]/70 border border-neutral-200/90 dark:border-white/5 p-4 sm:p-5 shadow-2xs space-y-4">
        <div class="flex items-start gap-3">
            <div class="flex size-9 items-center justify-center rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400 shrink-0 mt-0.5">
                <flux:icon name="server" class="size-5" />
            </div>
            <div>
                <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">Penyimpanan Berkas (Filesystem)</h2>
                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-0.5">
                    Menentukan disk default yang digunakan.
                </p>
            </div>
        </div>

        {{-- Driver Storage Options --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3 pt-1">
            {{-- Local Disk --}}
            @php $isLocal = ($services['storage_driver'] ?? 'local') === 'local'; @endphp
            <div
                wire:click="$set('services.storage_driver', 'local')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isLocal ? 'border-teal-500 bg-teal-500/5 ring-1 ring-teal-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">Lokal (Privat)</span>
                    <span class="size-2 rounded-full {{ $isLocal ? 'bg-teal-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    Direktori <code class="font-mono text-[10px]">storage/app</code>. Terisolasi dari web publik.
                </p>
            </div>

            {{-- Public Disk --}}
            @php $isPublic = ($services['storage_driver'] ?? 'local') === 'public'; @endphp
            <div
                wire:click="$set('services.storage_driver', 'public')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isPublic ? 'border-teal-500 bg-teal-500/5 ring-1 ring-teal-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">Publik (Web URL)</span>
                    <span class="size-2 rounded-full {{ $isPublic ? 'bg-teal-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    Direktori <code class="font-mono text-[10px]">storage/app/public</code>. Untuk gambar &amp; media terbuka.
                </p>
            </div>

            {{-- S3 Object Storage --}}
            @php $isS3 = ($services['storage_driver'] ?? 'local') === 's3'; @endphp
            <div
                wire:click="$set('services.storage_driver', 's3')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isS3 ? 'border-teal-500 bg-teal-500/5 ring-1 ring-teal-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">S3 Storage</span>
                    <span class="size-2 rounded-full {{ $isS3 ? 'bg-teal-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    AWS S3, MinIO, Cloudflare R2, atau Spaces.
                </p>
            </div>

            {{-- BunnyCDN Storage --}}
            @php $isBunny = ($services['storage_driver'] ?? 'local') === 'bunny'; @endphp
            <div
                wire:click="$set('services.storage_driver', 'bunny')"
                class="cursor-pointer rounded-xl border p-3.5 transition-all {{ $isBunny ? 'border-teal-500 bg-teal-500/5 ring-1 ring-teal-500 shadow-2xs' : 'border-neutral-200/90 dark:border-white/10 hover:bg-neutral-50 dark:hover:bg-white/5' }}"
            >
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-900 dark:text-white">BunnyCDN</span>
                    <span class="size-2 rounded-full {{ $isBunny ? 'bg-teal-500' : 'bg-transparent' }}"></span>
                </div>
                <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-1 leading-relaxed">
                    Edge Storage &amp; Global CDN dari Bunny.net.
                </p>
            </div>
        </div>

        {{-- Parameter Konfigurasi S3 (Muncul jika S3 dipilih) --}}
        @if (($services['storage_driver'] ?? 'local') === 's3')
            <div class="mt-4 rounded-xl border border-neutral-200/80 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 p-4 space-y-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Kredensial S3-Compatible Storage</span>
                    <span class="text-[10px] text-neutral-500 font-mono">Dukungan: AWS • R2 • MinIO • Spaces</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Bucket Name</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.s3_bucket"
                            placeholder="nama-bucket-anda"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Region</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.s3_region"
                            placeholder="us-east-1 atau auto"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Access Key (Key ID)</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.s3_key"
                            placeholder="AKIAIOSFODNN7EXAMPLE"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-teal-500 font-mono"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Secret Key</label>
                        <input
                            type="password"
                            wire:model.live.debounce.300ms="services.s3_secret"
                            placeholder="wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-teal-500 font-mono"
                        />
                    </div>

                    <div class="space-y-1 sm:col-span-2">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Custom Endpoint (Opsional untuk MinIO / Cloudflare R2)</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.s3_endpoint"
                            placeholder="https://<account-id>.r2.cloudflarestorage.com atau http://127.0.0.1:9000"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-teal-500 font-mono"
                        />
                    </div>
                </div>

                <div class="flex items-center justify-between pt-1">
                    <div class="space-y-0.5">
                        <span class="text-xs font-medium text-neutral-800 dark:text-neutral-200">Gunakan Path Style Endpoint</span>
                        <p class="text-[11px] text-neutral-500">Wajib diaktifkan saat menggunakan server MinIO lokal.</p>
                    </div>
                    <flux:switch wire:model.live="services.s3_use_path_style" />
                </div>
            </div>
        @endif

        {{-- Parameter Konfigurasi BunnyCDN (Muncul jika Bunny dipilih) --}}
        @if (($services['storage_driver'] ?? 'local') === 'bunny')
            <div class="mt-4 rounded-xl border border-neutral-200/80 dark:border-white/10 bg-neutral-50/70 dark:bg-white/5 p-4 space-y-3.5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Kredensial BunnyCDN Edge Storage</span>
                    <span class="text-[10px] text-neutral-500 font-mono">Powered by Bunny.net</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Storage Zone Name</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.bunny_storage_zone"
                            placeholder="nama-storage-zone"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-teal-500"
                        />
                    </div>

                    <div class="space-y-1">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Storage Region</label>
                        <flux:select wire:model.live="services.bunny_region">
                            <option value="de">Falkenstein / Frankfurt (de - Default EU)</option>
                            <option value="uk">London (uk - United Kingdom)</option>
                            <option value="se">Stockholm (se - Sweden)</option>
                            <option value="ny">New York (ny - US East)</option>
                            <option value="la">Los Angeles (la - US West)</option>
                            <option value="sg">Singapore (sg - Asia)</option>
                            <option value="syd">Sydney (syd - Oceania)</option>
                            <option value="br">São Paulo (br - Brazil)</option>
                            <option value="jh">Johannesburg (jh - Africa)</option>
                        </flux:select>
                    </div>

                    <div class="space-y-1 sm:col-span-2">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Storage Password / API Access Key</label>
                        <input
                            type="password"
                            wire:model.live.debounce.300ms="services.bunny_api_key"
                            placeholder="Password Storage Zone dari dasbor Bunny.net"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-teal-500 font-mono"
                        />
                    </div>

                    <div class="space-y-1 sm:col-span-2">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Pull Zone URL (Opsional / Akses Publik CDN)</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="services.bunny_pull_zone"
                            placeholder="https://myzone.b-cdn.net"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-teal-500 font-mono"
                        />
                    </div>

                    <div class="space-y-1 sm:col-span-2">
                        <label class="text-[11px] font-medium text-neutral-700 dark:text-neutral-300">Token Auth Key (Opsional - URL Signing Key)</label>
                        <input
                            type="password"
                            wire:model.live.debounce.300ms="services.bunny_token_auth_key"
                            placeholder="Token Authentication Key jika Pull Zone dilindungi"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white placeholder-neutral-400 focus:outline-none focus:ring-1 focus:ring-teal-500 font-mono"
                        />
                    </div>
                </div>
            </div>
        @endif

        {{-- Test Storage Connection Action --}}
        <div class="pt-2 flex items-center justify-between flex-wrap gap-2">
            <button
                type="button"
                wire:click="testStorageConnection"
                wire:loading.attr="disabled"
                class="flex items-center gap-2 rounded-lg border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3.5 py-1.5 text-xs font-medium text-neutral-800 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-white/5 active:scale-98 transition-all disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="testStorageConnection">
                    <flux:icon name="arrow-path" class="size-3.5 text-teal-600 dark:text-teal-400" />
                </span>
                <span wire:loading wire:target="testStorageConnection" class="inline-block animate-spin size-3.5 border-2 border-teal-600 border-t-transparent rounded-full"></span>
                <span>Uji Koneksi Penyimpanan</span>
            </button>

            @if ($storageTestStatus)
                <span class="text-xs text-emerald-600 dark:text-emerald-400 font-medium flex items-center gap-1.5">
                    <flux:icon name="check-circle" class="size-4" />
                    <span>{{ $storageTestStatus }}</span>
                </span>
            @endif

            @if ($storageTestError)
                <span class="text-xs text-rose-600 dark:text-rose-400 font-medium flex items-center gap-1.5">
                    <flux:icon name="exclamation-circle" class="size-4" />
                    <span>{{ $storageTestError }}</span>
                </span>
            @endif
        </div>
    </div>
</div>
