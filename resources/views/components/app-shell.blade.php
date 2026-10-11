@props(['appId', 'layout' => []])
{{-- Opt-in managed shell for apps which do NOT already render x-minios:desktop. --}}
<x-minios:desktop :app-id="$appId">
    @if($layout['menubar_enabled'] ?? false)
        <x-minios:desktop.menu>
            {{-- x-minios:desktop.menu automatically injects the database/virtual menu. --}}
        </x-minios:desktop.menu>
    @endif

    @if($layout['sidebar_enabled'] ?? false)
        <x-minios:desktop.sidebar>
            <x-minios.sidebar.managed :app-id="$appId" />
        </x-minios:desktop.sidebar>
    @endif

    <x-minios:desktop.content>
        @if($layout['sidebar_enabled'] ?? false)
            <livewire:dynamic-component :is="\Novay\MiniOS\Livewire\Apps\ManagedAppContent::class" :app-id="$appId" :wire:key="'managed-content-'.$appId" />
        @else
            {{ $slot }}
        @endif
    </x-minios:desktop.content>

    @if($layout['statusbar_enabled'] ?? false)
        <x-minios:desktop.statusbar :app-id="$appId" />
    @endif
</x-minios:desktop>
