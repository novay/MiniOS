<div
    x-data="{
        cursorLine: 1,
        cursorCol: 1,
        updateCursor(el) {
            const val = el.value.substr(0, el.selectionStart);
            const lines = val.split('\n');
            this.cursorLine = lines.length;
            this.cursorCol = lines[lines.length - 1].length + 1;
        },
        handleTab(e) {
            const start = e.target.selectionStart;
            const end = e.target.selectionEnd;
            e.target.value = e.target.value.substring(0, start) + '    ' + e.target.value.substring(end);
            e.target.selectionStart = e.target.selectionEnd = start + 4;
            $wire.set('content', e.target.value);
            this.updateCursor(e.target);
        },
        syncScroll(source, target) {
            target.scrollTop = source.scrollTop;
        }
    }"
    @keydown.window.ctrl.s.prevent="$wire.saveFile()"
    @keydown.window.meta.s.prevent="$wire.saveFile()"
    class="flex h-full w-full flex-col bg-white dark:bg-[#1e1e1e] text-neutral-800 dark:text-neutral-100 font-sans select-none overflow-hidden relative"
    style="--accent-color: {{ $accent['hex'] }};"
>
    {{-- ========================================================= --}}
    {{-- TOP COMMAND BAR                                           --}}
    {{-- ========================================================= --}}
    <header class="flex h-11 shrink-0 items-center justify-between gap-3 border-b border-neutral-200/80 dark:border-white/10 bg-neutral-50/90 dark:bg-[#252526]/90 px-3.5 backdrop-blur-md">
        {{-- File Info & Dirty Indicator --}}
        <div class="flex items-center gap-2.5 min-w-0">
            <div class="flex size-6 items-center justify-center rounded-md bg-sky-500/10 text-sky-500 shrink-0">
                <flux:icon name="document-text" class="size-3.5" />
            </div>

            <div class="flex items-center gap-1.5 truncate">
                <span class="text-xs font-semibold truncate text-neutral-900 dark:text-white" title="{{ $fileName }}">
                    {{ $fileName }}
                </span>
                @if ($isDirty)
                    <span class="flex size-2 rounded-full bg-amber-500 animate-pulse shrink-0" title="{{ $this->t('status_unsaved') }}"></span>
                @endif
            </div>
        </div>

        {{-- Center / Right Action Buttons --}}
        <div class="flex items-center gap-1.5 shrink-0">
            {{-- Tombol Berkas Baru --}}
            <button
                type="button"
                wire:click="newFile"
                class="flex items-center gap-1 rounded-lg border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-white/5 px-2.5 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition shadow-2xs"
                title="{{ $this->t('btn_new') }}"
            >
                <flux:icon name="plus" class="size-3.5" />
                <span class="hidden sm:inline">{{ $this->t('btn_new') }}</span>
            </button>

            {{-- Tombol Simpan --}}
            <button
                type="button"
                wire:click="saveFile"
                class="flex items-center gap-1.5 rounded-lg px-3 py-1 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition"
                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                title="{{ $this->t('btn_save') }} (Ctrl+S / Cmd+S)"
            >
                <flux:icon name="check" class="size-3.5 stroke-[2.5]" />
                <span>{{ $this->t('btn_save') }}</span>
            </button>

            <div class="h-4 w-px bg-neutral-300/80 dark:bg-white/10 mx-0.5"></div>

            {{-- Toggle Word Wrap --}}
            <button
                type="button"
                wire:click="toggleWordWrap"
                class="flex size-7 items-center justify-center rounded-lg border border-neutral-300/80 dark:border-white/10 transition {{ $wordWrap ? 'bg-neutral-200/90 dark:bg-white/15 text-neutral-900 dark:text-white font-semibold' : 'bg-white dark:bg-white/5 text-neutral-600 dark:text-neutral-400 hover:bg-neutral-100 dark:hover:bg-white/10' }}"
                title="{{ $this->t('btn_word_wrap') }}"
            >
                <flux:icon name="bars-3-bottom-left" class="size-3.5" />
            </button>

            {{-- Tombol Unduh --}}
            <button
                type="button"
                wire:click="download"
                class="flex size-7 items-center justify-center rounded-lg border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-white/5 text-neutral-600 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-white/10 transition"
                title="{{ $this->t('btn_download') }}"
            >
                <flux:icon name="arrow-down-tray" class="size-3.5" />
            </button>
        </div>
    </header>

    {{-- ========================================================= --}}
    {{-- EDITOR BODY & LINE NUMBERS                                --}}
    {{-- ========================================================= --}}
    <main class="flex-1 flex overflow-hidden relative bg-white dark:bg-[#1e1e1e]">
        {{-- Gutter Line Numbers --}}
        <div
            x-ref="gutter"
            class="w-11 sm:w-12 shrink-0 border-r border-neutral-200 dark:border-white/5 bg-neutral-50/70 dark:bg-[#1e1e1e] py-3 text-right pr-2.5 font-mono text-xs text-neutral-400 dark:text-neutral-600 select-none overflow-hidden leading-6"
        >
            @for ($i = 1; $i <= max($this->linesCount, 1); $i++)
                <div class="h-6">{{ $i }}</div>
            @endfor
        </div>

        {{-- Textarea Editor --}}
        <div class="flex-1 relative h-full overflow-hidden">
            <textarea
                x-ref="editor"
                wire:model.live.debounce.300ms="content"
                @scroll="syncScroll($event.target, $refs.gutter)"
                @keyup="updateCursor($event.target)"
                @click="updateCursor($event.target)"
                @keydown.tab.prevent="handleTab($event)"
                spellcheck="false"
                class="h-full w-full p-3 font-mono text-xs text-neutral-900 dark:text-neutral-100 bg-transparent border-0 focus:ring-0 focus:outline-none resize-none leading-6 select-text overflow-auto scrollbar-thin {{ $wordWrap ? 'whitespace-pre-wrap' : 'whitespace-pre overflow-x-auto' }}"
                placeholder="{{ $this->t('placeholder_editor') }}"
            ></textarea>
        </div>
    </main>

    {{-- ========================================================= --}}
    {{-- BOTTOM STATUS BAR                                         --}}
    {{-- ========================================================= --}}
    <footer class="flex h-6 shrink-0 items-center justify-between border-t border-neutral-200/80 dark:border-white/10 bg-neutral-100/80 dark:bg-[#007acc]/90 text-neutral-600 dark:text-white px-3 text-[11px] select-none font-mono">
        <div class="flex items-center gap-3 truncate">
            @if ($filePath)
                <span class="truncate">storage/{{ $filePath }}</span>
            @else
                <span>{{ $this->t('untitled_file') }}</span>
            @endif
            @if ($isDirty)
                <span class="text-amber-600 dark:text-amber-300 font-sans font-medium text-[10px] uppercase">
                    [{{ $this->t('status_unsaved') }}]
                </span>
            @endif
        </div>

        <div class="flex items-center gap-4 shrink-0">
            <span x-text="`Ln ${cursorLine}, Col ${cursorCol}`">Ln 1, Col 1</span>
            <span class="hidden sm:inline">{{ $this->t('status_lines', ['lines' => $this->linesCount]) }}</span>
            <span class="hidden sm:inline">{{ $this->t('status_chars', ['chars' => $this->charsCount]) }}</span>
            <span>{{ $encoding }}</span>
        </div>
    </footer>
</div>
