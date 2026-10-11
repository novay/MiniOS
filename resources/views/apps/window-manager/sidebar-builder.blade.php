<section class="space-y-4 p-5" wire:key="sidebar-builder-{{ $appId }}">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-sm font-semibold">Sidebar Builder</h2>
            <p class="mt-1 text-xs text-neutral-500">Navigasi managed apps. Pilih Default atau CRUD, urutkan, dan generate source secara opsional.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(!$customized)
                <button type="button" wire:click="initialize" @disabled(!$ready) class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white disabled:opacity-40">Mulai Edit</button>
            @else
                <button type="button" wire:click="add" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white">+ Item Baru</button>
                <button type="button" wire:click="resetSidebar" wire:confirm="Reset sidebar? Source code yang sudah di-generate tidak dihapus." class="rounded-lg border border-neutral-200 px-3 py-2 text-xs dark:border-white/10">Reset</button>
            @endif
        </div>
    </div>
    @if(!$ready)
        <p class="rounded-lg border border-amber-300 bg-amber-50 p-3 text-xs text-amber-800">Jalankan php artisan migrate dahulu.</p>
    @endif
    @if($isCore)
        <p class="rounded-lg bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-500/10 dark:text-amber-200">Aplikasi sistem dapat mengelola item navigasi, tetapi tidak dapat menghasilkan source CRUD baru melalui UI.</p>
    @endif
    @if($flash)
        <p class="rounded-lg p-3 text-xs {{ $flashType === 'error' ? 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-300' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-300' }}">{{ $flash }}</p>
    @endif
    @if($errors->any())
        <ul class="rounded-lg bg-red-50 p-3 text-xs text-red-700 dark:bg-red-500/10 dark:text-red-300">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    @endif
    <div class="grid gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(300px,0.85fr)]">
        <div class="space-y-2">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-neutral-500">Navigation Items</p>
            @forelse($items as $item)
                <div wire:key="sidebar-item-{{ $item['id'] ?? 'virtual-home' }}" class="flex items-center gap-2 rounded-xl border border-neutral-200 bg-white p-3 dark:border-white/10 dark:bg-white/[0.03]">
                    <span class="rounded-lg bg-neutral-100 p-2 dark:bg-white/10"><flux:icon :name="$item['icon'] ?: 'folder'" class="size-4" /></span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2"><span class="truncate text-xs font-semibold">{{ $item['label'] }}</span><span class="rounded bg-neutral-100 px-1.5 py-0.5 text-[10px] dark:bg-white/10">{{ strtoupper($item['type']) }}</span>@unless($item['enabled'])<span class="text-[10px] text-amber-500">Disabled</span>@endunless</div>
                        <div class="truncate font-mono text-[10px] text-neutral-500">{{ $item['route_prefix'] }}</div>
                        @if($item['type'] === 'crud')
                            <div class="text-[10px] text-neutral-500">{{ ($item['options']['generated'] ?? false) ? '8 file generated' : 'Source belum di-generate' }}</div>
                        @endif
                    </div>
                    @if($customized && $item['id'])
                        <div class="flex flex-wrap justify-end gap-1">
                            <button type="button" wire:click="move({{ $item['id'] }}, -1)" title="Naik" class="rounded px-1.5 py-1 text-xs hover:bg-neutral-100 dark:hover:bg-white/10">↑</button>
                            <button type="button" wire:click="move({{ $item['id'] }}, 1)" title="Turun" class="rounded px-1.5 py-1 text-xs hover:bg-neutral-100 dark:hover:bg-white/10">↓</button>
                            <button type="button" wire:click="edit({{ $item['id'] }})" class="rounded px-2 py-1 text-xs hover:bg-neutral-100 dark:hover:bg-white/10">Edit</button>
                            <button type="button" wire:click="deleteItem({{ $item['id'] }})" wire:confirm="Hapus menu ini? File source yang sudah dibuat tetap dipertahankan." class="rounded px-2 py-1 text-xs text-rose-600">Hapus</button>
                            @if($item['type'] === 'crud' && !($item['options']['generated'] ?? false) && !$isCore)
                                <button type="button" wire:click="generate({{ $item['id'] }})" wire:confirm="Buat delapan berkas PHP/Blade placeholder untuk CRUD ini? File lama tidak akan ditimpa." class="rounded bg-indigo-50 px-2 py-1 text-xs text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-300">Generate</button>
                            @endif
                        </div>
                    @endif
                </div>
            @empty
                <p class="rounded-lg border border-dashed border-neutral-200 p-6 text-center text-xs text-neutral-500 dark:border-white/10">Belum ada item sidebar.</p>
            @endforelse
        </div>
        @if($customized)
        <form wire:submit="saveItem" class="space-y-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-white/10 dark:bg-white/[0.03]">
            <h3 class="text-xs font-semibold">{{ $editingId ? 'Edit Sidebar Item' : 'Tambah Sidebar Item' }}</h3>
            <div class="grid grid-cols-2 gap-3">
                <label class="space-y-1 text-xs">Slug<input wire:model="itemForm.slug" class="w-full rounded-lg border border-neutral-200 bg-transparent p-2 font-mono text-xs dark:border-white/10" placeholder="tasks" /></label>
                <label class="space-y-1 text-xs">Jenis<select wire:model="itemForm.type" class="w-full rounded-lg border border-neutral-200 bg-transparent p-2 text-xs dark:border-white/10"><option value="default">Default</option><option value="crud">CRUD</option></select></label>
                <label class="space-y-1 text-xs">Label<input wire:model="itemForm.label" class="w-full rounded-lg border border-neutral-200 bg-transparent p-2 text-xs dark:border-white/10" placeholder="Tasks" /></label>
                <label class="space-y-1 text-xs">Icon (Heroicon)<input wire:model="itemForm.icon" class="w-full rounded-lg border border-neutral-200 bg-transparent p-2 text-xs dark:border-white/10" placeholder="clipboard-document-list" /></label>
                <label class="col-span-2 space-y-1 text-xs">Description (opsional)<input wire:model="itemForm.description" class="w-full rounded-lg border border-neutral-200 bg-transparent p-2 text-xs dark:border-white/10" /></label>
                <label class="space-y-1 text-xs">Warna ikon (Tailwind)<input wire:model="itemForm.icon_color" class="w-full rounded-lg border border-neutral-200 bg-transparent p-2 text-xs dark:border-white/10" placeholder="text-indigo-500" /></label>
                <label class="space-y-1 text-xs">Badge (opsional)<input wire:model="itemForm.badge" class="w-full rounded-lg border border-neutral-200 bg-transparent p-2 text-xs dark:border-white/10" /></label>
                <label class="col-span-2 space-y-1 text-xs">Route prefix<input wire:model="itemForm.route_prefix" class="w-full rounded-lg border border-neutral-200 bg-transparent p-2 font-mono text-xs dark:border-white/10" placeholder="/todo/tasks" /></label>
                <label class="col-span-2 space-y-1 text-xs">Permissions — 1 Gate per baris<textarea wire:model="permissionsText" rows="2" class="w-full rounded-lg border border-neutral-200 bg-transparent p-2 font-mono text-xs dark:border-white/10"></textarea></label>
                <label class="col-span-2 flex items-center gap-2 text-xs"><input type="checkbox" wire:model="itemForm.enabled" class="size-4 accent-indigo-600" /> Item aktif</label>
            </div>
            <div class="flex gap-2"><button type="submit" wire:loading.attr="disabled" class="rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white disabled:opacity-50">Simpan Item</button><button type="button" wire:click="add" class="rounded-lg border border-neutral-200 px-3 py-2 text-xs dark:border-white/10">Batal</button></div>
        </form>
        @else
        <div class="rounded-xl border border-dashed border-neutral-200 p-4 text-xs text-neutral-500 dark:border-white/10">Klik Mulai Edit untuk menyimpan navigasi default Home dan menambahkan item baru.</div>
        @endif
    </div>
</section>
