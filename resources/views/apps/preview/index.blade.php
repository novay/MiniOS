<div
    class="flex h-full w-full flex-col bg-[#f5f5f5] dark:bg-[#1f1f1f] text-neutral-800 dark:text-neutral-100 font-sans select-none overflow-hidden relative"
    style="--accent-color: {{ $accent['hex'] }};"
>
    {{-- ========================================================= --}}
    {{-- TOP COMMAND BAR & CONTROLS (WINDOWS 11 / MACOS STYLE)     --}}
    {{-- ========================================================= --}}
    <header class="flex h-11 shrink-0 items-center justify-between gap-3 border-b border-neutral-200/80 dark:border-white/10 bg-white/80 dark:bg-[#262626]/80 px-3.5 backdrop-blur-md">
        {{-- File Info & Title --}}
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="flex size-6 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                @if ($fileType === 'pdf')
                    <flux:icon name="document" class="size-3.5 text-rose-500" />
                @else
                    <flux:icon name="photo" class="size-3.5 text-emerald-500" />
                @endif
            </div>

            @if ($fileName)
                <div class="flex items-center gap-2 truncate">
                    <span class="text-xs font-semibold truncate text-neutral-900 dark:text-white" title="{{ $fileName }}">
                        {{ $fileName }}
                    </span>
                    <span class="rounded bg-neutral-200/80 dark:bg-white/10 px-1.5 py-0.2 text-[10px] font-mono uppercase tracking-wider text-neutral-600 dark:text-neutral-300 shrink-0">
                        {{ $extension }}
                    </span>
                    @if ($fileSize)
                        <span class="text-[11px] text-neutral-400 dark:text-neutral-500 hidden sm:inline shrink-0">
                            ({{ $fileSize }})
                        </span>
                    @endif
                </div>
            @else
                <span class="text-xs font-semibold text-neutral-600 dark:text-neutral-400">
                    {{ $this->t('app_title') }}
                </span>
            @endif
        </div>

        {{-- Center Toolbar Tools (Hanya jika gambar) --}}
        @if ($fileType === 'image')
            <div class="flex items-center gap-1 rounded-lg border border-neutral-200/90 dark:border-white/10 bg-neutral-100/70 dark:bg-white/5 p-0.5">
                {{-- Zoom Out --}}
                <button
                    type="button"
                    wire:click="zoomOut"
                    @disabled($zoom <= 25)
                    class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-white dark:hover:bg-white/10 hover:text-neutral-900 dark:hover:text-white transition disabled:opacity-40"
                    title="{{ $this->t('btn_zoom_out') }}"
                >
                    <flux:icon name="minus" class="size-3.5" />
                </button>

                {{-- Zoom Value Badge (Click to reset) --}}
                <button
                    type="button"
                    wire:click="resetZoom"
                    class="px-2 py-0.5 text-[11px] font-mono font-medium text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-white rounded transition"
                    title="{{ $this->t('btn_reset_zoom') }}"
                >
                    {{ $zoom }}%
                </button>

                {{-- Zoom In --}}
                <button
                    type="button"
                    wire:click="zoomIn"
                    @disabled($zoom >= 300)
                    class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-white dark:hover:bg-white/10 hover:text-neutral-900 dark:hover:text-white transition disabled:opacity-40"
                    title="{{ $this->t('btn_zoom_in') }}"
                >
                    <flux:icon name="plus" class="size-3.5" />
                </button>

                <div class="h-4 w-px bg-neutral-300/80 dark:bg-white/10 mx-0.5"></div>

                {{-- Fit to Window Toggle --}}
                <button
                    type="button"
                    wire:click="toggleFitToWindow"
                    class="flex size-7 items-center justify-center rounded-md transition {{ $fitToWindow ? 'bg-white dark:bg-white/15 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-300 hover:bg-white dark:hover:bg-white/10' }}"
                    title="{{ $this->t('btn_fit_window') }}"
                >
                    <flux:icon :name="$fitToWindow ? 'arrows-pointing-in' : 'arrows-pointing-out'" class="size-3.5" />
                </button>

                <div class="h-4 w-px bg-neutral-300/80 dark:bg-white/10 mx-0.5"></div>

                {{-- Rotate Left --}}
                <button
                    type="button"
                    wire:click="rotateLeft"
                    class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-white dark:hover:bg-white/10 hover:text-neutral-900 dark:hover:text-white transition"
                    title="{{ $this->t('btn_rotate_left') }}"
                >
                    <flux:icon name="arrow-uturn-left" class="size-3.5" />
                </button>

                {{-- Rotate Right --}}
                <button
                    type="button"
                    wire:click="rotateRight"
                    class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-white dark:hover:bg-white/10 hover:text-neutral-900 dark:hover:text-white transition"
                    title="{{ $this->t('btn_rotate_right') }}"
                >
                    <flux:icon name="arrow-uturn-right" class="size-3.5" />
                </button>
            </div>
        @endif

        {{-- Right Action Buttons --}}
        <div class="flex items-center gap-1.5 shrink-0">
            @if ($filePath)
                <button
                    type="button"
                    wire:click="download"
                    class="flex items-center gap-1.5 rounded-lg border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-white/5 px-2.5 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition shadow-2xs"
                    title="{{ $this->t('btn_download') }}"
                >
                    <flux:icon name="arrow-down-tray" class="size-3.5" />
                    <span class="hidden sm:inline">{{ $this->t('btn_download') }}</span>
                </button>

                <button
                    type="button"
                    wire:click="closeFile"
                    class="flex size-7 items-center justify-center rounded-lg text-neutral-400 hover:text-neutral-700 dark:hover:text-white hover:bg-neutral-200/70 dark:hover:bg-white/10 transition"
                    title="{{ $this->t('btn_close_file') }}"
                >
                    <flux:icon name="x-mark" class="size-4" />
                </button>
            @endif
        </div>
    </header>

    {{-- ========================================================= --}}
    {{-- WORKSPACE CANVAS VIEWER                                   --}}
    {{-- ========================================================= --}}
    <main class="flex-1 overflow-auto flex items-center justify-center relative bg-[#ececec] dark:bg-[#171717] p-4 sm:p-6 select-none">
        @if ($fileType === 'image' && $fileData)
            {{-- Image Canvas with Subtle Checkerboard Pattern --}}
            <div class="h-full w-full flex items-center justify-center overflow-auto rounded-xl">
                <div class="relative transition-transform duration-200 ease-out flex items-center justify-center max-h-full max-w-full"
                    style="transform: rotate({{ $rotation }}deg) scale({{ $zoom / 100 }});"
                >
                    <img
                        src="{{ $fileData }}"
                        alt="{{ $fileName }}"
                        class="max-h-[75vh] max-w-full rounded-lg object-contain shadow-2xl transition-all pointer-events-none select-none {{ $fitToWindow ? 'max-h-[72vh] max-w-full' : '' }}"
                    />
                </div>
            </div>
        @elseif ($fileType === 'pdf' && $fileData)
            {{-- PDF Viewer Container --}}
            <div class="h-full w-full flex flex-col rounded-xl overflow-hidden border border-neutral-300/80 dark:border-white/10 bg-white shadow-xl">
                <iframe
                    src="{{ $fileData }}"
                    class="h-full w-full border-none"
                    title="{{ $fileName }}"
                ></iframe>
            </div>
        @else
            {{-- Empty State (No File Opened) --}}
            <div class="flex h-full w-full flex-col items-center justify-center text-center p-8 space-y-3.5">
                <div class="flex size-16 items-center justify-center rounded-2xl bg-neutral-200/70 dark:bg-white/5 border border-neutral-300/80 dark:border-white/10 text-neutral-400 dark:text-neutral-500 shadow-sm">
                    <flux:icon name="photo" class="size-8" />
                </div>
                <div class="max-w-sm space-y-1">
                    <h2 class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                        {{ $this->t('empty_title') }}
                    </h2>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 leading-relaxed">
                        {{ $this->t('empty_desc') }}
                    </p>
                </div>
            </div>
        @endif
    </main>

    {{-- ========================================================= --}}
    {{-- BOTTOM STATUS BAR                                         --}}
    {{-- ========================================================= --}}
    @if ($filePath)
        <footer class="flex h-6 shrink-0 items-center justify-between border-t border-neutral-200/80 dark:border-white/10 bg-white/70 dark:bg-[#202020]/70 px-3 text-[11px] text-neutral-500 dark:text-neutral-400 select-none">
            <div class="flex items-center gap-3 truncate">
                <span class="truncate font-mono">storage/{{ $filePath }}</span>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                @if ($fileType === 'image')
                    <span>{{ $this->t('info_zoom', ['zoom' => $zoom]) }}</span>
                    @if ($rotation > 0)
                        <span>{{ $this->t('info_rotation', ['deg' => $rotation]) }}</span>
                    @endif
                @endif
                <span>{{ $fileSize }}</span>
            </div>
        </footer>
    @endif
</div>
