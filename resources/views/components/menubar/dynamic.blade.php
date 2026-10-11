@props(['appId'])
@php
    $menuApp = app(\Novay\MiniOS\Support\AppRegistry::class)->get($appId);
    $menuItems = $menuApp ? app(\Novay\MiniOS\Services\AppMenuService::class)->tree($menuApp) : [];
@endphp
@if($menuItems !== [])
<x-minios.menubar {{ $attributes }}>
    @foreach($menuItems as $group)
        <x-minios.menubar.menu :label="$group['label']" :id="'dynamic-'.($group['id'] ?? \Illuminate\Support\Str::slug($group['label']))">
            @include('minios::components.menubar.dynamic-items', ['items' => $group['children'] ?? [], 'appId' => $appId])
        </x-minios.menubar.menu>
    @endforeach
</x-minios.menubar>
@endif
