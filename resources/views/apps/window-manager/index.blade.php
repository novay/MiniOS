<x-minios:desktop app-id="window-manager" width="265" min-width="205" max-width="385">
    <x-minios:desktop.menu>
        <x-minios.menubar>
            <x-minios.menubar.menu label="File">
                <x-minios.menubar.item @click="$dispatch('close-window', { id: 'window-manager' })" icon="x-mark">Close</x-minios.menubar.item>
            </x-minios.menubar.menu>
            <x-minios.menubar.menu label="View">
                <x-minios.menubar.item @click="toggleSidebar()" icon="bars-3-bottom-left">Toggle Sidebar</x-minios.menubar.item>
            </x-minios.menubar.menu>
            <x-minios.menubar.menu label="Window">
                <x-minios.menubar.item @click="refresh()" icon="arrow-path">Reload Page</x-minios.menubar.item>
                <x-minios.menubar.item @click="toggleStatusbar()" icon="rectangle-stack">Toggle Statusbar</x-minios.menubar.item>
            </x-minios.menubar.menu>
        </x-minios.menubar>
    </x-minios:desktop.menu>

    <x-minios:desktop.sidebar>
        <div class="mb-3 flex items-center gap-2" x-show="!sidebarCollapsed">
            <div class="flex size-9 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                <flux:icon name="squares-2x2" class="size-5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold truncate">Applications</p>
                <p class="text-[11px] text-neutral-500">Manage application manifests</p>
            </div>
        </div>

        @if ($authorized)
            <div class="relative mb-3" x-show="!sidebarCollapsed">
                <flux:icon name="magnifying-glass" class="absolute left-2.5 top-2 size-4 text-neutral-400" />
                <input type="search" wire:model.live.debounce.250ms="search" placeholder="Cari aplikasi..."
                    class="w-full rounded-lg border border-neutral-200 bg-white py-1.5 pl-8 pr-2 text-xs outline-none focus:ring-2 focus:ring-indigo-400 dark:border-white/10 dark:bg-white/5" />
            </div>
            <nav class="min-h-0 flex-1 overflow-y-auto space-y-1">
                @forelse ($apps as $item)
                    <button type="button" wire:key="wm-app-{{ $item['id'] }}"
                        wire:click="selectApp('{{ $item['id'] }}')"
                        title="{{ $item['name'] }}"
                        class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs transition-colors {{ $selectedId === $item['id'] ? 'bg-white text-neutral-900 shadow-sm dark:bg-white/10 dark:text-white' : 'hover:bg-black/5 dark:hover:bg-white/5' }}">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-neutral-200/60 dark:bg-white/10">
                            <x-minios.icon :name="$item['icon']" class="size-5" />
                        </span>
                        <span class="min-w-0 flex-1" x-show="!sidebarCollapsed">
                            <span class="block truncate font-semibold">{{ $item['name'] }}</span>
                            <span class="block truncate text-[10px] text-neutral-500">{{ $item['id'] }}</span>
                        </span>
                        @if ($item['modified'])
                            <span x-show="!sidebarCollapsed" class="size-1.5 rounded-full bg-indigo-500" title="Overrides aktif"></span>
                        @endif
                    </button>
                @empty
                    <div class="p-3 text-xs text-neutral-500" x-show="!sidebarCollapsed">Aplikasi tidak ditemukan.</div>
                @endforelse
            </nav>
        @else
            <div class="rounded-lg border border-amber-500/20 bg-amber-500/10 p-3 text-xs" x-show="!sidebarCollapsed">
                Manajemen aplikasi hanya tersedia untuk administrator.
            </div>
        @endif
    </x-minios:desktop.sidebar>

    <x-minios:desktop.content>
        @if (! $authorized)
            <section class="m-auto max-w-md space-y-3 p-8 text-center">
                <flux:icon name="lock-closed" class="mx-auto size-10 text-amber-500" />
                <h2 class="text-base font-semibold">Akses Window Manager dibatasi</h2>
                <p class="text-sm text-neutral-500">Administrator harus diberi izin <code class="font-mono">minios.manage-apps</code> melalui Laravel Gate.</p>
            </section>
        @elseif ($selectedId !== '')
            <div class="flex min-h-full flex-col" wire:key="wm-form-{{ $selectedId }}">
                <header class="sticky top-0 z-10 flex shrink-0 flex-wrap items-center justify-between gap-3 border-b border-neutral-200 bg-[#f9f9f9]/95 px-5 py-4 backdrop-blur-md dark:border-white/10 dark:bg-[#202020]/95">
                    <div class="min-w-0">
                        <p class="text-[11px] font-medium uppercase tracking-wide text-neutral-500">Application Configuration</p>
                        <h1 class="truncate text-lg font-semibold">{{ $form['name'] ?? $selectedId }}</h1>
                        <p class="text-xs text-neutral-500">{{ $selectedId }} @if($isCore) · System Application @endif</p>
                    </div>
                    <button type="button" @click="window.location.reload()"
                        class="rounded-lg border border-neutral-200 px-3 py-2 text-xs font-medium hover:bg-neutral-100 dark:border-white/10 dark:hover:bg-white/10">
                        <flux:icon name="arrow-path" class="mr-1 inline size-4" /> Terapkan (reload)
                    </button>
                </header>

                <div class="flex gap-1 border-b border-neutral-200 px-5 pt-2 text-xs dark:border-white/10">
                    <button type="button" wire:click="setSection('properties')" class="border-b-2 px-3 py-2 font-medium {{ $section === 'properties' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-neutral-500' }}">Properties & Window</button>
                    <button type="button" wire:click="setSection('layout')" class="border-b-2 px-3 py-2 font-medium {{ $section === 'layout' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-neutral-500' }}">App Shell</button>
                    <button type="button" wire:click="setSection('menus')" class="border-b-2 px-3 py-2 font-medium {{ $section === 'menus' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-neutral-500' }}">Menubar Builder</button>
                    <button type="button" wire:click="setSection('sidebar')" class="border-b-2 px-3 py-2 font-medium {{ $section === 'sidebar' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-neutral-500' }}">Sidebar & CRUD</button>
                    <button type="button" wire:click="setSection('widgets')" class="border-b-2 px-3 py-2 font-medium {{ $section === 'widgets' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-neutral-500' }}">Desktop Widgets</button>
                </div>

                @if (! $hasDatabase)
                    <div class="mx-5 mt-4 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-xs text-amber-800 dark:border-amber-600/20 dark:bg-amber-500/10 dark:text-amber-300">Database belum disiapkan. Jalankan <code>php artisan migrate</code> sebelum menyimpan.</div>
                @endif
                @if ($message)
                    <div class="mx-5 mt-4 rounded-lg border px-4 py-3 text-xs {{ $messageType === 'error' ? 'border-red-300 bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300' : 'border-emerald-300 bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' }}">{{ $message }}</div>
                @endif
                @if ($errors->any())
                    <div class="mx-5 mt-4 rounded-lg border border-red-300 bg-red-50 p-3 text-xs text-red-700 dark:bg-red-500/10 dark:text-red-300">
                        <p class="font-semibold">Konfigurasi belum valid:</p>
                        <ul class="ml-4 mt-1 list-disc space-y-1">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                @if($section === 'menus')
                    <livewire:dynamic-component :is="\Novay\MiniOS\Livewire\Apps\WindowMenuBuilder::class" :app-id="$selectedId" :wire:key="'wm-menu-'.$selectedId" />
                @elseif($section === 'sidebar')
                    <livewire:dynamic-component :is="\Novay\MiniOS\Livewire\Apps\WindowSidebarBuilder::class" :app-id="$selectedId" :wire:key="'wm-sidebar-'.$selectedId" />
                @elseif($section === 'widgets')
                    <div class="flex flex-1 flex-col p-6 space-y-6">
                        <div class="flex items-start justify-between gap-4 rounded-2xl border border-indigo-500/20 bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-transparent p-5 backdrop-blur-sm dark:border-white/10 dark:from-indigo-500/15">
                            <div class="space-y-1">
                                <div class="inline-flex items-center gap-2 rounded-full bg-indigo-500/10 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-400">
                                    <span class="size-1.5 rounded-full bg-indigo-500 animate-pulse"></span>
                                    Planned Feature · Desktop Widgets
                                </div>
                                <h2 class="text-base font-semibold text-neutral-900 dark:text-white">Desktop Widgets Engine</h2>
                                <p class="text-xs text-neutral-600 dark:text-neutral-400 max-w-xl">
                                    Fungsi ini akan memungkinkan aplikasi menyediakan widget interaktif (mini view, statistik, quick actions, sticky notes) yang dapat disematkan langsung di atas wallpaper desktop.
                                </p>
                            </div>
                            <div class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600/10 text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-400">
                                <flux:icon name="squares-plus" class="size-6" />
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition dark:border-white/10 dark:bg-white/[0.03]">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex size-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                        <flux:icon name="cursor-arrow-rays" class="size-4" />
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-semibold">Draggable & Snap Grid</h3>
                                        <p class="text-[10px] text-neutral-500">Free placement & docking</p>
                                    </div>
                                </div>
                                <p class="text-[11px] text-neutral-500 leading-relaxed">
                                    Widget dapat diletakkan di grid desktop dengan koordinat dan konfigurasi persisten per user.
                                </p>
                            </div>

                            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition dark:border-white/10 dark:bg-white/[0.03]">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex size-8 items-center justify-center rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">
                                        <flux:icon name="bolt" class="size-4" />
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-semibold">Reaktif Livewire / Blade</h3>
                                        <p class="text-[10px] text-neutral-500">Live polling & lightweight</p>
                                    </div>
                                </div>
                                <p class="text-[11px] text-neutral-500 leading-relaxed">
                                    Mendukung komponen Livewire mandiri atau view Blade ringan tanpa beban frame window OS.
                                </p>
                            </div>

                            <div class="rounded-xl border border-neutral-200 bg-white p-4 shadow-sm transition dark:border-white/10 dark:bg-white/[0.03]">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="flex size-8 items-center justify-center rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400">
                                        <flux:icon name="puzzle-piece" class="size-4" />
                                    </div>
                                    <div>
                                        <h3 class="text-xs font-semibold">App Manifest Provider</h3>
                                        <p class="text-[10px] text-neutral-500">Contract & presets</p>
                                    </div>
                                </div>
                                <p class="text-[11px] text-neutral-500 leading-relaxed">
                                    Aplikasi seperti <code>{{ $selectedId }}</code> dapat mendaftarkan satu atau lebih widget melalui method <code>widgets()</code>.
                                </p>
                            </div>
                        </div>

                        <div class="rounded-xl border border-neutral-200 bg-neutral-50/60 p-4 dark:border-white/10 dark:bg-white/[0.02]">
                            <h3 class="text-xs font-semibold mb-2 flex items-center gap-2">
                                <flux:icon name="code-bracket" class="size-4 text-indigo-500" />
                                Rencana Konsep Kontrak Widget
                            </h3>
                            <pre class="overflow-x-auto rounded-lg bg-neutral-900 p-3 font-mono text-[11px] text-neutral-200 dark:bg-black/40"><code>// Konsep implementasi di {{ \Illuminate\Support\Str::studly($selectedId) }}App.php:
public function widgets(): array
{
    return [
        DesktopWidget::make('quick-summary')
            ->label('{{ $form['name'] ?? $selectedId }} Summary')
            ->size(width: 2, height: 1) // 2x1 grid unit
            ->component(\App\MiniOS\{{ \Illuminate\Support\Str::studly($selectedId) }}\Livewire\Widgets\Summary::class),
    ];
}</code></pre>
                        </div>
                    </div>
                @else
                <form wire:submit="save" class="flex flex-1 flex-col">
                    <div class="flex-1 space-y-5 p-5">
                        @if ($section === 'properties')
                            <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                <h2 class="mb-3 text-sm font-semibold">Identitas Aplikasi</h2>
                                <div class="grid gap-4 sm:grid-cols-2">
                                    <label class="block space-y-1"><span class="text-xs font-medium">App ID (read-only)</span><input value="{{ $selectedId }}" disabled class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 outline-none focus:ring-2 focus:ring-indigo-400 dark:border-white/10 dark:bg-[#292929] dark:text-white w-full opacity-60" /></label>
                                    <label class="block space-y-1"><span class="text-xs font-medium">App Name</span><input wire:model="form.name" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 outline-none focus:ring-2 focus:ring-indigo-400 dark:border-white/10 dark:bg-[#292929] dark:text-white w-full" maxlength="120" /></label>
                                    <label class="block space-y-1"><span class="text-xs font-medium">Icon Identifier / Asset URL</span><input wire:model="form.icon" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 outline-none focus:ring-2 focus:ring-indigo-400 dark:border-white/10 dark:bg-[#292929] dark:text-white w-full" /></label>
                                    <label class="block space-y-1"><span class="text-xs font-medium">Livewire Component</span><input wire:model="form.component" @disabled($isCore) class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 outline-none focus:ring-2 focus:ring-indigo-400 dark:border-white/10 dark:bg-[#292929] dark:text-white w-full font-mono text-[11px]" /></label>
                                    <label class="block space-y-1"><span class="text-xs font-medium">Entry Path</span><input wire:model="form.entry" @disabled($isCore) class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 outline-none focus:ring-2 focus:ring-indigo-400 dark:border-white/10 dark:bg-[#292929] dark:text-white w-full font-mono" /></label>
                                    <div class="flex items-center gap-3 rounded-lg bg-neutral-50 px-3 py-2 dark:bg-white/5"><input type="checkbox" wire:model="form.pinned" class="size-4 accent-indigo-600" id="wm-pinned" /><label for="wm-pinned" class="text-xs font-medium">Pinned to Dock (default)</label></div>
                                </div>
                                <label class="mt-4 block space-y-1"><span class="text-xs font-medium">Owned Routes (satu path per baris)</span><textarea wire:model="routesText" rows="3" @disabled($isCore) class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 outline-none focus:ring-2 focus:ring-indigo-400 dark:border-white/10 dark:bg-[#292929] dark:text-white w-full resize-y font-mono text-[11px]"></textarea></label>
                                @if($isCore)<p class="mt-2 text-[11px] text-neutral-500">Entry, routes, dan component aplikasi sistem dilindungi dari perubahan melalui UI.</p>@endif
                            </section>
                            <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                <h2 class="mb-1 text-sm font-semibold">Window Configuration</h2>
                                <p class="mb-4 text-xs text-neutral-500">Semua ukuran dalam pixel. Nilai maksimum boleh dikosongkan.</p>
                                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                    @foreach (['width' => 'Default Width', 'height' => 'Default Height', 'min_width' => 'Minimum Width', 'min_height' => 'Minimum Height', 'max_width' => 'Maximum Width', 'max_height' => 'Maximum Height'] as $key => $label)
                                        <label class="block space-y-1" wire:key="wm-window-{{ $key }}">
                                            <span class="text-xs font-medium">{{ $label }}</span>
                                            <input type="number" min="200" max="10000" wire:model="form.window.{{ $key }}" class="rounded-lg border border-neutral-200 bg-white px-3 py-2 text-xs text-neutral-900 outline-none focus:ring-2 focus:ring-indigo-400 dark:border-white/10 dark:bg-[#292929] dark:text-white w-full" />
                                        </label>
                                    @endforeach
                                </div>
                                <div class="mt-4 flex flex-wrap gap-x-6 gap-y-3">
                                    @foreach (['resizable' => 'Resizable', 'maximizable' => 'Enable Maximize', 'center' => 'Center on Open'] as $key => $label)
                                        <label class="flex items-center gap-2 text-xs"><input type="checkbox" wire:model="form.window.{{ $key }}" class="size-4 accent-indigo-600" />{{ $label }}</label>
                                    @endforeach
                                </div>
                            </section>
                        @else
                            <section class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.03]">
                                <h2 class="text-sm font-semibold">Application Shell</h2>
                                <p class="mt-1 mb-4 text-xs text-neutral-500">Mode managed membuat kerangka aplikasi otomatis. Mode embedded untuk aplikasi yang sudah menggunakan &lt;x-minios:desktop&gt;.</p>
                                <label class="mb-4 block space-y-1 text-xs font-medium">App Shell Mode
                                    <select wire:model="form.layout.shell_mode" @disabled($isCore) class="mt-1 w-full rounded-lg border border-neutral-200 bg-white p-2 text-xs dark:border-white/10 dark:bg-[#292929]">
                                        <option value="managed">Managed — MiniOS membungkus konten secara otomatis</option>
                                        <option value="embedded">Embedded — aplikasi memiliki desktop layout sendiri</option>
                                    </select>
                                </label>
                                @foreach (['menubar_enabled' => ['Enable Menubar', 'Menu File, View, Window, Help, submenu, dan action terkelola.'], 'sidebar_enabled' => ['Enable Sidebar', 'Sidebar item Default / CRUD dan navigasi.'], 'statusbar_enabled' => ['Enable Statusbar', 'Area HTML/Blade khusus di bawah konten.']] as $key => $copy)
                                    <label class="flex cursor-pointer items-center justify-between gap-4 border-b border-neutral-100 py-4 last:border-0 dark:border-white/10">
                                        <span class="min-w-0"><span class="block text-sm font-medium">{{ $copy[0] }}</span><span class="block text-xs text-neutral-500">{{ $copy[1] }}</span></span>
                                        <input type="checkbox" wire:model="form.layout.{{ $key }}" class="size-5 shrink-0 accent-indigo-600" />
                                    </label>
                                @endforeach
                                <div class="mt-4 rounded-lg border border-dashed border-neutral-200 p-3 text-xs text-neutral-500 dark:border-white/10">
                                    Statusbar custom: <code class="select-all">resources/views/apps/{{ $selectedId }}/customs/statusbar.blade.php</code>
                                    <span class="ml-1 font-semibold {{ $hasStatusbarTemplate ? 'text-emerald-600' : 'text-amber-600' }}">{{ $hasStatusbarTemplate ? 'Template tersedia' : 'Template belum ada — fallback Ready' }}</span>
                                    <p class="mt-1">Managed Shell memuat view ini otomatis. Embedded Shell dapat memakai <code>&lt;x-minios:desktop.statusbar app-id="{{ $selectedId }}" /&gt;</code>. Preferensi tampil/sembunyi disimpan lokal per aplikasi.</p>
                                </div>
                            </section>
                        @endif
                    </div>
                    <div class="sticky bottom-0 flex items-center justify-between gap-3 border-t border-neutral-200 bg-white/95 px-5 py-3 backdrop-blur-md dark:border-white/10 dark:bg-[#252525]/95">
                        <button type="button" wire:click="resetToDefaults" wire:confirm="Hapus semua override aplikasi ini dan kembalikan ke manifest PHP?" class="rounded-lg border border-neutral-200 px-3 py-2 text-xs hover:bg-neutral-100 dark:border-white/10 dark:hover:bg-white/10">Reset to Default</button>
                        <button type="submit" @disabled(! $hasDatabase) wire:loading.attr="disabled" wire:target="save" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">Simpan Perubahan</button>
                    </div>
                </form>
                @endif
            </div>
        @else
            <div class="m-auto p-8 text-sm text-neutral-500">Tidak ada aplikasi yang terdaftar.</div>
        @endif
    </x-minios:desktop.content>

    <x-minios:desktop.statusbar>
        <span>MiniOS · App Definition Manager</span>
        <span>Statusbar · Phase 4</span>
    </x-minios:desktop.statusbar>
</x-minios:desktop>
