<div
    x-data="{
        showNewMenu: false,
        contextMenu: false,
        contextX: 0,
        contextY: 0,
        contextItem: null,
        openContextMenu(e, item) {
            this.contextX = Math.min(e.clientX, window.innerWidth - 220);
            this.contextY = Math.min(e.clientY, window.innerHeight - 260);
            this.contextItem = item;
            this.contextMenu = true;
        },
        closeContextMenu() {
            this.contextMenu = false;
            this.contextItem = null;
        }
    }"
    @click="closeContextMenu(); showNewMenu = false"
    @keydown.escape.window="closeContextMenu(); showNewMenu = false"
    @keydown.window.cmd.c="if ($el.closest('.active-window') || $el.contains(document.activeElement)) { if ($wire.selectedPath) $wire.copyItem(); }"
    @keydown.window.ctrl.c="if ($el.closest('.active-window') || $el.contains(document.activeElement)) { if ($wire.selectedPath) $wire.copyItem(); }"
    @keydown.window.cmd.x="if ($el.closest('.active-window') || $el.contains(document.activeElement)) { if ($wire.selectedPath) $wire.cutItem(); }"
    @keydown.window.ctrl.x="if ($el.closest('.active-window') || $el.contains(document.activeElement)) { if ($wire.selectedPath) $wire.cutItem(); }"
    @keydown.window.cmd.v="if ($el.closest('.active-window') || $el.contains(document.activeElement)) { if ($wire.clipboardPath) $wire.pasteItem(); }"
    @keydown.window.ctrl.v="if ($el.closest('.active-window') || $el.contains(document.activeElement)) { if ($wire.clipboardPath) $wire.pasteItem(); }"
    @trash-updated.window="
        const c = $event.detail?.count ?? $event.detail?.[0]?.count ?? (typeof $event.detail === 'number' ? $event.detail : null);
        if (c !== null && c !== undefined) {
            $wire.trashCount = Number(c);
        }
    "
    class="flex h-full min-h-125 w-full flex-col overflow-hidden bg-[#f3f3f3] dark:bg-[#202020] text-neutral-800 dark:text-neutral-100 font-sans select-none relative"
    style="--accent-color: {{ $accent['hex'] }};"
>
    {{-- ========================================================= --}}
    {{-- WINDOWS 11 TOP NAVIGATION & ADDRESS BAR                   --}}
    {{-- ========================================================= --}}
    <header class="flex shrink-0 items-center justify-between gap-3 border-b border-neutral-200/90 dark:border-white/5 bg-white/70 dark:bg-[#2b2b2b]/70 px-4 py-2.5 backdrop-blur-xl">
        {{-- History & Navigation Controls --}}
        <div class="flex items-center gap-1">
            {{-- Back Button --}}
            <button
                type="button"
                wire:click="goBack"
                title="{{ $this->t('btn_back') }}"
                @disabled(! $this->canGoBack)
                class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
                <flux:icon name="arrow-left" class="size-4" />
            </button>

            {{-- Forward Button --}}
            <button
                type="button"
                wire:click="goForward"
                title="{{ $this->t('btn_forward') }}"
                @disabled(! $this->canGoForward)
                class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
                <flux:icon name="arrow-right" class="size-4" />
            </button>

            {{-- Navigate Up Button --}}
            <button
                type="button"
                wire:click="navigateUp"
                title="{{ $this->t('btn_up') }}"
                @disabled(empty($currentPath))
                class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
                <flux:icon name="arrow-up" class="size-4" />
            </button>

            {{-- Refresh Button --}}
            <button
                type="button"
                wire:click="$refresh"
                title="{{ $this->t('btn_refresh') }}"
                class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 transition-colors"
            >
                <flux:icon name="arrow-path" class="size-3.5" />
            </button>
        </div>

        {{-- Windows 11 Breadcrumbs Address Bar --}}
        <div class="flex flex-1 items-center gap-1.5 min-w-0 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-2.5 py-1 text-xs shadow-2xs transition-all focus-within:ring-2 {{ $accent['ring'] }}">
            {{-- Lead Icon --}}
            <div class="flex items-center text-neutral-400 dark:text-neutral-500 shrink-0">
                @if (empty($currentPath))
                    <flux:icon name="computer-desktop" class="size-3.5" />
                @else
                    <flux:icon name="folder" class="size-3.5 text-amber-500" />
                @endif
            </div>

            {{-- Interactive Breadcrumb Trail --}}
            <nav class="flex flex-1 items-center gap-1 min-w-0 overflow-x-auto scrollbar-none">
                @foreach ($this->breadcrumbs as $index => $crumb)
                    @if ($index > 0)
                        <flux:icon name="chevron-right" class="size-2.5 text-neutral-400 dark:text-neutral-600 shrink-0" />
                    @endif
                    <button
                        type="button"
                        wire:click="navigate('{{ $crumb['path'] }}')"
                        class="rounded px-1.5 py-0.5 truncate font-medium transition-colors hover:bg-neutral-100 dark:hover:bg-white/10 {{ $crumb['path'] === $currentPath ? 'font-semibold text-neutral-900 dark:text-white' : 'text-neutral-600 dark:text-neutral-400' }}"
                    >
                        {{ $crumb['name'] === 'storage' ? $this->t('crumb_storage') : $crumb['name'] }}
                    </button>
                @endforeach
            </nav>
        </div>

        {{-- Windows 11 Fluent Search Box --}}
        <div class="relative w-44 sm:w-60 shrink-0">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                <flux:icon name="magnifying-glass" class="size-3.5 text-neutral-400 dark:text-neutral-500" />
            </div>
            <input
                type="text"
                wire:model.live.debounce.200ms="searchQuery"
                placeholder="{{ $this->t('search_placeholder') }}"
                class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] py-1 pl-8 pr-7 text-xs text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
            />
            @if ($searchQuery !== '')
                <button
                    type="button"
                    wire:click="$set('searchQuery', '')"
                    class="absolute inset-y-0 right-0 flex items-center pr-2 text-neutral-400 hover:text-neutral-700 dark:hover:text-white"
                    title="{{ $this->t('search_clear') }}"
                >
                    <flux:icon name="x-mark" class="size-3.5" />
                </button>
            @endif
        </div>
    </header>

    {{-- ========================================================= --}}
    {{-- WINDOWS 11 COMMAND BAR (RIBBON STRIP WITH ACTIONS)        --}}
    {{-- ========================================================= --}}
    <div class="relative z-30 flex shrink-0 items-center justify-between border-b border-neutral-200/90 dark:border-white/5 bg-[#f8f8f8]/90 dark:bg-[#262626]/80 px-4 py-1.5 text-xs backdrop-blur-md gap-3">
        <div class="flex items-center gap-1.5 flex-wrap">
            @if ($this->isInTrash)
                {{-- Tombol Kosongkan Tempat Sampah (Empty Trash) --}}
                <button
                    type="button"
                    wire:click="emptyTrash"
                    @disabled(count($this->items) === 0)
                    title="{{ $this->t('ctx_empty_trash') }}"
                    class="flex items-center gap-1.5 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-500/10 dark:hover:text-rose-400 hover:border-rose-300 dark:hover:border-rose-500/20 disabled:opacity-40 disabled:pointer-events-none transition-all active:scale-98"
                >
                    <flux:icon name="trash" class="size-3.5 text-rose-500" />
                    <span>{{ $this->t('ctx_empty_trash') }}</span>
                </button>
            @elseif (! $this->isSystemProtected)
                {{-- Tombol Baru (+ New Dropdown) --}}
                <div class="relative">
                    <button
                        type="button"
                        @click.stop="showNewMenu = !showNewMenu"
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="flex items-center gap-1.5 rounded-md px-3 py-1 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                    >
                        <flux:icon name="plus" class="size-3.5 stroke-[2.5]" />
                        <span>{{ $this->t('btn_new') }}</span>
                        <flux:icon name="chevron-down" class="size-3" />
                    </button>

                    {{-- Dropdown Menu Baru --}}
                    <div
                        x-show="showNewMenu"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute left-0 top-full mt-1.5 w-48 rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white/95 dark:bg-[#2b2b2b]/95 p-1.5 shadow-2xl backdrop-blur-xl z-50 space-y-0.5"
                        style="display: none;"
                    >
                        <button
                            type="button"
                            wire:click="openNewFolderModal"
                            @click="showNewMenu = false"
                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left text-xs text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                        >
                            <flux:icon name="folder-plus" class="size-4 text-amber-500" />
                            <span>{{ $this->t('menu_new_folder') }}</span>
                        </button>
                        <button
                            type="button"
                            wire:click="openNewFileModal"
                            @click="showNewMenu = false"
                            class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left text-xs text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                        >
                            <flux:icon name="document-plus" class="size-4 text-sky-500" />
                            <span>{{ $this->t('menu_new_file') }}</span>
                        </button>
                    </div>
                </div>

                {{-- Tombol Unggah (Upload) --}}
                <button
                    type="button"
                    wire:click="openUploadModal"
                    class="flex items-center gap-1.5 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all active:scale-98"
                >
                    <flux:icon name="arrow-up-tray" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                    <span>{{ $this->t('btn_upload') }}</span>
                </button>
            @endif

            {{-- Action Buttons if Item Selected --}}
            @if ($selectedPath)
                @if ($this->isInTrash || ! $this->isSystemProtected)
                    <div class="h-3.5 w-px bg-neutral-300/80 dark:bg-white/10 mx-1"></div>
                @endif

                @if (! $this->isInTrash)
                    {{-- Unduh File --}}
                    <button
                        type="button"
                        wire:click="downloadFile('{{ $selectedPath }}')"
                        title="{{ $this->t('btn_download') }}"
                        class="flex items-center gap-1.5 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-2.5 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all"
                    >
                        <flux:icon name="arrow-down-tray" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                        <span class="hidden sm:inline">{{ $this->t('btn_download') }}</span>
                    </button>

                    @if (! $this->isSystemProtected)
                        {{-- Ganti Nama --}}
                        <button
                            type="button"
                            wire:click="openRenameModal('{{ $selectedPath }}')"
                            title="{{ $this->t('btn_rename') }}"
                            class="flex items-center gap-1.5 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-2.5 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all"
                        >
                            <flux:icon name="pencil-square" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                            <span class="hidden sm:inline">{{ $this->t('btn_rename') }}</span>
                        </button>

                        {{-- Dropdown More (Salin, Potong, Pindahkan) --}}
                        <div class="relative" x-data="{ showMoreMenu: false }" @click.outside="showMoreMenu = false">
                            <button
                                type="button"
                                @click="showMoreMenu = !showMoreMenu"
                                title="{{ $this->t('btn_more') }}"
                                class="flex items-center gap-1.5 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-2.5 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all"
                            >
                                <flux:icon name="ellipsis-horizontal" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                                <span class="hidden sm:inline">{{ $this->t('btn_more') }}</span>
                                <flux:icon name="chevron-down" class="size-3 opacity-75 hidden sm:inline" />
                            </button>

                            <div
                                x-show="showMoreMenu"
                                x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95"
                                class="absolute left-0 top-full mt-1.5 w-44 rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white/95 dark:bg-[#242427]/95 p-1 shadow-xl backdrop-blur-xl z-50 select-none"
                                style="display: none;"
                            >
                                {{-- Salin (Copy) --}}
                                <button
                                    type="button"
                                    wire:click="copyItem"
                                    @click="showMoreMenu = false"
                                    class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-left text-xs text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                                >
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="document-duplicate" class="size-4 text-neutral-500 dark:text-neutral-400" />
                                        <span>{{ $this->t('btn_copy') }}</span>
                                    </div>
                                    <span class="text-[10px] text-neutral-400 font-mono">⌘C</span>
                                </button>

                                {{-- Potong (Cut) --}}
                                <button
                                    type="button"
                                    wire:click="cutItem"
                                    @click="showMoreMenu = false"
                                    class="flex w-full items-center justify-between rounded-lg px-2.5 py-1.5 text-left text-xs text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                                >
                                    <div class="flex items-center gap-2">
                                        <flux:icon name="scissors" class="size-4 text-neutral-500 dark:text-neutral-400" />
                                        <span>{{ $this->t('btn_cut') }}</span>
                                    </div>
                                    <span class="text-[10px] text-neutral-400 font-mono">⌘X</span>
                                </button>

                                <div class="my-1 border-t border-neutral-200/80 dark:border-white/10"></div>

                                {{-- Pindahkan (Move) --}}
                                <button
                                    type="button"
                                    wire:click="openMoveModal"
                                    @click="showMoreMenu = false"
                                    class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left text-xs text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                                >
                                    <flux:icon name="folder-arrow-down" class="size-4 text-indigo-500 dark:text-indigo-400" />
                                    <span>{{ $this->t('btn_move') }}</span>
                                </button>
                            </div>
                        </div>
                    @endif
                @endif

                {{-- Hapus (Hapus Permanen jika di Trash) --}}
                <button
                    type="button"
                    wire:click="openDeleteModal('{{ $selectedPath }}', false)"
                    title="{{ $this->isInTrash ? __('Hapus Permanen') : $this->t('btn_delete') }}"
                    class="flex items-center gap-1.5 rounded-md border border-rose-300/80 dark:border-rose-500/20 bg-rose-50/50 dark:bg-rose-500/10 px-2.5 py-1 text-xs font-medium text-rose-600 dark:text-rose-400 shadow-2xs hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-all"
                >
                    <flux:icon name="trash" class="size-3.5 text-rose-500" />
                    <span class="hidden sm:inline">{{ $this->isInTrash ? __('Hapus Permanen') : $this->t('btn_delete') }}</span>
                </button>

                {{-- Batal Seleksi --}}
                <button
                    type="button"
                    @click="$dispatch('close-details'); setTimeout(() => $wire.set('selectedPath', null), 180)"
                    title="{{ $this->t('btn_clear_selection') }}"
                    class="text-neutral-400 hover:text-neutral-700 dark:hover:text-white p-1"
                >
                    <flux:icon name="x-mark" class="size-3.5" />
                </button>
            @endif

            {{-- Tombol Tempel (Paste) jika ada di Clipboard --}}
            @if ($clipboardPath && ! $this->isInTrash)
                <div class="flex items-center gap-1 rounded-md border border-indigo-300/80 dark:border-indigo-500/30 bg-indigo-50/60 dark:bg-indigo-500/10 p-0.5 shadow-2xs">
                    <button
                        type="button"
                        wire:click="pasteItem"
                        title="{{ $this->t('btn_paste') }}"
                        class="flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100/70 dark:hover:bg-indigo-500/20 rounded transition-all"
                    >
                        <flux:icon name="clipboard-document" class="size-3.5 text-indigo-600 dark:text-indigo-400" />
                        <span>{{ $this->t('btn_paste') }}</span>
                        <span class="text-[10px] opacity-75 font-mono">({{ $clipboardMode === 'cut' ? $this->t('btn_cut') : $this->t('btn_copy') }}: {{ Str::limit($clipboardName, 12) }})</span>
                    </button>
                    <button
                        type="button"
                        wire:click="clearClipboard"
                        title="{{ $this->t('btn_cancel_clipboard') }}"
                        class="p-1 text-neutral-400 hover:text-rose-500 rounded transition-colors"
                    >
                        <flux:icon name="x-mark" class="size-3" />
                    </button>
                </div>
            @endif

            {{-- Divider --}}
            @if ($this->isInTrash || ! $this->isSystemProtected || $selectedPath)
                <div class="h-3.5 w-px bg-neutral-300/80 dark:bg-white/10 mx-1"></div>
            @endif

            {{-- Tampilan View Mode Toggle --}}
            <div class="flex items-center rounded-md border border-neutral-300/70 dark:border-white/10 bg-white/80 dark:bg-[#202020]/80 p-0.5 shadow-2xs">
                <button
                    type="button"
                    wire:click="setViewMode('grid')"
                    title="{{ $this->t('title_view_grid') }}"
                    class="flex size-6 items-center justify-center rounded transition-all {{ $viewMode === 'grid' ? 'bg-neutral-200/90 dark:bg-white/15 text-neutral-900 dark:text-white font-medium shadow-2xs' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-white' }}"
                >
                    <flux:icon name="squares-2x2" class="size-3.5" />
                </button>
                <button
                    type="button"
                    wire:click="setViewMode('list')"
                    title="{{ $this->t('title_view_list') }}"
                    class="flex size-6 items-center justify-center rounded transition-all {{ $viewMode === 'list' ? 'bg-neutral-200/90 dark:bg-white/15 text-neutral-900 dark:text-white font-medium shadow-2xs' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-white' }}"
                >
                    <flux:icon name="list-bullet" class="size-3.5" />
                </button>
            </div>

            {{-- Toggle Panel Detail --}}
            <button
                type="button"
                @click="if ($wire.selectedPath && $wire.showDetailsPanel) { $dispatch('close-details'); setTimeout(() => $wire.toggleDetailsPanel(), 200); } else { $wire.toggleDetailsPanel(); }"
                title="{{ $this->t('btn_toggle_details') }}"
                class="flex size-7 items-center justify-center rounded-md border border-neutral-300/70 dark:border-white/10 bg-white/80 dark:bg-[#202020]/80 transition-all {{ $showDetailsPanel ? 'bg-neutral-200/90 dark:bg-white/15 '.$accent['text'].' font-medium shadow-2xs' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-white' }}"
            >
                <flux:icon name="information-circle" class="size-4" />
            </button>
        </div>

        {{-- Current Path Pill --}}
        <div class="hidden sm:flex items-center gap-1.5 text-[13px] text-neutral-500 dark:text-neutral-400">
            {{-- Sort Options --}}
            <div class="flex items-center gap-1 text-[11px] text-neutral-600 dark:text-neutral-400">
                <span class="text-neutral-400 dark:text-neutral-500 hidden md:inline">{{ $this->t('lbl_sort_by') }}</span>
                <button
                    type="button"
                    wire:click="sort('name')"
                    class="flex items-center gap-1 rounded px-2 py-0.5 transition-colors hover:bg-neutral-200/70 dark:hover:bg-white/10 {{ $sortBy === 'name' ? 'font-semibold text-neutral-900 dark:text-white' : '' }}"
                >
                    <span>{{ $this->t('sort_name') }}</span>
                    @if ($sortBy === 'name')
                        <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                    @endif
                </button>

                <button
                    type="button"
                    wire:click="sort('updated_at')"
                    class="flex items-center gap-1 rounded px-2 py-0.5 transition-colors hover:bg-neutral-200/70 dark:hover:bg-white/10 {{ $sortBy === 'updated_at' ? 'font-semibold text-neutral-900 dark:text-white' : '' }}"
                >
                    <span>{{ $this->t('sort_date') }}</span>
                    @if ($sortBy === 'updated_at')
                        <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                    @endif
                </button>

                <button
                    type="button"
                    wire:click="sort('size')"
                    class="flex items-center gap-1 rounded px-2 py-0.5 transition-colors hover:bg-neutral-200/70 dark:hover:bg-white/10 {{ $sortBy === 'size' ? 'font-semibold text-neutral-900 dark:text-white' : '' }}"
                >
                    <span>{{ $this->t('sort_size') }}</span>
                    @if ($sortBy === 'size')
                        <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- EXPLORER BODY: NAVIGATION PANE + FILE VIEWER             --}}
    {{-- ========================================================= --}}
    <div class="flex flex-1 overflow-hidden">
        {{-- Windows 11 Navigation Pane (Left Sidebar) --}}
        <aside class="flex w-52 sm:w-60 shrink-0 flex-col border-r border-neutral-200/90 dark:border-white/5 bg-[#f8f8f8]/85 dark:bg-[#202020]/90 p-3 backdrop-blur-xl gap-4 text-xs overflow-y-auto">
            {{-- Quick Access Section --}}
            <div>
                <div class="px-2 text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500 mb-1">
                    {{ $this->t('section_quick_access') }}
                </div>
                <nav class="flex flex-col gap-0.5">
                    {{-- Storage Root --}}
                    @php $isRoot = $currentPath === ''; @endphp
                    <button
                        type="button"
                        wire:click="navigate('')"
                        class="group relative flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-left transition-all {{ $isRoot ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
                    >
                        @if ($isRoot)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 h-3.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                        @endif
                        <div class="flex size-5 items-center justify-center rounded text-neutral-700 dark:text-neutral-300 {{ $isRoot ? $accent['text'] : '' }}">
                            <flux:icon name="computer-desktop" class="size-4" />
                        </div>
                        <span class="truncate">{{ $this->t('nav_storage_root') }}</span>
                    </button>

                    {{-- App Data --}}
                    @php $isApp = str_starts_with($currentPath, 'app') && !str_starts_with($currentPath, 'app/public'); @endphp
                    <button
                        type="button"
                        wire:click="navigate('app')"
                        class="group relative flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-left transition-all {{ $isApp ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
                    >
                        @if ($isApp)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 h-3.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                        @endif
                        <div class="flex size-5 items-center justify-center rounded text-amber-500">
                            <flux:icon name="folder" class="size-4" />
                        </div>
                        <span class="truncate">{{ $this->t('nav_app_data') }}</span>
                    </button>

                    {{-- Public Storage --}}
                    @php $isPublic = str_starts_with($currentPath, 'app/public'); @endphp
                    <button
                        type="button"
                        wire:click="navigate('app/public')"
                        class="group relative flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-left transition-all {{ $isPublic ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
                    >
                        @if ($isPublic)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 h-3.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                        @endif
                        <div class="flex size-5 items-center justify-center rounded text-emerald-500">
                            <flux:icon name="globe-alt" class="size-4" />
                        </div>
                        <span class="truncate">{{ $this->t('nav_public_storage') }}</span>
                    </button>
                </nav>
            </div>

            {{-- Cloud Storage Section --}}
            <div>
                <div class="px-2 text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500 mb-1 flex items-center justify-between">
                    <span>{{ $this->t('section_cloud_storage') }}</span>
                </div>
                <nav class="flex flex-col gap-0.5">
                    @if ($this->cloudStorageInfo)
                        <button
                            type="button"
                            wire:click="openCloudStorageModal"
                            class="group relative flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-left transition-all text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white"
                        >
                            <div class="flex size-5 items-center justify-center rounded {{ $this->cloudStorageInfo['driver'] === 'bunny' ? 'text-amber-500' : 'text-sky-500' }}">
                                <flux:icon name="cloud" class="size-4" />
                            </div>
                            <div class="min-w-0 flex-1 truncate">
                                <div class="truncate font-medium text-neutral-800 dark:text-neutral-200">{{ $this->cloudStorageInfo['name'] }}</div>
                            </div>
                            <flux:icon name="information-circle" class="size-3.5 text-neutral-400 opacity-60 group-hover:opacity-100 transition-opacity" />
                        </button>
                    @else
                        <button
                            type="button"
                            wire:click="openCloudStorageModal"
                            class="group relative flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-left transition-all text-neutral-500 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-800 dark:hover:text-neutral-200"
                        >
                            <div class="flex size-5 items-center justify-center rounded text-neutral-400 dark:text-neutral-500">
                                <flux:icon name="cloud-arrow-up" class="size-4" />
                            </div>
                            <div class="min-w-0 flex-1 truncate">
                                <div class="truncate font-medium">S3 / BunnyCDN</div>
                            </div>
                        </button>
                    @endif
                </nav>
            </div>

            {{-- System & Cache Section --}}
            <div>
                <div class="px-2 text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500 mb-1">
                    {{ $this->t('section_system') }}
                </div>
                <nav class="flex flex-col gap-0.5">
                    {{-- Logs --}}
                    @php $isLogs = str_starts_with($currentPath, 'logs'); @endphp
                    <button
                        type="button"
                        wire:click="navigate('logs')"
                        class="group relative flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-left transition-all {{ $isLogs ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
                    >
                        @if ($isLogs)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 h-3.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                        @endif
                        <div class="flex size-5 items-center justify-center rounded text-rose-500">
                            <flux:icon name="document-text" class="size-4" />
                        </div>
                        <span class="truncate">{{ $this->t('nav_system_logs') }}</span>
                    </button>

                    {{-- Framework Cache --}}
                    @php $isFramework = str_starts_with($currentPath, 'framework'); @endphp
                    <button
                        type="button"
                        wire:click="navigate('framework')"
                        class="group relative flex items-center gap-2.5 rounded-md px-2.5 py-1.5 text-left transition-all {{ $isFramework ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
                    >
                        @if ($isFramework)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 h-3.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                        @endif
                        <div class="flex size-5 items-center justify-center rounded text-indigo-500">
                            <flux:icon name="circle-stack" class="size-4" />
                        </div>
                        <span class="truncate">{{ $this->t('nav_framework_cache') }}</span>
                    </button>

                    {{-- Trash Can Nav Item --}}
                    <button
                        type="button"
                        wire:click="navigate('.trash')"
                        class="group relative flex items-center justify-between rounded-md px-2.5 py-1.5 text-left transition-all {{ $this->isInTrash ? 'bg-white dark:bg-white/10 text-neutral-900 dark:text-white shadow-2xs font-semibold' : 'text-neutral-600 dark:text-neutral-400 hover:bg-black/[0.04] dark:hover:bg-white/5 hover:text-neutral-900 dark:hover:text-white' }}"
                    >
                        @if ($this->isInTrash)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 h-3.5 w-1 rounded-r-full" style="background-color: var(--accent-color, {{ $accent['hex'] }});"></span>
                        @endif
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="flex size-5 items-center justify-center rounded text-rose-500">
                                <flux:icon name="trash" class="size-4" />
                            </div>
                            <span class="truncate">{{ __('Trash') }}</span>
                        </div>
                        <span
                            x-show="$wire.trashCount > 0"
                            class="rounded-full bg-neutral-200 dark:bg-neutral-700 px-1.5 py-0.2 text-[10px] font-mono text-neutral-600 dark:text-neutral-300"
                            x-text="$wire.trashCount"
                        ></span>
                    </button>
                </nav>
            </div>

            {{-- Storage Drive Summary Card (Bottom Sidebar) --}}
            <div class="mt-auto rounded-xl bg-white/70 dark:bg-white/5 border border-neutral-200/90 dark:border-white/5 p-3 space-y-2 shadow-2xs">
                <div class="flex items-center gap-2">
                    <div class="flex size-6 items-center justify-center rounded-md bg-neutral-200/70 dark:bg-white/10 text-neutral-700 dark:text-neutral-300">
                        <flux:icon name="server" class="size-3.5" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="text-xs font-semibold truncate text-neutral-900 dark:text-white">{{ $this->t('card_local_storage') }}</div>
                        <div class="text-[10px] text-neutral-500 dark:text-neutral-400">{{ $this->t('card_local_desc') }}</div>
                    </div>
                </div>

                @if ($this->cloudStorageInfo)
                    <div class="pt-1.5 border-t border-neutral-200/60 dark:border-white/5 flex items-center justify-between text-[10px]">
                        <span class="text-neutral-500 dark:text-neutral-400 flex items-center gap-1 min-w-0">
                            <flux:icon name="cloud" class="size-3 shrink-0 {{ $this->cloudStorageInfo['driver'] === 'bunny' ? 'text-amber-500' : 'text-sky-500' }}" />
                            <span class="truncate">{{ $this->cloudStorageInfo['name'] }}</span>
                        </span>
                        @if ($this->cloudStorageInfo['is_active'])
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400 shrink-0">{{ $this->t('status_active') }}</span>
                        @else
                            <span class="text-neutral-400 shrink-0">{{ $this->t('status_available') }}</span>
                        @endif
                    </div>
                @else
                    <div class="w-full bg-neutral-200/80 dark:bg-white/10 h-1 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width: 35%; background-color: var(--accent-color, {{ $accent['hex'] }});"></div>
                    </div>
                @endif
            </div>
        </aside>

        {{-- Main File View Area --}}
        <main class="flex-1 bg-white dark:bg-[#191919] p-4 sm:p-5 overflow-y-auto transition-all duration-200" @contextmenu.prevent="openContextMenu($event, null)" @click.self="$dispatch('close-details'); setTimeout(() => $wire.set('selectedPath', null), 180)">
            @if ($this->isLocked)
                {{-- Windows 11 Security Access Prompt --}}
                <div class="flex h-full w-full flex-col items-center justify-center text-center p-8 space-y-4">
                    <div class="flex size-16 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-500 border border-rose-500/20 shadow-md">
                        <flux:icon name="lock-closed" class="size-8" />
                    </div>
                    <div class="max-w-xs space-y-1">
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-white">{{ $this->t('locked_title') }}</h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('locked_desc') }}</p>
                    </div>
                </div>
            @elseif (count($this->items) === 0)
                {{-- Windows 11 Empty Folder State --}}
                <div class="flex h-full w-full flex-col items-center justify-center text-center p-8 space-y-3">
                    <div class="flex size-14 items-center justify-center rounded-2xl bg-neutral-100 dark:bg-white/5 border border-neutral-200 dark:border-white/10 text-neutral-400 dark:text-neutral-500 shadow-2xs">
                        <flux:icon name="folder-open" class="size-7" />
                    </div>
                    <div class="max-w-xs space-y-0.5">
                        <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">
                            {{ $searchQuery !== '' ? $this->t('empty_search_title') : $this->t('empty_folder_title') }}
                        </p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            {{ $searchQuery !== '' ? $this->t('empty_search_desc', ['query' => $searchQuery]) : $this->t('empty_folder_desc') }}
                        </p>
                    </div>
                    @if ($searchQuery !== '')
                        <button
                            type="button"
                            wire:click="$set('searchQuery', '')"
                            class="inline-flex items-center gap-1 rounded-md bg-neutral-200/80 dark:bg-white/10 px-3 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-300 dark:hover:bg-white/15 transition-colors"
                        >
                            {{ $this->t('btn_clear_search_filter') }}
                        </button>
                    @endif
                </div>
            @else
                @if ($viewMode === 'grid')
                    {{-- ========================================================= --}}
                    {{-- GRID VIEW (WINDOWS 11 TILES / ICONS)                     --}}
                    {{-- ========================================================= --}}
                    <div class="grid grid-cols-[repeat(auto-fill,minmax(104px,1fr))] gap-2.5 sm:gap-3 transition-all duration-200">
                        @foreach ($this->items as $item)
                            @php
                                $isSelected = $selectedPath === $item['path'];
                            @endphp
                            <div
                                @contextmenu.prevent.stop="openContextMenu($event, { path: '{{ $item['path'] }}', name: '{{ addslashes($item['name']) }}', is_dir: {{ $item['is_dir'] ? 'true' : 'false' }} })"
                                wire:click="selectItem('{{ $item['path'] }}')"
                                @dblclick.stop="@if ($item['is_dir']) $wire.navigate('{{ $item['path'] }}') @else $wire.openFile('{{ $item['path'] }}') @endif"
                                class="group relative flex flex-col items-center gap-1.5 p-3 rounded-xl border transition-all text-center cursor-pointer select-none active:scale-98 {{ $isSelected ? ($accent['radio_card'] ?? 'border-indigo-500 bg-indigo-500/10 ring-1 ring-indigo-500') : 'border-transparent hover:border-neutral-200/80 dark:hover:border-white/10 hover:bg-neutral-100/70 dark:hover:bg-white/5' }}"
                                @if ($isSelected)
                                    style="border-color: var(--accent-color, {{ $accent['hex'] }}); background-color: color-mix(in srgb, var(--accent-color, {{ $accent['hex'] }}) 14%, transparent); box-shadow: 0 0 0 1px var(--accent-color, {{ $accent['hex'] }});"
                                @endif
                            >
                                {{-- 3-Dots Quick Action Button (Hover) --}}
                                <button
                                    type="button"
                                    @click.stop="openContextMenu($event, { path: '{{ $item['path'] }}', name: '{{ addslashes($item['name']) }}', is_dir: {{ $item['is_dir'] ? 'true' : 'false' }} })"
                                    class="absolute top-2 right-2 flex size-6 items-center justify-center rounded-md bg-white/80 dark:bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-neutral-200 dark:hover:bg-white/20 text-neutral-600 dark:text-neutral-300 shadow-2xs"
                                    title="{{ $this->t('title_options') }}"
                                >
                                    <flux:icon name="ellipsis-horizontal" class="size-3.5" />
                                </button>

                                @if ($item['is_dir'])
                                    {{-- Windows 11 Fluent 3D Folder Icon --}}
                                    <div
                                        class="relative flex size-13 items-center justify-center transition-transform group-hover:scale-105 pointer-events-none"
                                    >
                                        <svg class="size-12 drop-shadow-xs" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            {{-- Back Flap --}}
                                            <path d="M4 10C4 7.79086 5.79086 6 8 6H18.5858C19.6466 6 20.664 6.42143 21.4142 7.17157L24.8284 10.5858C25.5786 11.3359 26.596 11.7574 27.6569 11.7574H40C42.2091 11.7574 44 13.5482 44 15.7574V38C44 40.2091 42.2091 42 40 42H8C5.79086 42 4 40.2091 4 38V10Z" fill="#F1A21A"/>
                                            {{-- Sheet Inside --}}
                                            <rect x="8" y="11" width="32" height="14" rx="2" fill="#FFF2D6"/>
                                            {{-- Front Flap --}}
                                            <path d="M4 17C4 14.7909 5.79086 13 8 13H40C42.2091 13 44 14.7909 44 17V38C44 40.2091 42.2091 42 40 42H8C5.79086 42 4 40.2091 4 38V17Z" fill="url(#folder_grad_{{ $loop->index }})"/>
                                            <defs>
                                                <linearGradient id="folder_grad_{{ $loop->index }}" x1="24" y1="13" x2="24" y2="42" gradientUnits="userSpaceOnUse">
                                                    <stop stop-color="#FFD453"/>
                                                    <stop offset="1" stop-color="#F5B228"/>
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                @else
                                    {{-- Windows 11 Fluent File Icons --}}
                                    <div
                                        class="flex size-13 items-center justify-center transition-transform group-hover:scale-105 pointer-events-none"
                                    >
                                        @if (in_array($item['extension'], ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']))
                                            <div class="flex size-11 items-center justify-center rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-500 shadow-2xs">
                                                <flux:icon name="photo" class="size-6" />
                                            </div>
                                        @elseif ($item['extension'] === 'pdf')
                                            <div class="flex size-11 items-center justify-center rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-500 shadow-2xs">
                                                <flux:icon name="document" class="size-6" />
                                            </div>
                                        @elseif (in_array($item['extension'], ['zip', 'tar', 'gz', 'rar']))
                                            <div class="flex size-11 items-center justify-center rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-500 shadow-2xs">
                                                <flux:icon name="archive-box" class="size-6" />
                                            </div>
                                        @elseif (in_array($item['extension'], ['php', 'js', 'json', 'md', 'css', 'html', 'txt', 'log']))
                                            <div class="flex size-11 items-center justify-center rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-500 shadow-2xs">
                                                <flux:icon name="document-text" class="size-6" />
                                            </div>
                                        @else
                                            <div class="flex size-11 items-center justify-center rounded-xl bg-neutral-200/80 dark:bg-white/10 border border-neutral-300 dark:border-white/10 text-neutral-600 dark:text-neutral-300 shadow-2xs">
                                                <flux:icon name="document" class="size-6" />
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <div class="min-w-0 w-full space-y-0.5">
                                    <span class="block text-xs font-medium text-neutral-800 dark:text-neutral-200 group-hover:text-neutral-900 dark:group-hover:text-white truncate">
                                        {{ $item['name'] }}
                                    </span>
                                    <span class="block text-[11px] text-neutral-500 dark:text-neutral-400">
                                        {{ $item['size'] }}
                                    </span>
                                    @if ($searchQuery !== '' && !empty($item['location']))
                                        <span class="block text-[10px] text-neutral-400 dark:text-neutral-500 truncate font-mono" title="{{ $item['location'] }}">
                                            {{ $item['location'] }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- ========================================================= --}}
                    {{-- DETAILS VIEW (WINDOWS 11 DETAILS TABLE)                  --}}
                    {{-- ========================================================= --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs border-collapse">
                            <thead>
                                <tr class="border-b border-neutral-200 dark:border-white/10 text-[11px] font-semibold text-neutral-500 dark:text-neutral-400 select-none">
                                    <th class="py-2 px-3 hover:text-neutral-800 dark:hover:text-white cursor-pointer" wire:click="sort('name')">
                                        <div class="flex items-center gap-1">
                                            <span>{{ $this->t('col_name') }}</span>
                                            @if ($sortBy === 'name')
                                                <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                                            @endif
                                        </div>
                                    </th>
                                    <th class="py-2 px-3 hover:text-neutral-800 dark:hover:text-white cursor-pointer" wire:click="sort('updated_at')">
                                        <div class="flex items-center gap-1">
                                            <span>{{ $this->t('col_modified') }}</span>
                                            @if ($sortBy === 'updated_at')
                                                <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                                            @endif
                                        </div>
                                    </th>
                                    <th class="py-2 px-3">{{ $this->t('col_type') }}</th>
                                    <th class="py-2 px-3 hover:text-neutral-800 dark:hover:text-white cursor-pointer text-right" wire:click="sort('size')">
                                        <div class="flex items-center justify-end gap-1">
                                            <span>{{ $this->t('col_size') }}</span>
                                            @if ($sortBy === 'size')
                                                <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                                            @endif
                                        </div>
                                    </th>
                                    <th class="py-2 px-2 text-right w-10"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-neutral-100 dark:divide-white/5">
                                @foreach ($this->items as $item)
                                    @php
                                        $isSelected = $selectedPath === $item['path'];
                                    @endphp
                                    <tr
                                        @contextmenu.prevent.stop="openContextMenu($event, { path: '{{ $item['path'] }}', name: '{{ addslashes($item['name']) }}', is_dir: {{ $item['is_dir'] ? 'true' : 'false' }} })"
                                        wire:click="selectItem('{{ $item['path'] }}')"
                                        @dblclick.stop="@if ($item['is_dir']) $wire.navigate('{{ $item['path'] }}') @else $wire.openFile('{{ $item['path'] }}') @endif"
                                        class="group cursor-pointer transition-colors {{ $isSelected ? ($accent['selected_row'] ?? 'bg-indigo-500/10 dark:bg-indigo-500/15 ring-1 ring-inset ring-indigo-500/30 font-medium') : 'hover:bg-neutral-100/70 dark:hover:bg-white/5' }}"
                                        @if ($isSelected)
                                            style="background-color: color-mix(in srgb, var(--accent-color, {{ $accent['hex'] }}) 14%, transparent); box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--accent-color, {{ $accent['hex'] }}) 35%, transparent);"
                                        @endif
                                    >
                                        <td class="py-2 px-3 flex items-center gap-2.5">
                                            @if ($item['is_dir'])
                                                <flux:icon name="folder" class="size-4 text-amber-500 shrink-0" />
                                            @elseif (in_array($item['extension'], ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']))
                                                <flux:icon name="photo" class="size-4 text-emerald-500 shrink-0" />
                                            @elseif ($item['extension'] === 'pdf')
                                                <flux:icon name="document" class="size-4 text-rose-500 shrink-0" />
                                            @elseif (in_array($item['extension'], ['zip', 'tar', 'gz', 'rar']))
                                                <flux:icon name="archive-box" class="size-4 text-amber-500 shrink-0" />
                                            @elseif (in_array($item['extension'], ['php', 'js', 'json', 'md', 'css', 'html', 'txt', 'log']))
                                                <flux:icon name="document-text" class="size-4 text-sky-500 shrink-0" />
                                            @else
                                                <flux:icon name="document" class="size-4 text-neutral-400 shrink-0" />
                                            @endif
                                            <span class="text-neutral-900 dark:text-neutral-100 group-hover:text-neutral-900 dark:group-hover:text-white truncate">
                                                {{ $item['name'] }}
                                            </span>
                                            @if ($searchQuery !== '' && !empty($item['location']))
                                                <span class="text-[10px] text-neutral-400 dark:text-neutral-500 truncate font-mono">
                                                    ({{ $item['location'] }})
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2 px-3 text-neutral-500 dark:text-neutral-400 whitespace-nowrap">
                                            {{ $item['updated_at'] }}
                                        </td>
                                        <td class="py-2 px-3 text-neutral-500 dark:text-neutral-400 uppercase text-[10px] font-mono">
                                            {{ $item['extension'] }}
                                        </td>
                                        <td class="py-2 px-3 text-neutral-500 dark:text-neutral-400 text-right whitespace-nowrap">
                                            {{ $item['size'] }}
                                        </td>
                                        <td class="py-2 px-2 text-right">
                                            <button
                                                type="button"
                                                @click.stop="openContextMenu($event, { path: '{{ $item['path'] }}', name: '{{ addslashes($item['name']) }}', is_dir: {{ $item['is_dir'] ? 'true' : 'false' }} })"
                                                class="flex size-6 items-center justify-center rounded-md opacity-0 group-hover:opacity-100 transition-opacity hover:bg-neutral-200 dark:hover:bg-white/20 text-neutral-600 dark:text-neutral-300"
                                                title="Pilihan"
                                            >
                                                <flux:icon name="ellipsis-horizontal" class="size-3.5" />
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </main>

        {{-- Right Inspector / Details Pane (Windows 11 Style) --}}
        @if ($showDetailsPanel && $this->selectedItem)
            @php $item = $this->selectedItem; @endphp
            <aside
                wire:key="files-details-inspector"
                x-data="{
                    closing: false,
                    closeDetails() {
                        if (this.closing) return;
                        this.closing = true;
                        setTimeout(() => {
                            $wire.set('selectedPath', null);
                        }, 200);
                    }
                }"
                @close-details.window="closeDetails()"
                :class="closing ? 'translate-x-full opacity-0 pointer-events-none' : 'translate-x-0 opacity-100'"
                class="w-64 sm:w-72 shrink-0 border-l border-neutral-200/90 dark:border-white/5 bg-[#fafafa]/90 dark:bg-[#202020]/90 backdrop-blur-md flex flex-col overflow-y-auto select-none transition-all duration-200 ease-out transform animate-[detailsSlideIn_0.22s_cubic-bezier(0.16,1,0.3,1)]"
            >
                {{-- Panel Header --}}
                <div class="flex items-center justify-between px-4 py-3 border-b border-neutral-200/60 dark:border-white/5">
                    <span class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">{{ $this->t('panel_details_title') }}</span>
                    <button
                        type="button"
                        @click="closeDetails()"
                        class="p-1 rounded-md text-neutral-400 hover:text-neutral-700 dark:hover:text-white hover:bg-neutral-200/60 dark:hover:bg-white/10 transition-colors"
                        title="{{ $this->t('btn_close_panel') }}"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </div>

                <div class="p-4 flex flex-col items-center text-center space-y-3">
                    {{-- Large Preview / Icon --}}
                    <div class="size-24 rounded-xl flex items-center justify-center bg-white dark:bg-white/5 border border-neutral-200/80 dark:border-white/10 shadow-xs overflow-hidden">
                        @if (!empty($item['preview_thumb']))
                            <img src="{{ $item['preview_thumb'] }}" alt="{{ $item['name'] }}" class="h-full w-full object-cover">
                        @elseif ($item['is_dir'])
                            <svg class="size-16 drop-shadow-sm" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 10C4 7.79086 5.79086 6 8 6H18.5858C19.6466 6 20.664 6.42143 21.4142 7.17157L24.8284 10.5858C25.5786 11.3359 26.596 11.7574 27.6569 11.7574H40C42.2091 11.7574 44 13.5482 44 15.7574V38C44 40.2091 42.2091 42 40 42H8C5.79086 42 4 40.2091 4 38V10Z" fill="#F1A21A"/>
                                <rect x="8" y="11" width="32" height="14" rx="2" fill="#FFF2D6"/>
                                <path d="M4 17C4 14.7909 5.79086 13 8 13H40C42.2091 13 44 14.7909 44 17V38C44 40.2091 42.2091 42 40 42H8C5.79086 42 4 40.2091 4 38V17Z" fill="url(#panel_folder_grad)"/>
                                <defs>
                                    <linearGradient id="panel_folder_grad" x1="24" y1="13" x2="24" y2="42" gradientUnits="userSpaceOnUse">
                                        <stop stop-color="#FFD453"/>
                                        <stop offset="1" stop-color="#F5B228"/>
                                    </linearGradient>
                                </defs>
                            </svg>
                        @elseif (in_array($item['extension'], ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg']))
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-emerald-500/10 text-emerald-500">
                                <flux:icon name="photo" class="size-8" />
                            </div>
                        @elseif ($item['extension'] === 'pdf')
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-500">
                                <flux:icon name="document" class="size-8" />
                            </div>
                        @elseif (in_array($item['extension'], ['zip', 'tar', 'gz', 'rar']))
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-amber-500/10 text-amber-500">
                                <flux:icon name="archive-box" class="size-8" />
                            </div>
                        @elseif (in_array($item['extension'], ['php', 'js', 'json', 'md', 'css', 'html', 'txt', 'log']))
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-sky-500/10 text-sky-500">
                                <flux:icon name="document-text" class="size-8" />
                            </div>
                        @else
                            <div class="flex size-14 items-center justify-center rounded-2xl bg-neutral-200/80 dark:bg-white/10 text-neutral-600 dark:text-neutral-300">
                                <flux:icon name="document" class="size-8" />
                            </div>
                        @endif
                    </div>

                    {{-- Name --}}
                    <div class="w-full">
                        <div class="text-sm font-semibold text-neutral-900 dark:text-white break-words select-text" title="{{ $item['name'] }}">
                            {{ $item['name'] }}
                        </div>
                        <div class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">
                            {{ $item['is_dir'] ? $this->t('word_folder_type') : strtoupper($item['extension'] ?: 'File') }}
                        </div>
                    </div>

                    {{-- Primary Action Button --}}
                    <div class="w-full pt-1 flex gap-2">
                        @if ($item['is_dir'])
                            <button
                                type="button"
                                wire:click="navigate('{{ $item['path'] }}')"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-lg text-xs font-medium text-white shadow-xs transition-opacity hover:opacity-95"
                                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                            >
                                <flux:icon name="folder-open" class="size-3.5" />
                                <span>{{ $this->t('btn_open_folder') }}</span>
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="openFile('{{ $item['path'] }}')"
                                class="flex-1 inline-flex items-center justify-center gap-1.5 py-1.5 px-3 rounded-lg text-xs font-medium text-white shadow-xs transition-opacity hover:opacity-95"
                                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                            >
                                <flux:icon name="arrow-top-right-on-square" class="size-3.5" />
                                <span>{{ $this->t('btn_preview_file') }}</span>
                            </button>
                        @endif

                        @if (! $item['is_dir'] && ! $this->isInTrash)
                            <button
                                type="button"
                                wire:click="downloadFile('{{ $item['path'] }}')"
                                class="inline-flex items-center justify-center p-1.5 rounded-lg border border-neutral-300/80 dark:border-white/10 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/5 transition-colors"
                                title="{{ $this->t('btn_download') }}"
                            >
                                <flux:icon name="arrow-down-tray" class="size-4" />
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Property Details Table / List --}}
                <div class="flex-1 px-4 py-2 border-t border-neutral-200/60 dark:border-white/5 space-y-3 text-xs">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                        {{ $this->t('panel_details_title') }}
                    </div>

                    <div class="space-y-2.5">
                        <div class="flex flex-col gap-0.5">
                            <span class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ $this->t('lbl_details_type') }}</span>
                            <span class="font-medium text-neutral-800 dark:text-neutral-200 break-words">
                                {{ $item['is_dir'] ? $this->t('word_folder_type') : ($item['extension'] ? strtoupper($item['extension']) . ' File' : 'File') }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ $this->t('lbl_details_size') }}</span>
                            <span class="font-medium text-neutral-800 dark:text-neutral-200 font-mono text-[11px]">
                                {{ $item['size'] }}
                            </span>
                        </div>

                        <div class="flex flex-col gap-0.5">
                            <span class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ $this->t('lbl_details_location') }}</span>
                            <span class="font-medium text-neutral-800 dark:text-neutral-200 font-mono text-[11px] truncate select-text" title="{{ $item['path'] }}">
                                {{ $item['path'] }}
                            </span>
                        </div>

                        @if (isset($item['original_path']) && $item['original_path'])
                            <div class="flex flex-col gap-0.5">
                                <span class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ $this->t('lbl_details_original_path') }}</span>
                                <span class="font-medium text-neutral-800 dark:text-neutral-200 font-mono text-[11px] truncate select-text" title="{{ $item['original_path'] }}">
                                    {{ $item['original_path'] }}
                                </span>
                            </div>
                        @endif

                        <div class="flex flex-col gap-0.5">
                            <span class="text-[11px] text-neutral-500 dark:text-neutral-400">{{ $this->t('lbl_details_modified') }}</span>
                            <span class="font-medium text-neutral-800 dark:text-neutral-200 text-[11px]">
                                {{ $item['updated_at'] }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Trash Quick Actions if item is in trash --}}
                @if ($this->isInTrash)
                    <div class="p-4 border-t border-neutral-200/60 dark:border-white/5 flex gap-2">
                        <button
                            type="button"
                            wire:click="restoreTrashItem('{{ $item['path'] }}')"
                            class="flex-1 py-1.5 px-2.5 rounded-lg text-xs font-medium bg-neutral-200/80 dark:bg-white/10 hover:bg-neutral-300 dark:hover:bg-white/20 text-neutral-800 dark:text-neutral-200 transition-colors flex items-center justify-center gap-1.5"
                        >
                            <flux:icon name="arrow-uturn-left" class="size-3.5" />
                            <span>{{ $this->t('trash_restore_item') }}</span>
                        </button>
                        <button
                            type="button"
                            wire:click="deleteForever('{{ $item['path'] }}')"
                            class="py-1.5 px-2.5 rounded-lg text-xs font-medium bg-rose-500/10 hover:bg-rose-500/20 text-rose-600 dark:text-rose-400 transition-colors"
                            title="{{ $this->t('trash_delete_forever') }}"
                        >
                            <flux:icon name="trash" class="size-3.5" />
                        </button>
                    </div>
                @endif
            </aside>
        @endif
    </div>

    {{-- ========================================================= --}}
    {{-- FOOTER STATUS BAR (WINDOWS 11 STYLE)                      --}}
    {{-- ========================================================= --}}
    <footer class="flex shrink-0 items-center justify-between border-t border-neutral-200/90 dark:border-white/5 bg-[#f8f8f8]/90 dark:bg-[#202020]/90 px-4 py-1 text-xs text-neutral-500 dark:text-neutral-400 select-none">
        <div class="flex items-center gap-2">
            <span>{{ $this->t('footer_item_count', ['count' => count($this->items)]) }}</span>
            @if ($selectedPath)
                <span
                    class="rounded px-1.5 py-0.2 text-[10px] font-medium"
                    style="color: var(--accent-color, {{ $accent['hex'] }}); background-color: color-mix(in srgb, var(--accent-color, {{ $accent['hex'] }}) 14%, transparent);"
                >
                    {{ $this->t('status_selected_one') }}
                </span>
            @endif
            @if ($searchQuery !== '')
                <span class="rounded bg-neutral-200 dark:bg-white/10 px-1.5 py-0.2 text-[10px] text-neutral-700 dark:text-neutral-300">
                    {{ $this->t('status_search_results') }}
                </span>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <span class="hidden sm:inline font-mono text-[11px] text-neutral-400 dark:text-neutral-500">
                storage/{{ $currentPath }}
            </span>
            <div class="flex items-center gap-1 border-l border-neutral-300/80 dark:border-white/10 pl-2">
                <button
                    type="button"
                    wire:click="setViewMode('grid')"
                    title="Grid"
                    class="p-0.5 rounded hover:text-neutral-900 dark:hover:text-white {{ $viewMode === 'grid' ? $accent['text'] : '' }}"
                >
                    <flux:icon name="squares-2x2" class="size-3.5" />
                </button>
                <button
                    type="button"
                    wire:click="setViewMode('list')"
                    title="List"
                    class="p-0.5 rounded hover:text-neutral-900 dark:hover:text-white {{ $viewMode === 'list' ? $accent['text'] : '' }}"
                >
                    <flux:icon name="list-bullet" class="size-3.5" />
                </button>
                <button
                    type="button"
                    wire:click="toggleDetailsPanel"
                    title="{{ $this->t('btn_toggle_details') }}"
                    class="p-0.5 rounded hover:text-neutral-900 dark:hover:text-white {{ $showDetailsPanel ? $accent['text'] : '' }}"
                >
                    <flux:icon name="information-circle" class="size-3.5" />
                </button>
            </div>
        </div>
    </footer>

    {{-- ========================================================= --}}
    {{-- WINDOWS 11 RIGHT-CLICK CONTEXT MENU                       --}}
    {{-- ========================================================= --}}
    <div
        x-show="contextMenu"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :style="`left: ${contextX}px; top: ${contextY}px;`"
        class="fixed z-50 w-52 rounded-xl border border-neutral-200/90 dark:border-white/10 bg-white/95 dark:bg-[#2b2b2b]/95 p-1.5 shadow-2xl backdrop-blur-xl text-xs space-y-0.5"
        style="display: none;"
    >
        @if ($this->isInTrash)
            {{-- CONTEXT MENU KHUSUS TEMPAT SAMPAH (TRASH) --}}
            <div class="px-2 py-1 border-b border-neutral-200/80 dark:border-white/10 mb-1">
                <span class="block truncate font-semibold text-neutral-900 dark:text-white" x-text="contextItem?.name || '{{ __('Tempat Sampah') }}'"></span>
            </div>

            {{-- 1. Put back / Kembalikan (Jika item di-klik kanan) --}}
            <template x-if="contextItem">
                <button
                    type="button"
                    @click="$wire.restorePath(contextItem.path); closeContextMenu()"
                    class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                >
                    <flux:icon name="arrow-uturn-left" class="size-3.5 text-indigo-500 dark:text-indigo-400" />
                    <span>{{ $this->t('ctx_put_back') }}</span>
                </button>
            </template>

            {{-- 2. Delete Immediately / Hapus Segera (Jika item di-klik kanan) --}}
            <template x-if="contextItem">
                <button
                    type="button"
                    @click="$wire.openDeleteModal(contextItem.path, contextItem.is_dir); closeContextMenu()"
                    class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors"
                >
                    <flux:icon name="trash" class="size-3.5 text-rose-500" />
                    <span>{{ $this->t('ctx_delete_immediately') }}</span>
                </button>
            </template>

            {{-- Divider jika ada item --}}
            <template x-if="contextItem">
                <div class="h-px bg-neutral-200/80 dark:bg-white/10 my-1"></div>
            </template>

            {{-- 3. Empty Trash / Kosongkan Tempat Sampah --}}
            <button
                type="button"
                :disabled="!$wire.trashCount || $wire.trashCount <= 0"
                @click="$wire.emptyTrash(); closeContextMenu()"
                class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors disabled:opacity-40 disabled:pointer-events-none"
            >
                <flux:icon name="trash" class="size-3.5 text-rose-500" />
                <span>{{ $this->t('ctx_empty_trash') }}</span>
            </button>
        @else
            {{-- CONTEXT MENU STANDAR (NON-TRASH) --}}
            <template x-if="contextItem">
                <div class="space-y-0.5">
                    {{-- Item Info Header in Context Menu --}}
                    <div class="px-2 py-1 border-b border-neutral-200/80 dark:border-white/10 mb-1">
                        <span class="block truncate font-semibold text-neutral-900 dark:text-white" x-text="contextItem?.name"></span>
                    </div>

                    {{-- Buka / Pratinjau --}}
                    <button
                        type="button"
                        @click="if (contextItem?.is_dir) { $wire.navigate(contextItem.path); } else { $wire.openFile(contextItem.path); } closeContextMenu()"
                        class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors font-medium"
                    >
                        <flux:icon name="arrow-top-right-on-square" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                        <span x-text="contextItem?.is_dir ? '{{ $this->t('ctx_open_folder') }}' : '{{ $this->t('ctx_preview_file') }}'"></span>
                    </button>

                    @if (! $this->isSystemProtected)
                        {{-- Salin (Copy) --}}
                        <button
                            type="button"
                            @click="$wire.copyItem(contextItem.path); closeContextMenu()"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                        >
                            <flux:icon name="document-duplicate" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                            <span>{{ $this->t('ctx_copy') }}</span>
                        </button>

                        {{-- Potong (Cut) --}}
                        <button
                            type="button"
                            @click="$wire.cutItem(contextItem.path); closeContextMenu()"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                        >
                            <flux:icon name="scissors" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                            <span>{{ $this->t('ctx_cut') }}</span>
                        </button>

                        {{-- Pindahkan ke... (Move to) --}}
                        <button
                            type="button"
                            @click="$wire.openMoveModal(contextItem.path, contextItem.is_dir); closeContextMenu()"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                        >
                            <flux:icon name="folder-arrow-down" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                            <span>{{ $this->t('ctx_move_to') }}</span>
                        </button>
                    @endif

                    {{-- Unduh (Jika File) --}}
                    <template x-if="!contextItem?.is_dir">
                        <button
                            type="button"
                            @click="$wire.downloadFile(contextItem.path); closeContextMenu()"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                        >
                            <flux:icon name="arrow-down-tray" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                            <span>{{ $this->t('ctx_download_file') }}</span>
                        </button>
                    </template>

                    @if (! $this->isSystemProtected)
                        {{-- Ganti Nama --}}
                        <button
                            type="button"
                            @click="$wire.openRenameModal(contextItem.path); closeContextMenu()"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                        >
                            <flux:icon name="pencil-square" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                            <span>{{ $this->t('ctx_rename') }}</span>
                        </button>
                    @endif

                    {{-- Divider --}}
                    <div class="h-px bg-neutral-200/80 dark:bg-white/10 my-1"></div>

                    {{-- Hapus --}}
                    <button
                        type="button"
                        @click="$wire.openDeleteModal(contextItem.path, contextItem.is_dir); closeContextMenu()"
                        class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors"
                    >
                        <flux:icon name="trash" class="size-3.5 text-rose-500" />
                        <span>{{ $this->t('ctx_delete') }}</span>
                    </button>
                </div>
            </template>

            {{-- Background / Canvas Context Menu (when clicking empty area) --}}
            <template x-if="!contextItem">
                <div class="space-y-0.5">
                    @if ($clipboardPath)
                        <button
                            type="button"
                            @click="$wire.pasteItem(); closeContextMenu()"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 transition-colors font-medium"
                        >
                            <flux:icon name="clipboard-document" class="size-3.5 text-indigo-500" />
                            <span>{{ $this->t('ctx_paste') }}</span>
                        </button>
                        <div class="h-px bg-neutral-200/80 dark:bg-white/10 my-1"></div>
                    @endif

                    @if (! $this->isSystemProtected)
                        <button
                            type="button"
                            @click="$wire.openNewFolderModal(); closeContextMenu()"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                        >
                            <flux:icon name="folder-plus" class="size-3.5 text-amber-500" />
                            <span>{{ $this->t('menu_new_folder') }}</span>
                        </button>

                        <button
                            type="button"
                            @click="$wire.openNewFileModal(); closeContextMenu()"
                            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                        >
                            <flux:icon name="document-plus" class="size-3.5 text-sky-500" />
                            <span>{{ $this->t('menu_new_file') }}</span>
                        </button>
                        <div class="h-px bg-neutral-200/80 dark:bg-white/10 my-1"></div>
                    @endif

                    <button
                        type="button"
                        @click="$wire.$refresh(); closeContextMenu()"
                        class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                    >
                        <flux:icon name="arrow-path" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                        <span>{{ $this->t('btn_refresh') }}</span>
                    </button>
                </div>
            </template>
        @endif
    </div>

    {{-- ========================================================= --}}
    {{-- MODAL: FOLDER BARU (WINDOWS 11 FLUENT DIALOG)             --}}
    {{-- ========================================================= --}}
    @if ($showNewFolderModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-[#2b2b2b] border border-neutral-200/90 dark:border-white/10 p-5 shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                        <flux:icon name="folder-plus" class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('modal_new_folder_title') }}</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('modal_new_folder_desc') }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $this->t('lbl_folder_name') }}</label>
                    <input
                        type="text"
                        wire:model="newFolderName"
                        wire:keydown.enter="createFolder"
                        autofocus
                        placeholder="{{ $this->t('placeholder_folder_name') }}"
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    />
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeNewFolderModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        {{ $this->t('btn_cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="createFolder"
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="rounded-md px-4 py-1.5 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                    >
                        {{ $this->t('btn_create_folder') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- MODAL: BERKAS BARU (WINDOWS 11 FLUENT DIALOG)             --}}
    {{-- ========================================================= --}}
    @if ($showNewFileModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-[#2b2b2b] border border-neutral-200/90 dark:border-white/10 p-5 shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-sky-500/10 text-sky-500">
                        <flux:icon name="document-plus" class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('modal_new_file_title') }}</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('modal_new_file_desc') }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $this->t('lbl_file_name') }}</label>
                    <input
                        type="text"
                        wire:model="newFileName"
                        wire:keydown.enter="createFile"
                        autofocus
                        placeholder="{{ $this->t('placeholder_file_name') }}"
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    />
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeNewFileModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        {{ $this->t('btn_cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="createFile"
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="rounded-md px-4 py-1.5 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                    >
                        {{ $this->t('btn_create_file') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- MODAL: UNGGAH BERKAS (WINDOWS 11 FLUENT DIALOG)           --}}
    {{-- ========================================================= --}}
    @if ($showUploadModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-[#2b2b2b] border border-neutral-200/90 dark:border-white/10 p-5 shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500">
                        <flux:icon name="arrow-up-tray" class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('modal_upload_title') }}</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">{{ $this->t('modal_upload_location', ['path' => $currentPath ?: '']) }}</p>
                    </div>
                </div>

                {{-- Upload Dropzone Area --}}
                <div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-neutral-300 dark:border-white/10 p-6 text-center hover:bg-neutral-50 dark:hover:bg-white/5 transition-all">
                    <flux:icon name="cloud-arrow-up" class="size-10 text-neutral-400 dark:text-neutral-500 mb-2" />
                    <label for="files_uploader" class="cursor-pointer text-xs font-semibold text-neutral-800 dark:text-neutral-200 hover:underline">
                        <span>{{ $this->t('upload_dropzone_label') }}</span>
                    </label>
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">{{ $this->t('upload_dropzone_hint') }}</p>
                    <input
                        id="files_uploader"
                        type="file"
                        multiple
                        wire:model="uploadedFiles"
                        class="absolute inset-0 cursor-pointer opacity-0"
                    />
                </div>

                {{-- Selected Files Count / Status --}}
                @if (count($uploadedFiles) > 0)
                    <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-3 py-2 text-xs text-emerald-600 dark:text-emerald-400 flex items-center gap-2">
                        <flux:icon name="check" class="size-3.5 shrink-0" />
                        <span>{{ $this->t('upload_selected_count', ['count' => count($uploadedFiles)]) }}</span>
                    </div>
                @endif

                <div wire:loading wire:target="uploadedFiles" class="text-xs text-neutral-500 flex items-center gap-2">
                    <flux:icon name="arrow-path" class="size-3.5 animate-spin" />
                    <span>{{ $this->t('upload_processing') }}</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeUploadModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        {{ $this->t('btn_cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="uploadFiles"
                        @disabled(count($uploadedFiles) === 0)
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="rounded-md px-4 py-1.5 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 disabled:opacity-40 transition-all"
                    >
                        {{ $this->t('btn_upload_now') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- MODAL: GANTI NAMA (WINDOWS 11 FLUENT DIALOG)              --}}
    {{-- ========================================================= --}}
    @if ($showRenameModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-[#2b2b2b] border border-neutral-200/90 dark:border-white/10 p-5 shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-amber-500/10 text-amber-500">
                        <flux:icon name="pencil-square" class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">{{ $this->t('modal_rename_title') }}</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate max-w-56">{{ $renameTargetName }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">{{ $this->t('lbl_new_name') }}</label>
                    <input
                        type="text"
                        wire:model="renameNewName"
                        wire:keydown.enter="rename"
                        autofocus
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    />
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeRenameModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        {{ $this->t('btn_cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="rename"
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="rounded-md px-4 py-1.5 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                    >
                        {{ $this->t('btn_save_name') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- MODAL: HAPUS ITEM (WINDOWS 11 FLUENT DIALOG)              --}}
    {{-- ========================================================= --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-[#2b2b2b] border border-neutral-200/90 dark:border-white/10 p-5 shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-rose-500/10 text-rose-500">
                        <flux:icon name="trash" class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">
                            @if ($this->isInTrash)
                                {{ $this->t('modal_delete_permanent_title', ['type' => $deleteIsDirectory ? $this->t('word_folder') : $this->t('word_file')]) }}
                            @else
                                {{ $this->t('modal_move_trash_title') }}
                            @endif
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate max-w-56">{{ $deleteTargetName }}</p>
                    </div>
                </div>

                <p class="text-xs text-neutral-600 dark:text-neutral-300">
                    @if ($this->isInTrash)
                        {{ $this->t('modal_delete_permanent_confirm', ['name' => $deleteTargetName]) }}
                    @else
                        {{ $this->t('modal_move_trash_confirm', ['name' => $deleteTargetName]) }}
                    @endif
                </p>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        {{ $this->t('btn_cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="delete"
                        class="rounded-md bg-rose-600 hover:bg-rose-700 px-4 py-1.5 text-xs font-medium text-white shadow-2xs active:scale-98 transition-all"
                    >
                        @if ($this->isInTrash)
                            {{ $this->t('btn_delete_permanent') }}
                        @else
                            {{ $this->t('btn_move_to_trash') }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- MODAL: PINDAHKAN ITEM (WINDOWS 11 FLUENT DIALOG)          --}}
    {{-- ========================================================= --}}
    @if ($showMoveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-sm rounded-2xl bg-white dark:bg-[#2b2b2b] border border-neutral-200/90 dark:border-white/10 p-5 shadow-2xl space-y-4">
                <div class="flex items-center gap-3">
                    <div class="flex size-10 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-500">
                        <flux:icon name="folder-arrow-down" class="size-5" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">
                            {{ $this->t('modal_move_title', ['type' => $moveIsDirectory ? $this->t('word_folder') : $this->t('word_file')]) }}
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate max-w-56">{{ $moveTargetName }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">
                        {{ $this->t('lbl_destination_folder') }}
                    </label>
                    <div class="space-y-1">
                        <select
                            wire:model="moveDestination"
                            class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                        >
                            @foreach ($this->availableFolders as $folder)
                                <option value="{{ $folder['path'] }}">
                                    {{ $folder['name'] ?: 'storage (Root)' }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-neutral-400 dark:text-neutral-500">
                            {{ $this->t('modal_move_desc', ['name' => $moveTargetName]) }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeMoveModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        {{ $this->t('btn_cancel') }}
                    </button>
                    <button
                        type="button"
                        wire:click="moveItem"
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="rounded-md px-4 py-1.5 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                    >
                        {{ $this->t('btn_confirm_move') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- MODAL: CLOUD STORAGE DETAIL (WINDOWS 11 FLUENT DIALOG)    --}}
    {{-- ========================================================= --}}
    @if ($showCloudStorageModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 p-4 backdrop-blur-xs">
            <div class="relative w-full max-w-md rounded-2xl bg-white dark:bg-[#2b2b2b] border border-neutral-200/90 dark:border-white/10 p-5 sm:p-6 shadow-2xl space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex size-10 items-center justify-center rounded-xl {{ ($this->cloudStorageInfo['driver'] ?? '') === 'bunny' ? 'bg-amber-500/10 text-amber-500' : 'bg-sky-500/10 text-sky-500' }}">
                            <flux:icon name="cloud" class="size-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">
                                {{ $this->cloudStorageInfo['name'] ?? $this->t('cloud_modal_title') }}
                            </h3>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                {{ $this->t('cloud_modal_desc') }}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeCloudStorageModal"
                        class="flex size-7 items-center justify-center rounded-lg text-neutral-400 hover:bg-neutral-100 dark:hover:bg-white/10 hover:text-neutral-700 dark:hover:text-white"
                        title="{{ $this->t('btn_close') }}"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </div>

                @if ($this->cloudStorageInfo)
                    <div class="rounded-xl border border-neutral-200/80 dark:border-white/10 bg-neutral-50/60 dark:bg-white/5 p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">{{ $this->t('cloud_lbl_driver') }}</span>
                                <div class="font-medium text-neutral-800 dark:text-neutral-200 font-mono mt-0.5">
                                    {{ strtoupper($this->cloudStorageInfo['driver']) }}
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">{{ $this->t('cloud_lbl_default_status') }}</span>
                                <div class="mt-0.5">
                                    @if ($this->cloudStorageInfo['is_active'])
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $this->t('cloud_default_active') }}
                                        </span>
                                    @else
                                        <span class="text-[11px] text-neutral-500">{{ $this->t('cloud_available_secondary') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-span-2 pt-1 border-t border-neutral-200/60 dark:border-white/5">
                                <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">{{ $this->t('cloud_lbl_target') }}</span>
                                <div class="font-medium text-neutral-800 dark:text-neutral-200 font-mono mt-0.5">
                                    {{ $this->cloudStorageInfo['target'] }}
                                </div>
                            </div>
                            @if (! empty($this->cloudStorageInfo['region']))
                                <div>
                                    <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">{{ $this->t('cloud_lbl_region') }}</span>
                                    <div class="font-medium text-neutral-800 dark:text-neutral-200 mt-0.5">
                                        {{ $this->cloudStorageInfo['region'] }}
                                    </div>
                                </div>
                            @endif
                            @if (! empty($this->cloudStorageInfo['endpoint']))
                                <div>
                                    <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">{{ $this->t('cloud_lbl_endpoint') }}</span>
                                    <div class="font-medium text-neutral-800 dark:text-neutral-200 truncate mt-0.5 font-mono text-[11px]">
                                        {{ $this->cloudStorageInfo['endpoint'] }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="rounded-lg bg-sky-500/5 dark:bg-sky-500/10 border border-sky-500/20 p-3 text-[11px] text-sky-700 dark:text-sky-300 leading-relaxed flex items-start gap-2">
                        <flux:icon name="information-circle" class="size-4 shrink-0 mt-0.5 text-sky-500" />
                        <div>
                            {{ $this->t('cloud_notice', ['name' => $this->cloudStorageInfo['name']]) }}
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-neutral-300 dark:border-white/10 p-6 text-center space-y-2">
                        <flux:icon name="cloud" class="size-8 text-neutral-400 mx-auto" />
                        <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">{{ $this->t('cloud_empty_title') }}</p>
                        <p class="text-[11px] text-neutral-500 max-w-xs mx-auto">
                            {{ $this->t('cloud_empty_desc') }}
                        </p>
                    </div>
                @endif

                {{-- Status Uji Koneksi --}}
                @if ($cloudStorageTestStatus)
                    <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 p-2.5 text-xs text-emerald-700 dark:text-emerald-300 flex items-center gap-2">
                        <flux:icon name="check-circle" class="size-4 text-emerald-500 shrink-0" />
                        <span>{{ $cloudStorageTestStatus }}</span>
                    </div>
                @endif

                @if ($cloudStorageTestError)
                    <div class="rounded-lg bg-rose-500/10 border border-rose-500/20 p-2.5 text-xs text-rose-700 dark:text-rose-300 flex items-center gap-2">
                        <flux:icon name="exclamation-circle" class="size-4 text-rose-500 shrink-0" />
                        <span>{{ $cloudStorageTestError }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between gap-2 pt-2 border-t border-neutral-200/80 dark:border-white/5">
                    @if ($this->cloudStorageInfo)
                        <button
                            type="button"
                            wire:click="testCloudStorageConnection"
                            wire:loading.attr="disabled"
                            class="flex items-center gap-1.5 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-white/5 transition-all disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="testCloudStorageConnection">
                                <flux:icon name="arrow-path" class="size-3.5 text-sky-500" />
                            </span>
                            <span wire:loading wire:target="testCloudStorageConnection" class="inline-block animate-spin size-3.5 border-2 border-sky-500 border-t-transparent rounded-full"></span>
                            <span>{{ $this->t('btn_test_connection') }}</span>
                        </button>
                    @else
                        <div></div>
                    @endif

                    <button
                        type="button"
                        wire:click="closeCloudStorageModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-4 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-white/5 transition-all"
                    >
                        {{ $this->t('btn_close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes detailsSlideIn {
            0% {
                transform: translateX(100%);
                opacity: 0.5;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</div>
