<div class="flex flex-col gap-4 p-5 text-sm" wire:key="wm-menu-builder-{{ $appId }}">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-semibold">Menubar Builder</h2>
            <p class="mt-1 text-xs text-neutral-500">Atur menu File, View, Window, Help, submenu, shortcut dan izin untuk <strong>{{ $appId }}</strong>.</p>
        </div>
        @if($ready)
            <div class="flex gap-2">
                @if(! $customized)
                    <button type="button" wire:click="initialize" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">Aktifkan Editor</button>
                @else
                    <button type="button" wire:click="add" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">+ Group</button>
                    <button type="button" wire:click="resetMenus" wire:confirm="Hapus semua kustomisasi dan kembali ke menu default?" class="rounded-lg border border-neutral-200 px-3 py-2 text-xs dark:border-white/10">Reset Menus</button>
                @endif
            </div>
        @endif
    </div>

    @if(! $ready)
        <p class="rounded-lg bg-amber-500/10 p-3 text-xs text-amber-600">Akses atau tabel menu belum tersedia. Jalankan migration dan periksa Gate minios.manage-apps.</p>
    @endif
    @if($flash)
        <p class="rounded-lg bg-emerald-500/10 p-3 text-xs text-emerald-700 dark:text-emerald-300">{{ $flash }}</p>
    @endif
    @if($errors->any())
        <div class="rounded-lg bg-red-500/10 p-3 text-xs text-red-700 dark:text-red-300">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(310px,0.85fr)]">
        <section class="min-w-0 space-y-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.03]">
            <h3 class="font-semibold">Struktur Menu {{ $customized ? '· Customized' : '· Default' }}</h3>
            @if($tree === [])
                <p class="text-xs text-neutral-500">Belum ada menu. Pilih + Group untuk menambahkan.</p>
            @else
                <div class="space-y-1">
                    @include('minios::apps.window-manager.partials.menu-tree', ['nodes' => $tree, 'depth' => 1, 'customized' => $customized])
                </div>
            @endif
            @unless($customized)<p class="text-xs text-neutral-500">Menu default masih virtual dan akan disalin ke editor saat kamu klik Aktifkan Editor.</p>@endunless
        </section>

        <section class="min-w-0 space-y-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.03]">
            @if($customized)
                <h3 class="font-semibold">{{ $editingId === null ? 'Tambah Menu' : 'Edit Menu #'.$editingId }}</h3>
                <p class="text-xs text-neutral-500">{{ $parentId === null ? 'Menu tingkat atas (Group)' : 'Submenu dari #'.$parentId }}</p>
                <label class="block text-xs font-semibold">Tipe
                    <select wire:model.live="menuForm.kind" @disabled($editingId !== null && $parentId === null)
                        class="mt-1 w-full rounded-lg border border-neutral-200 bg-white p-2 text-xs dark:border-white/10 dark:bg-[#292929]">
                        <option value="group">Group / Submenu</option>
                        @if($parentId !== null)<option value="action">Action</option><option value="separator">Separator</option>@endif
                    </select>
                </label>
                @if(($menuForm['kind'] ?? 'action') !== 'separator')
                    <label class="block text-xs font-semibold">Label
                        <input wire:model="menuForm.label" maxlength="120" placeholder="Nama menu" class="mt-1 w-full rounded-lg border border-neutral-200 bg-white p-2 text-xs dark:border-white/10 dark:bg-[#292929]" />
                    </label>
                @endif
                @if(($menuForm['kind'] ?? 'action') === 'action')
                    <div class="grid grid-cols-2 gap-2">
                        <label class="block text-xs font-semibold">Icon
                            <input wire:model="menuForm.icon" placeholder="arrow-path" class="mt-1 w-full rounded-lg border border-neutral-200 bg-white p-2 text-xs dark:border-white/10 dark:bg-[#292929]" />
                        </label>
                        <label class="block text-xs font-semibold">Shortcut (label)
                            <input wire:model="menuForm.shortcut" placeholder="Ctrl+R" class="mt-1 w-full rounded-lg border border-neutral-200 bg-white p-2 text-xs dark:border-white/10 dark:bg-[#292929]" />
                        </label>
                    </div>
                    <label class="block text-xs font-semibold">Action
                        <select wire:model.live="menuForm.action_type" class="mt-1 w-full rounded-lg border border-neutral-200 bg-white p-2 text-xs dark:border-white/10 dark:bg-[#292929]">
                            @foreach(\Novay\MiniOS\Services\AppMenuService::ACTIONS as $action)
                                <option value="{{ $action }}">{{ $action }}</option>
                            @endforeach
                        </select>
                    </label>
                    @if(($menuForm['action_type'] ?? '') === 'app.navigate')
                        <label class="block text-xs font-semibold">Internal Path
                            <input wire:model="menuForm.path" placeholder="/todo/tasks" class="mt-1 w-full rounded-lg border border-neutral-200 bg-white p-2 text-xs dark:border-white/10 dark:bg-[#292929]" />
                        </label>
                    @elseif(($menuForm['action_type'] ?? '') === 'app.event')
                        <label class="block text-xs font-semibold">App event slug
                            <input wire:model="menuForm.event" placeholder="open-settings" class="mt-1 w-full rounded-lg border border-neutral-200 bg-white p-2 text-xs dark:border-white/10 dark:bg-[#292929]" />
                        </label>
                    @endif
                @endif
                <label class="block text-xs font-semibold">Permissions (satu Gate ability per baris)
                    <textarea wire:model="permissionsText" rows="3" placeholder="tasks.view" class="mt-1 w-full rounded-lg border border-neutral-200 bg-white p-2 text-xs dark:border-white/10 dark:bg-[#292929]"></textarea>
                </label>
                <label class="flex items-center gap-2 text-xs"><input type="checkbox" wire:model="menuForm.enabled" class="accent-indigo-600" />Enabled</label>
                <div class="flex gap-2 pt-2">
                    <button type="button" wire:click="saveMenu" class="rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-500">Simpan Item</button>
                    <button type="button" wire:click="add" class="rounded-lg border border-neutral-200 px-4 py-2 text-xs dark:border-white/10">Batal / New Group</button>
                </div>
            @else
                <div class="flex h-48 flex-col items-center justify-center text-center text-xs text-neutral-500">
                    <p class="font-medium">Menu bawaan tersedia otomatis.</p>
                    <p class="mt-1">Klik Aktifkan Editor untuk mengubah, mengurutkan, atau menambahkan item.</p>
                </div>
            @endif
        </section>
    </div>
</div>
