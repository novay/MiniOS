@if ($showComposerModal)
    <div class="absolute inset-0 z-50 flex items-center justify-center bg-black/60 dark:bg-black/80 backdrop-blur-sm p-4 animate-in fade-in duration-150">
        <div class="w-full max-w-xl rounded-2xl border border-neutral-200 dark:border-white/10 bg-white dark:bg-[#2c2c2c] p-6 shadow-2xl text-left">
            <div class="flex items-center justify-between border-b border-neutral-200/80 dark:border-white/5 pb-4">
                <div class="flex items-center gap-2.5">
                    <div class="flex size-9 items-center justify-center rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                        <flux:icon name="archive-box" class="size-5" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('modal_composer_title') }}</h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            {{ $this->t('modal_composer_app', ['name' => $composerAppName]) }}
                        </p>
                    </div>
                </div>
                @if ($composerStatus !== 'running')
                    <button
                        type="button"
                        wire:click="closeComposerModal"
                        class="text-neutral-400 hover:text-neutral-700 dark:hover:text-white transition-colors"
                    >
                        <flux:icon name="x-mark" class="size-5" />
                    </button>
                @endif
            </div>

            {{-- Command Terminal Preview --}}
            <div class="mt-4 space-y-3">
                <div>
                    <span class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wider">{{ $this->t('composer_cmd_label') }}</span>
                    <div class="mt-1 flex items-center gap-2 rounded-xl bg-neutral-900 dark:bg-black/80 p-3 font-mono text-xs text-emerald-400 border border-neutral-800">
                        <span class="text-neutral-500">$</span>
                        <span class="select-all">{{ $composerCommand }}</span>
                    </div>
                </div>

                {{-- Output Terminal Window --}}
                @if ($composerStatus !== 'idle')
                    <div>
                        <span class="text-[11px] font-semibold text-neutral-500 uppercase tracking-wider">{{ $this->t('composer_output_label') }}</span>
                        <div class="mt-1 h-44 overflow-y-auto rounded-xl bg-neutral-950 p-3 font-mono text-[11px] text-neutral-300 border border-neutral-800 space-y-1">
                            @if ($composerStatus === 'running')
                                <div class="flex items-center gap-2 text-amber-400 animate-pulse">
                                    <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span>{{ $this->t('composer_running_hint') }}</span>
                                </div>
                            @endif
                            <pre class="whitespace-pre-wrap font-mono">{{ $composerOutput }}</pre>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Action Buttons --}}
            <div class="mt-6 flex items-center justify-between border-t border-neutral-200/80 dark:border-white/5 pt-4">
                <div class="text-xs">
                    @if ($composerStatus === 'running')
                        <span class="text-amber-600 dark:text-amber-400 font-medium flex items-center gap-1.5">
                            <svg class="size-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                            </svg>
                            <span>{{ $this->t('composer_installing') }}</span>
                        </span>
                    @elseif ($composerStatus === 'success')
                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-semibold">
                            <flux:icon name="check-circle" class="size-4" />
                            <span>{{ $this->t('composer_success') }}</span>
                        </span>
                    @elseif ($composerStatus === 'error')
                        <span class="inline-flex items-center gap-1 text-rose-600 dark:text-rose-400 font-semibold">
                            <flux:icon name="x-circle" class="size-4" />
                            <span>{{ $this->t('composer_error') }}</span>
                        </span>
                    @else
                        <span class="text-neutral-500 text-[11px]">{{ $this->t('composer_ready_hint') }}</span>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @if ($composerStatus !== 'running')
                        <button
                            type="button"
                            wire:click="closeComposerModal"
                            class="rounded-xl border border-neutral-300 dark:border-white/10 bg-white dark:bg-[#323232] px-4 py-1.5 text-xs font-semibold text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-[#3c3c3c] transition-all"
                        >
                            {{ $this->t('btn_close') }}
                        </button>
                    @endif

                    @if ($composerStatus === 'idle' || $composerStatus === 'error')
                        <button
                            type="button"
                            wire:click="runComposerInstall"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white px-4 py-1.5 text-xs font-bold shadow-sm transition-all disabled:opacity-50"
                        >
                            <flux:icon name="bolt" class="size-3.5" />
                            <span>{{ $this->t('btn_start_install') }}</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
