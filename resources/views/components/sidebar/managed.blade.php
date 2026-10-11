@props(['appId'])
@php
    $app = app(\Novay\MiniOS\Support\AppRegistry::class)->get($appId);
    $items = $app ? app(\Novay\MiniOS\Services\AppSidebarService::class)->items($app) : [];
@endphp
<x-minios:desktop.nav>
    @forelse($items as $item)
        @php
            $target = \Novay\MiniOS\MiniOS::path($item['route_prefix']);
        @endphp
        <x-minios:desktop.nav.item
            :label="$item['label']"
            :description="$item['description']"
            :icon="$item['icon'] ?: 'folder'"
            :icon-color="$item['icon_color'] ?: 'text-neutral-500'"
            :badge="$item['badge']"
            :active="false"
            :navigate-to="$target"
            :nav-is-crud="$item['type'] === 'crud'"
            x-bind:class="(currentManagedPath === $el.dataset.miniosNavPath || ($el.dataset.miniosNavCrud === '1' && currentManagedPath.startsWith($el.dataset.miniosNavPath + '/'))) ? '!bg-white dark:!bg-white/10 !text-neutral-900 dark:!text-white shadow-sm' : ''"
        />
    @empty
        <p class="px-3 py-3 text-xs text-neutral-500" x-show="!sidebarCollapsed">Tidak ada navigasi yang diaktifkan.</p>
    @endforelse
</x-minios:desktop.nav>
