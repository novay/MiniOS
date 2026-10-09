<div
    x-data="{
        cm: null,
        cursorLine: 1,
        cursorCol: 1,
        activeModeName: 'Plain Text',
        isLoadingEditor: true,

        getModeInfo(filename) {
            if (!filename) return { mode: 'text/plain', label: 'Plain Text' };
            const ext = filename.split('.').pop().toLowerCase();
            switch (ext) {
                case 'js':
                case 'mjs':
                    return { mode: 'javascript', label: 'JavaScript' };
                case 'ts':
                    return { mode: 'text/typescript', label: 'TypeScript' };
                case 'json':
                    return { mode: { name: 'javascript', json: true }, label: 'JSON' };
                case 'php':
                    return { mode: 'application/x-httpd-php', label: 'PHP' };
                case 'html':
                case 'htm':
                case 'blade':
                    return { mode: 'htmlmixed', label: 'HTML' };
                case 'css':
                case 'scss':
                    return { mode: 'css', label: 'CSS' };
                case 'md':
                case 'markdown':
                    return { mode: 'markdown', label: 'Markdown' };
                case 'sql':
                    return { mode: 'sql', label: 'SQL' };
                case 'sh':
                case 'bash':
                case 'zsh':
                    return { mode: 'shell', label: 'Shell' };
                case 'yml':
                case 'yaml':
                    return { mode: 'yaml', label: 'YAML' };
                case 'py':
                    return { mode: 'python', label: 'Python' };
                case 'xml':
                case 'svg':
                    return { mode: 'xml', label: 'XML' };
                default:
                    return { mode: 'text/plain', label: 'Plain Text' };
            }
        },

        async loadScript(src) {
            return new Promise((resolve, reject) => {
                if (document.querySelector(`script[src='${src}']`)) {
                    resolve();
                    return;
                }
                const s = document.createElement('script');
                s.src = src;
                s.onload = resolve;
                s.onerror = reject;
                document.head.appendChild(s);
            });
        },

        loadCss(href) {
            if (document.querySelector(`link[href='${href}']`)) return;
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            document.head.appendChild(link);
        },

        resetEditor() {
            if (this.cm) {
                this.cm.setValue('');
                this.cm.clearHistory();
                this.cursorLine = 1;
                this.cursorCol = 1;
                setTimeout(() => this.cm.refresh(), 50);
            }
        },

        mountCodeMirror() {
            const targetEl = this.$refs.cmContainer;
            if (!targetEl || targetEl.children.length > 0) return;

            const modeInfo = this.getModeInfo($wire.fileName);
            this.activeModeName = modeInfo.label;

            this.cm = window.CodeMirror(targetEl, {
                value: $wire.content || '',
                mode: modeInfo.mode,
                theme: 'material-darker',
                lineNumbers: true,
                lineWrapping: $wire.wordWrap,
                autoCloseBrackets: true,
                matchBrackets: true,
                styleActiveLine: true,
                tabSize: 4,
                indentUnit: 4,
                extraKeys: {
                    'Ctrl-S': () => $wire.saveFile(),
                    'Cmd-S': () => $wire.saveFile(),
                    'Tab': (cm) => cm.replaceSelection('    ', 'end'),
                }
            });

            this.cm.on('change', (cm) => {
                const val = cm.getValue();
                if (val !== $wire.content) {
                    $wire.content = val;
                    $wire.isDirty = (val !== $wire.originalContent);
                }
            });

            this.cm.on('cursorActivity', (cm) => {
                const cur = cm.getCursor();
                this.cursorLine = cur.line + 1;
                this.cursorCol = cur.ch + 1;
            });

            setTimeout(() => {
                if (this.cm) this.cm.refresh();
            }, 50);
        },

        async initCodeMirror() {
            try {
                this.loadCss('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.css');
                this.loadCss('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/theme/material-darker.min.css');

                if (!window.CodeMirror) {
                    await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.js');
                    await Promise.all([
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/xml/xml.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/javascript/javascript.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/css/css.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/clike/clike.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/markdown/markdown.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/yaml/yaml.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/sql/sql.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/shell/shell.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/python/python.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/edit/matchbrackets.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/edit/closebrackets.min.js'),
                        this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/selection/active-line.min.js'),
                    ]);
                    await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/htmlmixed/htmlmixed.min.js');
                    await this.loadScript('https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/php/php.min.js');
                }

                this.mountCodeMirror();
                this.isLoadingEditor = false;

                // Event listener saat file baru dimuat dari server
                window.addEventListener('editor-file-loaded', (e) => {
                    const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
                    if (this.cm && detail) {
                        if (typeof detail.content === 'string') {
                            this.cm.setValue(detail.content);
                            this.cm.clearHistory();
                        }
                        if (detail.fileName) {
                            const info = this.getModeInfo(detail.fileName);
                            this.activeModeName = info.label;
                            this.cm.setOption('mode', info.mode);
                        }
                        setTimeout(() => this.cm.refresh(), 50);
                    }
                });

                // Event listener saat reset file baru dari server
                window.addEventListener('editor-content-reset', (e) => {
                    this.resetEditor();
                    const detail = Array.isArray(e.detail) ? e.detail[0] : e.detail;
                    if (detail?.fileName) {
                        const info = this.getModeInfo(detail.fileName);
                        this.activeModeName = info.label;
                        if (this.cm) this.cm.setOption('mode', info.mode);
                    }
                });

                $wire.$watch('content', (newVal) => {
                    if (this.cm && this.cm.getValue() !== newVal) {
                        this.cm.setValue(newVal || '');
                    }
                });

                $wire.$watch('fileName', (newName) => {
                    const info = this.getModeInfo(newName);
                    this.activeModeName = info.label;
                    if (this.cm) this.cm.setOption('mode', info.mode);
                });

                $wire.$watch('wordWrap', (wrap) => {
                    if (this.cm) this.cm.setOption('lineWrapping', wrap);
                });

                setTimeout(() => {
                    if (this.cm) this.cm.refresh();
                }, 100);

            } catch (e) {
                console.error('Failed to initialize CodeMirror:', e);
                this.isLoadingEditor = false;
            }
        }
    }"
    x-init="initCodeMirror()"
    @keydown.window.ctrl.s.prevent="$wire.saveFile()"
    @keydown.window.meta.s.prevent="$wire.saveFile()"
    class="flex h-full w-full flex-col bg-white dark:bg-[#1e1e1e] text-neutral-800 dark:text-neutral-100 font-sans select-none overflow-hidden relative"
    style="--accent-color: {{ $accent['hex'] }};"
>
    <style>
        .CodeMirror {
            height: 100% !important;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace !important;
            font-size: 13px !important;
            line-height: 1.6 !important;
            background: #1e1e1e !important;
            color: #eeffff !important;
        }
        .CodeMirror-gutters {
            background: #181818 !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
        }
        .CodeMirror-linenumber {
            color: #64748b !important;
            font-size: 11px !important;
            padding: 0 5px 0 3px !important;
        }
        .CodeMirror-activeline-background {
            background: rgba(255, 255, 255, 0.05) !important;
        }
        .CodeMirror-cursor {
            border-left: 2px solid #38bdf8 !important;
        }
        .CodeMirror-selected {
            background: rgba(56, 189, 248, 0.25) !important;
        }
        .CodeMirror-scroll {
            overflow: auto !important;
            background: #1e1e1e !important;
        }
    </style>

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
                @click="resetEditor()"
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
    {{-- EDITOR BODY & CODEMIRROR CONTAINER (ALWAYS DARK)          --}}
    {{-- ========================================================= --}}
    <main class="flex-1 flex overflow-hidden relative bg-[#1e1e1e] text-neutral-100">
        {{-- CodeMirror Mount Element with wire:ignore --}}
        <div wire:ignore x-ref="cmContainer" class="h-full w-full select-text overflow-hidden bg-[#1e1e1e]"></div>

        {{-- Loading Overlay --}}
        <div
            x-show="isLoadingEditor"
            class="absolute inset-0 z-20 flex items-center justify-center bg-[#1e1e1e]/90 backdrop-blur-xs transition-opacity"
        >
            <div class="flex items-center gap-2.5 text-xs text-neutral-400">
                <svg class="size-4 animate-spin text-sky-500" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span>Memuat editor kode...</span>
            </div>
        </div>
    </main>

    {{-- ========================================================= --}}
    {{-- BOTTOM STATUS BAR                                         --}}
    {{-- ========================================================= --}}
    <footer class="flex h-6 shrink-0 items-center justify-between border-t border-neutral-200/80 dark:border-white/10 bg-neutral-100/90 dark:bg-[#007acc]/90 text-neutral-600 dark:text-white px-3 text-[11px] select-none font-mono">
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
            <span class="inline-flex items-center gap-1 rounded bg-black/5 dark:bg-white/10 px-1.5 py-0.5 text-[10px] font-sans font-medium" x-text="activeModeName">Plain Text</span>
            <span class="hidden sm:inline">{{ $this->t('status_lines', ['lines' => $this->linesCount]) }}</span>
            <span class="hidden sm:inline">{{ $this->t('status_chars', ['chars' => $this->charsCount]) }}</span>
            <span>{{ $encoding }}</span>
        </div>
    </footer>
</div>
