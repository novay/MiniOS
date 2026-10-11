<div class="min-h-full" @minios-app-navigate.window="if ($event.detail?.id === @js($appId)) $wire.navigate($event.detail.path)" @desktop-route-changed.window="if (($event.detail?.path === @js($basePath) || ($event.detail?.path || '').startsWith(@js($basePath.'/'))) && $event.detail.path !== @js($path)) $wire.navigate($event.detail.path)">
    @if($supported)
        @if(in_array($page['page'] ?? null, ['Show', 'Edit'], true))
            <livewire:dynamic-component :is="$component" :record-id="$recordId" :wire:key="'managed-'.$appId.'-'.md5($pathKey)" />
        @else
            <livewire:dynamic-component :is="$component" :wire:key="'managed-'.$appId.'-'.md5($pathKey)" />
        @endif
    @elseif($page !== null)
        <section class="m-6 rounded-xl border border-amber-300/50 bg-amber-50 p-5 text-sm text-amber-800 dark:bg-amber-500/10 dark:text-amber-200">
            <h2 class="font-semibold">Komponen halaman belum tersedia</h2>
            <p class="mt-2">Buat/generate Livewire placeholder untuk item ini terlebih dahulu, lalu reload desktop.</p>
        </section>
    @else
        <section class="m-6 rounded-xl border border-neutral-200 p-5 text-sm dark:border-white/10">
            <h2 class="font-semibold">Tidak ada halaman yang dapat ditampilkan</h2>
            <p class="mt-2 text-neutral-500">Periksa sidebar items dan permissions halaman.</p>
        </section>
    @endif
</div>
