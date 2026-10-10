@if ($showUploadModal)
    <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 backdrop-blur-sm p-4 animate-in fade-in duration-150">
        <div class="w-full max-w-lg rounded-2xl border border-neutral-200 dark:border-white/10 bg-white dark:bg-[#2c2c2c] p-6 shadow-2xl text-left">
            <div class="flex items-center justify-between border-b border-neutral-200/80 dark:border-white/5 pb-4">
                <div class="flex items-center gap-2.5">
                    <div
                        class="flex size-9 items-center justify-center rounded-xl"
                        style="color: var(--accent-color, {{ $accent['hex'] }}); background-color: color-mix(in srgb, var(--accent-color, {{ $accent['hex'] }}) 15%, transparent);"
                    >
                        <flux:icon name="arrow-up-tray" class="size-5" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('modal_upload_title') }}</h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('modal_upload_desc') }}</p>
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="closeUploadModal"
                    class="text-neutral-400 hover:text-neutral-700 dark:hover:text-white transition-colors"
                >
                    <flux:icon name="x-mark" class="size-5" />
                </button>
            </div>

            {{-- Structure Guideline --}}
            <div class="mt-4 rounded-xl bg-neutral-100/70 dark:bg-white/3 border border-neutral-200/80 dark:border-white/5 p-3.5 text-xs text-neutral-600 dark:text-neutral-300">
                <p class="font-medium text-neutral-900 dark:text-white mb-1.5 flex items-center gap-1.5">
                    <flux:icon name="information-circle" class="size-3.5" style="color: var(--accent-color, {{ $accent['hex'] }});" />
                    <span>{{ $this->t('package_structure_title') }}</span>
                </p>
                <ul class="space-y-1 text-[11px] text-neutral-500 dark:text-neutral-400">
                    <li>• <span class="text-neutral-800 dark:text-neutral-200">{{ $this->t('structure_manifest') }}</span></li>
                    <li>• <span class="text-neutral-800 dark:text-neutral-200">{{ $this->t('structure_livewire') }}</span></li>
                    <li>• <span class="text-neutral-800 dark:text-neutral-200">{{ $this->t('structure_views') }}</span></li>
                </ul>
            </div>

            {{-- Dropzone / File Input Area --}}
            <div class="mt-4">
                <label class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-neutral-300 dark:border-white/15 bg-neutral-50/60 dark:bg-white/2 hover:border-[var(--accent-color,{{ $accent['hex'] }})] dark:hover:border-[var(--accent-color,{{ $accent['hex'] }})]/60 p-6 text-center transition-all cursor-pointer group">
                    <input
                        type="file"
                        wire:model="uploadFile"
                        accept=".zip,application/zip"
                        class="hidden"
                    />
                    <div class="flex size-11 items-center justify-center rounded-xl bg-neutral-200/60 dark:bg-white/5 text-neutral-500 group-hover:scale-105 transition-transform">
                        <flux:icon name="cloud-arrow-up" class="size-6" />
                    </div>
                    <span class="mt-3 text-xs font-semibold text-neutral-700 dark:text-neutral-200">
                        {{ $this->t('upload_click_select') }}
                    </span>
                    <span class="text-[11px] text-neutral-400 mt-0.5">
                        {{ $this->t('upload_max_size') }}
                    </span>
                </label>

                @error('uploadFile')
                    <p class="mt-2 text-xs text-rose-600 dark:text-rose-400 flex items-center gap-1">
                        <flux:icon name="exclamation-circle" class="size-3.5 shrink-0" />
                        <span>{{ $message }}</span>
                    </p>
                @enderror

                <div wire:loading wire:target="uploadFile" class="mt-2 text-xs text-indigo-600 dark:text-indigo-400 flex items-center gap-1.5">
                    <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span>{{ $this->t('uploading_zip') }}</span>
                </div>

                @if ($uploadFile)
                    <div class="mt-3 flex items-center justify-between rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200/80 dark:border-emerald-500/20 p-3 text-xs text-emerald-800 dark:text-emerald-300">
                        <div class="flex items-center gap-2 truncate">
                            <flux:icon name="check-circle" class="size-4 shrink-0 text-emerald-600" />
                            <span class="truncate font-medium">{{ $uploadFile->getClientOriginalName() }}</span>
                        </div>
                        <span class="text-[10px] text-emerald-600/80 shrink-0">{{ $this->t('ready_to_install') }}</span>
                    </div>
                @endif
            </div>

            {{-- Dialog Actions --}}
            <div class="mt-6 flex items-center justify-end gap-2 border-t border-neutral-200/80 dark:border-white/5 pt-4">
                <button
                    type="button"
                    wire:click="closeUploadModal"
                    class="rounded-xl border border-neutral-300 dark:border-white/10 bg-white dark:bg-[#323232] px-4 py-2 text-xs font-semibold text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-[#3c3c3c] transition-all"
                >
                    {{ $this->t('btn_cancel') }}
                </button>
                <button
                    type="button"
                    wire:click="installZip"
                    wire:loading.attr="disabled"
                    wire:target="installZip"
                    @if (! $uploadFile) disabled @endif
                    style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                    class="inline-flex items-center gap-1.5 rounded-xl px-4 py-2 text-xs font-bold text-white shadow-xs transition-all hover:brightness-110 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="installZip">{{ $this->t('btn_install_now') }}</span>
                    <span wire:loading wire:target="installZip" class="inline-flex items-center gap-1.5">
                        <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                        </svg>
                        <span>{{ $this->t('btn_installing') }}</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
@endif
