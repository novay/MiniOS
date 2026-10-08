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
    class="flex h-full min-h-125 w-full flex-col overflow-hidden bg-[#f3f3f3] dark:bg-[#202020] text-neutral-800 dark:text-neutral-100 font-sans select-none relative"
>

    {{-- ========================================================= --}}
    {{-- TOAST NOTIFICATION STATUS BANNER                          --}}
    {{-- ========================================================= --}}
    @if ($statusMessage)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => { show = false; $wire.clearNotification() }, 4000)"
            class="absolute top-14 right-4 z-50 flex items-center gap-2.5 rounded-xl border {{ $statusType === 'error' ? 'border-rose-500/30 bg-rose-500/10 text-rose-700 dark:text-rose-300' : 'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' }} px-3.5 py-2 text-xs font-medium shadow-lg backdrop-blur-md transition-all"
        >
            <flux:icon :name="$statusType === 'error' ? 'exclamation-triangle' : 'check-circle'" class="size-4 shrink-0" />
            <span>{{ $statusMessage }}</span>
            <button type="button" @click="show = false; $wire.clearNotification()" class="ml-1 opacity-70 hover:opacity-100">
                <flux:icon name="x-mark" class="size-3" />
            </button>
        </div>
    @endif

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
                title="Kembali"
                @disabled(! $this->canGoBack)
                class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
                <flux:icon name="arrow-left" class="size-4" />
            </button>

            {{-- Forward Button --}}
            <button
                type="button"
                wire:click="goForward"
                title="Maju"
                @disabled(! $this->canGoForward)
                class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
                <flux:icon name="arrow-right" class="size-4" />
            </button>

            {{-- Navigate Up Button --}}
            <button
                type="button"
                wire:click="navigateUp"
                title="Ke Folder Induk"
                @disabled(empty($currentPath))
                class="flex size-7 items-center justify-center rounded-md text-neutral-600 dark:text-neutral-300 hover:bg-black/5 dark:hover:bg-white/10 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
                <flux:icon name="arrow-up" class="size-4" />
            </button>

            {{-- Refresh Button --}}
            <button
                type="button"
                wire:click="$refresh"
                title="Muat Ulang"
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
                        {{ $crumb['name'] === 'storage' ? 'Penyimpanan' : $crumb['name'] }}
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
                placeholder="Cari dalam berkas..."
                class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] py-1 pl-8 pr-7 text-xs text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 dark:placeholder-neutral-500 shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
            />
            @if ($searchQuery !== '')
                <button
                    type="button"
                    wire:click="$set('searchQuery', '')"
                    class="absolute inset-y-0 right-0 flex items-center pr-2 text-neutral-400 hover:text-neutral-700 dark:hover:text-white"
                    title="Hapus pencarian"
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
            {{-- Tombol Baru (+ New Dropdown) --}}
            <div class="relative">
                <button
                    type="button"
                    @click.stop="showNewMenu = !showNewMenu"
                    style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                    class="flex items-center gap-1.5 rounded-md px-3 py-1 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                >
                    <flux:icon name="plus" class="size-3.5 stroke-[2.5]" />
                    <span>Baru</span>
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
                        <span>Folder Baru</span>
                    </button>
                    <button
                        type="button"
                        wire:click="openNewFileModal"
                        @click="showNewMenu = false"
                        class="flex w-full items-center gap-2 rounded-lg px-2.5 py-1.5 text-left text-xs text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
                    >
                        <flux:icon name="document-plus" class="size-4 text-sky-500" />
                        <span>Berkas Baru (.txt)</span>
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
                <span>Unggah</span>
            </button>

            {{-- Action Buttons if Item Selected --}}
            @if ($selectedPath)
                <div class="h-3.5 w-px bg-neutral-300/80 dark:bg-white/10 mx-1"></div>

                {{-- Unduh File --}}
                <button
                    type="button"
                    wire:click="downloadFile('{{ $selectedPath }}')"
                    title="Unduh Berkas"
                    class="flex items-center gap-1.5 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-2.5 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all"
                >
                    <flux:icon name="arrow-down-tray" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                    <span class="hidden sm:inline">Unduh</span>
                </button>

                {{-- Ganti Nama --}}
                <button
                    type="button"
                    wire:click="openRenameModal('{{ $selectedPath }}')"
                    title="Ganti Nama"
                    class="flex items-center gap-1.5 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-2.5 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333333] transition-all"
                >
                    <flux:icon name="pencil-square" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                    <span class="hidden sm:inline">Ganti Nama</span>
                </button>

                {{-- Hapus --}}
                <button
                    type="button"
                    wire:click="openDeleteModal('{{ $selectedPath }}', false)"
                    title="Hapus"
                    class="flex items-center gap-1.5 rounded-md border border-rose-300/80 dark:border-rose-500/20 bg-rose-50/50 dark:bg-rose-500/10 px-2.5 py-1 text-xs font-medium text-rose-600 dark:text-rose-400 shadow-2xs hover:bg-rose-100 dark:hover:bg-rose-500/20 transition-all"
                >
                    <flux:icon name="trash" class="size-3.5 text-rose-500" />
                    <span class="hidden sm:inline">Hapus</span>
                </button>

                {{-- Batal Seleksi --}}
                <button
                    type="button"
                    wire:click="$set('selectedPath', null)"
                    title="Batal Pilih"
                    class="text-neutral-400 hover:text-neutral-700 dark:hover:text-white p-1"
                >
                    <flux:icon name="x-mark" class="size-3.5" />
                </button>
            @endif

            {{-- Divider --}}
            <div class="h-3.5 w-px bg-neutral-300/80 dark:bg-white/10 mx-1"></div>

            {{-- Tampilan View Mode Toggle --}}
            <div class="flex items-center rounded-md border border-neutral-300/70 dark:border-white/10 bg-white/80 dark:bg-[#202020]/80 p-0.5 shadow-2xs">
                <button
                    type="button"
                    wire:click="setViewMode('grid')"
                    title="Tampilan Grid / Ikon"
                    class="flex size-6 items-center justify-center rounded transition-all {{ $viewMode === 'grid' ? 'bg-neutral-200/90 dark:bg-white/15 text-neutral-900 dark:text-white font-medium shadow-2xs' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-white' }}"
                >
                    <flux:icon name="squares-2x2" class="size-3.5" />
                </button>
                <button
                    type="button"
                    wire:click="setViewMode('list')"
                    title="Tampilan List / Detail"
                    class="flex size-6 items-center justify-center rounded transition-all {{ $viewMode === 'list' ? 'bg-neutral-200/90 dark:bg-white/15 text-neutral-900 dark:text-white font-medium shadow-2xs' : 'text-neutral-500 hover:text-neutral-800 dark:hover:text-white' }}"
                >
                    <flux:icon name="list-bullet" class="size-3.5" />
                </button>
            </div>

            
        </div>

        {{-- Current Path Pill --}}
        <div class="hidden sm:flex items-center gap-1.5 text-[13px] text-neutral-500 dark:text-neutral-400">
            {{-- Sort Options --}}
            <div class="flex items-center gap-1 text-[11px] text-neutral-600 dark:text-neutral-400">
                <span class="text-neutral-400 dark:text-neutral-500 hidden md:inline">Urutkan:</span>
                <button
                    type="button"
                    wire:click="sort('name')"
                    class="flex items-center gap-1 rounded px-2 py-0.5 transition-colors hover:bg-neutral-200/70 dark:hover:bg-white/10 {{ $sortBy === 'name' ? 'font-semibold text-neutral-900 dark:text-white' : '' }}"
                >
                    <span>Nama</span>
                    @if ($sortBy === 'name')
                        <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                    @endif
                </button>

                <button
                    type="button"
                    wire:click="sort('updated_at')"
                    class="flex items-center gap-1 rounded px-2 py-0.5 transition-colors hover:bg-neutral-200/70 dark:hover:bg-white/10 {{ $sortBy === 'updated_at' ? 'font-semibold text-neutral-900 dark:text-white' : '' }}"
                >
                    <span>Tanggal</span>
                    @if ($sortBy === 'updated_at')
                        <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                    @endif
                </button>

                <button
                    type="button"
                    wire:click="sort('size')"
                    class="flex items-center gap-1 rounded px-2 py-0.5 transition-colors hover:bg-neutral-200/70 dark:hover:bg-white/10 {{ $sortBy === 'size' ? 'font-semibold text-neutral-900 dark:text-white' : '' }}"
                >
                    <span>Ukuran</span>
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
                    Akses Cepat
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
                        <span class="truncate">Storage Root</span>
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
                        <span class="truncate">App Data</span>
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
                        <span class="truncate">Public Storage</span>
                    </button>
                </nav>
            </div>

            {{-- Cloud Storage Section --}}
            <div>
                <div class="px-2 text-[10px] font-bold uppercase tracking-wider text-neutral-400 dark:text-neutral-500 mb-1 flex items-center justify-between">
                    <span>Cloud Storage</span>
                    {{-- @if ($this->cloudStorageInfo && $this->cloudStorageInfo['is_active'])
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[9px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">Default OS</span>
                    @endif --}}
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
                                {{-- <div class="text-[10px] text-neutral-400 dark:text-neutral-500 truncate">{{ $this->cloudStorageInfo['target'] }}</div> --}}
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
                    Sistem MiniOS
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
                        <span class="truncate">Logs Sistem</span>
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
                        <span class="truncate">Framework Cache</span>
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
                        <div class="text-xs font-semibold truncate text-neutral-900 dark:text-white">Penyimpanan Lokal</div>
                        <div class="text-[10px] text-neutral-500 dark:text-neutral-400">storage/app (Lokal Host)</div>
                    </div>
                </div>

                @if ($this->cloudStorageInfo)
                    <div class="pt-1.5 border-t border-neutral-200/60 dark:border-white/5 flex items-center justify-between text-[10px]">
                        <span class="text-neutral-500 dark:text-neutral-400 flex items-center gap-1 min-w-0">
                            <flux:icon name="cloud" class="size-3 shrink-0 {{ $this->cloudStorageInfo['driver'] === 'bunny' ? 'text-amber-500' : 'text-sky-500' }}" />
                            <span class="truncate">{{ $this->cloudStorageInfo['name'] }}</span>
                        </span>
                        @if ($this->cloudStorageInfo['is_active'])
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400 shrink-0">Aktif</span>
                        @else
                            <span class="text-neutral-400 shrink-0">Tersedia</span>
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
        <main class="flex-1 bg-white dark:bg-[#191919] p-4 sm:p-5 overflow-y-auto">
            @if ($this->isLocked)
                {{-- Windows 11 Security Access Prompt --}}
                <div class="flex h-full w-full flex-col items-center justify-center text-center p-8 space-y-4">
                    <div class="flex size-16 items-center justify-center rounded-2xl bg-rose-500/10 text-rose-500 border border-rose-500/20 shadow-md">
                        <flux:icon name="lock-closed" class="size-8" />
                    </div>
                    <div class="max-w-xs space-y-1">
                        <h2 class="text-base font-semibold text-neutral-900 dark:text-white">Akses Terkunci</h2>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Aplikasi Files hanya dapat diakses oleh pengguna yang sudah login ke MiniOS.</p>
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
                            {{ $searchQuery !== '' ? 'Berkas tidak ditemukan' : 'Folder ini kosong' }}
                        </p>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">
                            {{ $searchQuery !== '' ? 'Tidak ada berkas yang cocok dengan "'.$searchQuery.'"' : 'Tidak ada berkas atau direktori di lokasi ini' }}
                        </p>
                    </div>
                    @if ($searchQuery !== '')
                        <button
                            type="button"
                            wire:click="$set('searchQuery', '')"
                            class="inline-flex items-center gap-1 rounded-md bg-neutral-200/80 dark:bg-white/10 px-3 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:bg-neutral-300 dark:hover:bg-white/15 transition-colors"
                        >
                            Hapus filter pencarian
                        </button>
                    @endif
                </div>
            @else
                @if ($viewMode === 'grid')
                    {{-- ========================================================= --}}
                    {{-- GRID VIEW (WINDOWS 11 TILES / ICONS)                     --}}
                    {{-- ========================================================= --}}
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-2.5">
                        @foreach ($this->items as $item)
                            @php
                                $isSelected = $selectedPath === $item['path'];
                            @endphp
                            <div
                                @contextmenu.prevent.stop="openContextMenu($event, { path: '{{ $item['path'] }}', name: '{{ addslashes($item['name']) }}', is_dir: {{ $item['is_dir'] ? 'true' : 'false' }} })"
                                wire:click="selectItem('{{ $item['path'] }}')"
                                @dblclick="@if ($item['is_dir']) $wire.navigate('{{ $item['path'] }}') @else $wire.openPreview('{{ $item['path'] }}') @endif"
                                class="group relative flex flex-col items-center gap-1.5 p-3 rounded-xl border transition-all text-center cursor-pointer select-none active:scale-98 {{ $isSelected ? ($accent['radio_card'] ?? 'border-indigo-500 bg-indigo-500/10 ring-1 ring-indigo-500') : 'border-transparent hover:border-neutral-200/80 dark:hover:border-white/10 hover:bg-neutral-100/70 dark:hover:bg-white/5' }}"
                            >
                                {{-- 3-Dots Quick Action Button (Hover) --}}
                                <button
                                    type="button"
                                    @click.stop="openContextMenu($event, { path: '{{ $item['path'] }}', name: '{{ addslashes($item['name']) }}', is_dir: {{ $item['is_dir'] ? 'true' : 'false' }} })"
                                    class="absolute top-2 right-2 flex size-6 items-center justify-center rounded-md bg-white/80 dark:bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-neutral-200 dark:hover:bg-white/20 text-neutral-600 dark:text-neutral-300 shadow-2xs"
                                    title="Pilihan"
                                >
                                    <flux:icon name="ellipsis-horizontal" class="size-3.5" />
                                </button>

                                @if ($item['is_dir'])
                                    {{-- Windows 11 Fluent 3D Folder Icon --}}
                                    <div
                                        wire:click.stop="navigate('{{ $item['path'] }}')"
                                        class="relative flex size-13 items-center justify-center transition-transform group-hover:scale-105"
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
                                        wire:click.stop="openPreview('{{ $item['path'] }}')"
                                        class="flex size-13 items-center justify-center transition-transform group-hover:scale-105"
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
                                            <span>Nama</span>
                                            @if ($sortBy === 'name')
                                                <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                                            @endif
                                        </div>
                                    </th>
                                    <th class="py-2 px-3 hover:text-neutral-800 dark:hover:text-white cursor-pointer" wire:click="sort('updated_at')">
                                        <div class="flex items-center gap-1">
                                            <span>Terakhir Diubah</span>
                                            @if ($sortBy === 'updated_at')
                                                <flux:icon :name="$sortDirection === 'asc' ? 'bars-arrow-up' : 'bars-arrow-down'" class="size-3" />
                                            @endif
                                        </div>
                                    </th>
                                    <th class="py-2 px-3">Tipe</th>
                                    <th class="py-2 px-3 hover:text-neutral-800 dark:hover:text-white cursor-pointer text-right" wire:click="sort('size')">
                                        <div class="flex items-center justify-end gap-1">
                                            <span>Ukuran</span>
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
                                        class="group cursor-pointer transition-colors {{ $isSelected ? 'bg-neutral-100 dark:bg-white/10 font-medium' : 'hover:bg-neutral-100/70 dark:hover:bg-white/5' }}"
                                    >
                                        <td
                                            @if ($item['is_dir']) wire:click.stop="navigate('{{ $item['path'] }}')" @else wire:click.stop="openPreview('{{ $item['path'] }}')" @endif
                                            class="py-2 px-3 flex items-center gap-2.5"
                                        >
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
    </div>

    {{-- ========================================================= --}}
    {{-- FOOTER STATUS BAR (WINDOWS 11 STYLE)                      --}}
    {{-- ========================================================= --}}
    <footer class="flex shrink-0 items-center justify-between border-t border-neutral-200/90 dark:border-white/5 bg-[#f8f8f8]/90 dark:bg-[#202020]/90 px-4 py-1 text-xs text-neutral-500 dark:text-neutral-400 select-none">
        <div class="flex items-center gap-2">
            <span>{{ count($this->items) }} item</span>
            @if ($selectedPath)
                <span class="rounded bg-neutral-200 dark:bg-white/10 px-1.5 py-0.2 text-[10px] text-neutral-700 dark:text-neutral-300">
                    1 item dipilih
                </span>
            @endif
            @if ($searchQuery !== '')
                <span class="rounded bg-neutral-200 dark:bg-white/10 px-1.5 py-0.2 text-[10px] text-neutral-700 dark:text-neutral-300">
                    Hasil pencarian
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
        {{-- Item Info Header in Context Menu --}}
        <div class="px-2 py-1 border-b border-neutral-200/80 dark:border-white/10 mb-1">
            <span class="block truncate font-semibold text-neutral-900 dark:text-white" x-text="contextItem?.name"></span>
        </div>

        {{-- Buka / Pratinjau --}}
        <button
            type="button"
            @click="if (contextItem?.is_dir) { $wire.navigate(contextItem.path); } else { $wire.openPreview(contextItem.path); } closeContextMenu()"
            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
        >
            <flux:icon name="arrow-top-right-on-square" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
            <span x-text="contextItem?.is_dir ? 'Buka Folder' : 'Pratinjau Berkas'"></span>
        </button>

        {{-- Unduh (Jika File) --}}
        <template x-if="!contextItem?.is_dir">
            <button
                type="button"
                @click="$wire.downloadFile(contextItem.path); closeContextMenu()"
                class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
            >
                <flux:icon name="arrow-down-tray" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
                <span>Unduh Berkas</span>
            </button>
        </template>

        {{-- Ganti Nama --}}
        <button
            type="button"
            @click="$wire.openRenameModal(contextItem.path); closeContextMenu()"
            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-white/10 transition-colors"
        >
            <flux:icon name="pencil-square" class="size-3.5 text-neutral-500 dark:text-neutral-400" />
            <span>Ganti Nama</span>
        </button>

        {{-- Divider --}}
        <div class="h-px bg-neutral-200/10 dark:border-white/10 my-1"></div>

        {{-- Hapus --}}
        <button
            type="button"
            @click="$wire.openDeleteModal(contextItem.path, contextItem.is_dir); closeContextMenu()"
            class="flex w-full items-center gap-2.5 rounded-lg px-2.5 py-1.5 text-left text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors"
        >
            <flux:icon name="trash" class="size-3.5 text-rose-500" />
            <span>Hapus</span>
        </button>
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
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Folder Baru</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Buat folder baru di direktori saat ini.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Nama Folder</label>
                    <input
                        type="text"
                        wire:model="newFolderName"
                        wire:keydown.enter="createFolder"
                        autofocus
                        placeholder="Contoh: Dokumen"
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    />
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeNewFolderModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="createFolder"
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="rounded-md px-4 py-1.5 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                    >
                        Buat Folder
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
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Berkas Baru</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Buat berkas teks baru di direktori saat ini.</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Nama Berkas</label>
                    <input
                        type="text"
                        wire:model="newFileName"
                        wire:keydown.enter="createFile"
                        autofocus
                        placeholder="Contoh: catatan.txt"
                        class="w-full rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-3 py-1.5 text-xs text-neutral-900 dark:text-white shadow-2xs transition-all focus:outline-none focus:ring-2 {{ $accent['ring'] }}"
                    />
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeNewFileModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="createFile"
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="rounded-md px-4 py-1.5 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                    >
                        Buat Berkas
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
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Unggah Berkas</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400">Lokasi: storage/{{ $currentPath ?: '' }}</p>
                    </div>
                </div>

                {{-- Upload Dropzone Area --}}
                <div class="relative flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-neutral-300 dark:border-white/10 p-6 text-center hover:bg-neutral-50 dark:hover:bg-white/5 transition-all">
                    <flux:icon name="cloud-arrow-up" class="size-10 text-neutral-400 dark:text-neutral-500 mb-2" />
                    <label for="files_uploader" class="cursor-pointer text-xs font-semibold text-neutral-800 dark:text-neutral-200 hover:underline">
                        <span>Pilih berkas dari perangkat Anda</span>
                    </label>
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 mt-0.5">Dapat memilih beberapa berkas sekaligus.</p>
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
                        <span>{{ count($uploadedFiles) }} berkas terpilih dan siap diunggah.</span>
                    </div>
                @endif

                <div wire:loading wire:target="uploadedFiles" class="text-xs text-neutral-500 flex items-center gap-2">
                    <flux:icon name="arrow-path" class="size-3.5 animate-spin" />
                    <span>Memproses berkas...</span>
                </div>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeUploadModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="uploadFiles"
                        @disabled(count($uploadedFiles) === 0)
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="rounded-md px-4 py-1.5 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 disabled:opacity-40 transition-all"
                    >
                        Unggah Sekarang
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
                        <h3 class="text-sm font-semibold text-neutral-900 dark:text-white">Ganti Nama</h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate max-w-56">{{ $renameTargetName }}</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1.5">Nama Baru</label>
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
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="rename"
                        style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                        class="rounded-md px-4 py-1.5 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                    >
                        Simpan Nama
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
                            Hapus {{ $deleteIsDirectory ? 'Folder' : 'Berkas' }}?
                        </h3>
                        <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate max-w-56">{{ $deleteTargetName }}</p>
                    </div>
                </div>

                <p class="text-xs text-neutral-600 dark:text-neutral-300">
                    Apakah Anda yakin ingin menghapus <span class="font-semibold text-neutral-900 dark:text-white">{{ $deleteTargetName }}</span>? Tindakan ini akan menghapus data secara permanen.
                </p>

                <div class="flex items-center justify-end gap-2 pt-1">
                    <button
                        type="button"
                        wire:click="closeDeleteModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-3.5 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        wire:click="delete"
                        class="rounded-md bg-rose-600 hover:bg-rose-700 px-4 py-1.5 text-xs font-medium text-white shadow-2xs active:scale-98 transition-all"
                    >
                        Hapus Permanen
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- FILE PREVIEW & INLINE TEXT EDITOR MODAL                   --}}
    {{-- ========================================================= --}}
    @if ($previewItem)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 dark:bg-black/70 p-4 sm:p-6 backdrop-blur-xs">
            <div class="relative flex max-h-[88vh] max-w-4xl w-full flex-col rounded-2xl bg-white dark:bg-[#2b2b2b] border border-neutral-200/90 dark:border-white/10 shadow-2xl overflow-hidden">
                {{-- Dialog Header --}}
                <div class="flex items-center justify-between border-b border-neutral-200/90 dark:border-white/10 bg-neutral-50/80 dark:bg-[#303030]/80 px-4 py-3">
                    <div class="flex items-center gap-2 truncate">
                        <span class="rounded bg-neutral-200/70 dark:bg-white/10 px-2 py-0.5 text-[10px] font-mono uppercase tracking-wider text-neutral-700 dark:text-neutral-300">
                            {{ $previewItem['extension'] }}
                        </span>
                        <span class="text-sm font-semibold text-neutral-900 dark:text-white truncate">
                            {{ $previewItem['name'] }}
                        </span>
                        <span class="text-xs text-neutral-500 dark:text-neutral-400">
                            ({{ $previewItem['size'] }})
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Tombol Simpan jika File Text/Code --}}
                        @if ($previewItem['type'] === 'text')
                            <button
                                type="button"
                                wire:click="saveTextFile"
                                style="background-color: var(--accent-color, {{ $accent['hex'] }});"
                                class="flex items-center gap-1.5 rounded-md px-3 py-1 text-xs font-medium text-white shadow-2xs hover:brightness-110 active:scale-98 transition-all"
                            >
                                <flux:icon name="check" class="size-3.5 stroke-[2.5]" />
                                <span>Simpan</span>
                            </button>
                        @endif

                        {{-- Tombol Unduh --}}
                        <button
                            type="button"
                            wire:click="downloadFile('{{ $previewItem['path'] }}')"
                            class="flex items-center gap-1.5 rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#2b2b2b] px-2.5 py-1 text-xs font-medium text-neutral-700 dark:text-neutral-200 shadow-2xs hover:bg-neutral-50 dark:hover:bg-[#333] transition-all"
                            title="Unduh Berkas"
                        >
                            <flux:icon name="arrow-down-tray" class="size-3.5 text-neutral-500" />
                            <span class="hidden sm:inline">Unduh</span>
                        </button>

                        {{-- Tombol Tutup --}}
                        <button
                            wire:click="closePreview"
                            type="button"
                            class="flex size-7 items-center justify-center rounded-lg text-neutral-500 hover:bg-neutral-200/80 dark:hover:bg-white/10 hover:text-neutral-900 dark:hover:text-white transition-all"
                            title="Tutup"
                        >
                            <flux:icon name="x-mark" class="size-4" />
                        </button>
                    </div>
                </div>

                {{-- Dialog Preview Content --}}
                <div class="flex flex-1 items-center justify-center p-4 overflow-auto min-h-64 max-h-[72vh] bg-neutral-100/50 dark:bg-[#1a1a1a]">
                    @if ($previewItem['type'] === 'image')
                        <img
                            src="{{ $previewItem['data'] }}"
                            alt="{{ $previewItem['name'] }}"
                            class="max-h-[68vh] max-w-full rounded-lg object-contain shadow-md"
                        />
                    @elseif ($previewItem['type'] === 'pdf')
                        <iframe
                            src="{{ $previewItem['data'] }}"
                            class="h-[68vh] w-full border-none rounded-lg bg-white"
                        ></iframe>
                    @elseif ($previewItem['type'] === 'text')
                        <div class="w-full h-full flex flex-col space-y-1">
                            <textarea
                                wire:model="previewContent"
                                rows="18"
                                class="w-full h-[65vh] p-4 text-xs font-mono rounded-lg bg-white dark:bg-[#111111] text-neutral-900 dark:text-emerald-400 border border-neutral-200 dark:border-white/10 focus:outline-none focus:ring-2 {{ $accent['ring'] }} resize-none select-text leading-relaxed scrollbar-thin"
                                placeholder="Isi berkas teks..."
                            >{{ $previewContent }}</textarea>
                            <span class="text-[10px] text-neutral-400">Tekan tombol "Simpan" di sudut kanan atas untuk menyimpan perubahan berkas.</span>
                        </div>
                    @else
                        <div class="flex flex-col items-center gap-3 text-center text-neutral-400 p-8">
                            <flux:icon name="document" class="size-16 text-neutral-400 dark:text-neutral-600" />
                            <div>
                                <p class="text-sm font-semibold text-neutral-800 dark:text-neutral-200">Pratinjau tidak tersedia</p>
                                <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Format {{ strtoupper($previewItem['extension']) }} tidak didukung untuk pratinjau langsung.</p>
                            </div>
                        </div>
                    @endif
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
                                {{ $this->cloudStorageInfo['name'] ?? 'Cloud Storage MiniOS' }}
                            </h3>
                            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                                Integrasi penyimpanan awan untuk file dan dokumen.
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeCloudStorageModal"
                        class="flex size-7 items-center justify-center rounded-lg text-neutral-400 hover:bg-neutral-100 dark:hover:bg-white/10 hover:text-neutral-700 dark:hover:text-white"
                    >
                        <flux:icon name="x-mark" class="size-4" />
                    </button>
                </div>

                @if ($this->cloudStorageInfo)
                    <div class="rounded-xl border border-neutral-200/80 dark:border-white/10 bg-neutral-50/60 dark:bg-white/5 p-4 space-y-3">
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">Driver</span>
                                <div class="font-medium text-neutral-800 dark:text-neutral-200 font-mono mt-0.5">
                                    {{ strtoupper($this->cloudStorageInfo['driver']) }}
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">Status Default</span>
                                <div class="mt-0.5">
                                    @if ($this->cloudStorageInfo['is_active'])
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400">
                                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                                            Disk Default Aktif
                                        </span>
                                    @else
                                        <span class="text-[11px] text-neutral-500">Tersedia (Secondary)</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-span-2 pt-1 border-t border-neutral-200/60 dark:border-white/5">
                                <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">Target (Bucket / Zone)</span>
                                <div class="font-medium text-neutral-800 dark:text-neutral-200 font-mono mt-0.5">
                                    {{ $this->cloudStorageInfo['target'] }}
                                </div>
                            </div>
                            @if (! empty($this->cloudStorageInfo['region']))
                                <div>
                                    <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">Region</span>
                                    <div class="font-medium text-neutral-800 dark:text-neutral-200 mt-0.5">
                                        {{ $this->cloudStorageInfo['region'] }}
                                    </div>
                                </div>
                            @endif
                            @if (! empty($this->cloudStorageInfo['endpoint']))
                                <div>
                                    <span class="text-[10px] text-neutral-400 uppercase tracking-wider font-semibold">Endpoint / CDN URL</span>
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
                            Aplikasi Files tetap menelusuri penyimpanan lokal server MiniOS. Operasi berkas eksternal/upload kustom di MiniOS akan otomatis disalurkan ke disk <strong>{{ $this->cloudStorageInfo['name'] }}</strong>.
                        </div>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-neutral-300 dark:border-white/10 p-6 text-center space-y-2">
                        <flux:icon name="cloud" class="size-8 text-neutral-400 mx-auto" />
                        <p class="text-xs font-semibold text-neutral-800 dark:text-neutral-200">Belum Ada Cloud Storage Aktif</p>
                        <p class="text-[11px] text-neutral-500 max-w-xs mx-auto">
                            Konfigurasikan Amazon S3 atau BunnyCDN di menu Pengaturan &gt; Layanan &amp; Infrastruktur &gt; Filesystem.
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
                            <span>Uji Koneksi</span>
                        </button>
                    @else
                        <div></div>
                    @endif

                    <button
                        type="button"
                        wire:click="closeCloudStorageModal"
                        class="rounded-md border border-neutral-300/80 dark:border-white/10 bg-white dark:bg-[#202020] px-4 py-1.5 text-xs font-medium text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-white/5 transition-all"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
