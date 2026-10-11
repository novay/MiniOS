@foreach($items as $item)
    @if(($item['action_type'] ?? null) === 'separator')
        <x-minios.menubar.separator />
    @elseif(! empty($item['children']))
        <x-minios.menubar.submenu :label="$item['label']" :icon="$item['icon'] ?? null">
            @include('minios::components.menubar.dynamic-items', ['items' => $item['children'], 'appId' => $appId])
        </x-minios.menubar.submenu>
    @elseif(in_array($item['action_type'] ?? null, \Novay\MiniOS\Services\AppMenuService::ACTIONS, true))
        <x-minios.menubar.item
            :icon="$item['icon'] ?? null"
            :shortcut="$item['shortcut'] ?? null"
            :action="$item['action_type']"
            :payload="$item['action_payload'] ?? []">
            {{ $item['label'] }}
        </x-minios.menubar.item>
    @endif
@endforeach
